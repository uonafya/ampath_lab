<?php

namespace App\Http\Controllers;

use App\CancerWorksheet;
use App\CancerPatient;
use App\CancerSample;
use App\CancerSampleView;
use App\Machine;
use Illuminate\Http\Request;

class CancerWorksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($limit=94)
    {
        $data = $this->get_samples_for_run(94);

        return view('forms.cancerworksheet', $data)->with('pageTitle', "Create Worksheet ($limit)");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $worksheet = new CancerWorksheet;
        $worksheet->fill($request->except('_token', 'limit'));
        $worksheet->createdby = auth()->user()->id;
        $worksheet->lab_id = auth()->user()->lab_id;
        $worksheet->save();

        $data = $this->get_samples_for_run(94);

        if(!$data || !$data['create']){
            $worksheet->delete();
            session(['toast_message' => "The worksheet could not be created.", 'toast_error' => 1]);
            return back();            
        }
        $samples = $data['samples'];
        $sample_ids = $samples->pluck('id')->toArray();

        CancerSample::whereIn('id', $sample_ids)->update(['worksheet_id' => $worksheet->id]);

        return redirect()->route('cancerworksheet.print', ['worksheet' => $worksheet->id]);
    }



    public function print(CancerWorksheet $worksheet)
    {
        return $this->show($worksheet, true);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(CancerWorksheet $worksheet, $print=false)
    {
        $worksheet->load(['creator']);
        $sample_array = CancerSampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        // $samples = Sample::whereIn('id', $sample_array)->with(['patient', 'batch.facility'])->get();


        $samples = CancerSample::with(['patient'])
                    ->whereIn('id', $sample_array)
                    ->orderBy('run', 'desc')
                    // ->when(true, function($query){
                    //     if(in_array(env('APP_LAB'), [2])) return $query->orderBy('facility_id')->orderBy('batch_id', 'asc');
                    //     if(in_array(env('APP_LAB'), [3])) $query->orderBy('datereceived', 'asc');
                    //     if(!in_array(env('APP_LAB'), [8, 9, 1])) return $query->orderBy('batch_id', 'asc');
                    // })
                    ->orderBy('id', 'asc')
                    ->get();

        $data = ['worksheet' => $worksheet, 'samples' => $samples, 'i' => 0];

        if($print) $data['print'] = true;

        // if($worksheet->machine_type == 1){
        //     return view('worksheets.other-table', $data)->with('pageTitle', 'Worksheets');
        // }
        // else{
            return view('worksheets.abbot-table', $data)->with('pageTitle', 'Worksheets');
        // }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }




    private function get_samples_for_run($limit = 94){
        $samples = CancerSample::whereNull('worksheet_id')->where('receivedstatus', '<>', 2)->whereNull('result')
                                    ->orderBy('datereceived', 'asc')->orderBy('parentid', 'desc')->orderBy('id', 'asc')
                                    ->limit($limit)->get();
        $machine = Machine::find(3);
        return [
            'count' => $samples->count(),
            'limit' => $limit,
            'create' => true,
            'machine_type' => $machine->id,
            'machine' => $machine,
            'samples' => $samples
        ];
        
    }
}
