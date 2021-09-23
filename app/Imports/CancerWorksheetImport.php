<?php

namespace App\Imports;

use \App\Misc;
use \App\CancerSample;
use \App\CancerSampleView;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CancerWorksheetImport implements ToCollection, WithHeadingRow
{
	protected $worksheet;
	protected $cancelled;
    protected $daterun;
    private $targets = [
        'target_1' => 'Other HR HPV',
        'target_2' => 'HPV 16',
        'target_3' => 'HPV 18'
    ];

	public function __construct($worksheet, $request)
	{
        $cancelled = false;
        if($worksheet->status_id == 4) $cancelled =  true;
        $worksheet->fill($request->except(['_token', 'upload']));
        $this->cancelled = $cancelled;
        $this->worksheet = $worksheet;
        $this->daterun = $request->input('daterun', date('Y-m-d'));
	}

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        dd($collection);
    	$worksheet = $this->worksheet;
    	$cancelled = $this->cancelled;
        $today = $datetested = $this->daterun;
        $positive_control = $negative_control = null;

        $sample_array = $doubles = [];

        $collection = $collection->groupBy('sample_id');
        $newcollection = collect([]);
        foreach ($collection as $key => $collection) {
            $data = $collection->first();
            foreach ($this->targets as $key => $target) {
                $RsTarget = $collection->where('target', $target);
                $data[$key] = $RsTarget->first()['result'] ?? 'Invalid';
            }
            $newcollection->push($data);
        }
        
        $collection = $newcollection;
        foreach ($collection as $key => $data) 
        {
            if(!isset($data['sample_id'])) break;

            $sample_id = (int) trim($data['sample_id']);
            $interpretation = rtrim($data['flag'] ?? '');
            $control = rtrim($data['type']);
            $date_tested = $data['result_creation_datetime'] ?? NULL;
            $date_tested =  (isset($date_tested)) ? date("Y-m-d", strtotime($data['result_creation_datetime'])) :
                            date("Y-m-d");            

            $data_array = Misc::hpv_sample_result($data);

            if(\Str::contains($control, '+')){
                $positive_control = $data_array;
                continue;
            }
            else if(\Str::contains($control, '-')){
                $negative_control = $data_array;
                continue;
            }

            $data_array = array_merge($data_array, ['datemodified' => $today, 'datetested' => $datetested]);
            $sample = CancerSample::find($sample_id);
            if(!$sample) continue;

            $sample->fill($data_array);
            if($cancelled) $sample->worksheet_id = $worksheet->id;
            else if($sample->worksheet_id != $worksheet->id || $sample->dateapproved) continue;
                
            $sample->save();
        }
        
        CancerSample::where(['worksheet_id' => $worksheet->id, 'run' => 0])->update(['run' => 1]);
        CancerSample::where(['worksheet_id' => $worksheet->id])->whereNull('repeatt')->update(['repeatt' => 0]);
        CancerSample::where(['worksheet_id' => $worksheet->id])->whereNull('result')->update(['repeatt' => 1]);

        $worksheet->neg_control_interpretation = $negative_control['interpretation'] ?? null;
        $worksheet->neg_control_result = $negative_control['result'] ?? null;

        $worksheet->pos_control_interpretation = $positive_control['interpretation'] ?? null;
        $worksheet->pos_control_result = $positive_control['result'] ?? null;
        $worksheet->daterun = $datetested;
        $worksheet->uploadedby = auth()->user()->id;
        $worksheet->save();

        session(compact('doubles'));

        Misc::requeue($worksheet->id, $worksheet->daterun, 'hpv');
        session(['toast_message' => "The worksheet has been updated with the results."]);
    }
}
