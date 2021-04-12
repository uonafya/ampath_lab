<?php

namespace App\Http\Controllers;

use App\CancerWorksheet;
use App\CancerPatient;
use App\CancerSample;
use App\CancerSampleView;
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
    public function create()
    {
        $samples = $this->get_samples_for_run(94);
        $sampleCount = $samples->count();
        $worksheetCount = CancerWorksheet::max('id')+1;
        $data['samples'] = $samples;
        $data['worksheet'] = $worksheetCount;
        $data['limit'] = $limit;
        $data = (object) $data;
        return view('forms.cancerworksheet', compact('data'))->with('pageTitle', "Create Worksheet ($limit)");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        return CancerSample::whereNull('worksheet_id')->where('receivedstatus', '<>', 2)->whereNull('result')
                                    ->orderBy('datereceived', 'asc')->orderBy('parentid', 'desc')->orderBy('id', 'asc')
                                    ->limit($limit)->get();
    }
}
