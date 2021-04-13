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
    	$worksheet = $this->worksheet;
    	$cancelled = $this->cancelled;
        $today = $datetested = $this->daterun;
        $positive_control = $negative_control = null;

        $sample_array = $doubles = [];

        // if($worksheet->machine_type == 2)
        // {
        //     $date_tested = $this->daterun;
        //     $datetested = Misc::worksheet_date($date_tested, $worksheet->created_at);

        //     $check = array();

        //     $bool = false;
        //     $positive_control = $negative_control = "Passed";

        //     foreach ($collection as $key => $value) {
        //         if($value[5] == "RESULT"){
        //             $bool = true;
        //             continue;
        //         }

        //         if($bool){
        //             $sample_id = $value[1];
        //             $interpretation = $value[5];
        //             $error = $value[10];


        //             Misc::dup_worksheet_rows($doubles, $sample_array, $sample_id, $interpretation);

        //             $data_array = Misc::sample_result($interpretation, $error);

        //             if($sample_id == "HIV_NEG") $negative_control = $data_array;
        //             if($sample_id == "HIV_HIPOS") $positive_control = $data_array;

        //             $data_array = array_merge($data_array, ['datemodified' => $today, 'datetested' => $today]);
        //             // $search = ['id' => $sample_id, 'worksheet_id' => $worksheet->id];
        //             // Sample::where($search)->update($data_array);

        //             $sample_id = (int) $sample_id;
        //             $sample = Sample::find($sample_id);
        //             if(!$sample) continue;

        //             $sample->fill($data_array);
        //             if($cancelled) $sample->worksheet_id = $worksheet->id;
        //             else if($sample->worksheet_id != $worksheet->id || $sample->dateapproved) continue;

        //             $sample->save();
        //         }

        //         if($bool && $value[5] == "RESULT") break;
        //     }
        // }
        // else if($worksheet->machine_type == 1)
        // {
        //     foreach ($collection as $key => $data) 
        //     {
        //         $interpretation = rtrim($data[8]);
        //         $control = rtrim($data[5]);

        //         $error = $data[10];

        //         $date_tested=date("Y-m-d", strtotime($data[3]));

        //         $datetested = Misc::worksheet_date($date_tested, $worksheet->created_at);

        //         $data_array = Misc::sample_result($interpretation, $error);

        //         if($control == "NC") $negative_control = $data_array;

        //         if($control == "LPC" || $control == "PC") $positive_control = $data_array;

        //         $data_array = array_merge($data_array, ['datemodified' => $today, 'datetested' => $datetested]);

        //         $sample_id = (int) trim($data[4]);  

        //         Misc::dup_worksheet_rows($doubles, $sample_array, $sample_id, $interpretation);

        //         // $sample_id = substr($sample_id, 0, -1);
        //         $sample = Sample::find($sample_id);
        //         if(!$sample) continue;

        //         $sample->fill($data_array);
        //         if($cancelled) $sample->worksheet_id = $worksheet->id;
        //         else if($sample->worksheet_id != $worksheet->id || $sample->dateapproved) continue;
                    
        //         $sample->save();

        //     }
        // }
        // else if($worksheet->machine_type == 3)
        // {
            foreach ($collection as $key => $data) 
            {
                if(!isset($data['sample_id'])) break;

                // $sample_id = (int) trim($data[1]);
                // $interpretation = rtrim($data[5]); 
                // $control = rtrim($data[4]);
                // $date_tested=date("Y-m-d", strtotime($data[12]));
                // $datetested = Misc::worksheet_date($date_tested, $worksheet->created_at);

                $sample_id = (int) trim($data['sample_id']);
                $interpretation = rtrim($data['flag']);
                $control = NULL;
                $date_tested =  ($data['date_tested']) ? date("Y-m-d", strtotime($data['date_tested'])) :
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
        //}

        // $sample_array = SampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->flatten()->toArray();
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
