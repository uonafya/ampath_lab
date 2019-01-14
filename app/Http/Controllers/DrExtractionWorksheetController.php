<?php

namespace App\Http\Controllers;

use App\DrExtractionWorksheet;
use App\DrSample;
use Illuminate\Http\Request;

class DrExtractionWorksheetController extends Controller
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
    public function create($limit)
    {
        $data = Lookup::get_dr();
        $data = array_merge($data, MiscDr::get_extraction_worksheet_samples($limit));
        return view('forms.dr_extraction_worksheets', $data);        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {        
        $worksheet = new DrExtractionWorksheet;
        $worksheet->fill($request->except(['_token', 'limit']));
        $worksheet->save();

        $positive_control = DrSample::create(['extraction_worksheet_id' => $worksheet->id, 'patient_id' => 0, 'control' => 2]);
        $negative_control = DrSample::create(['extraction_worksheet_id' => $worksheet->id, 'patient_id' => 0, 'control' => 1]);

        $data = MiscDr::get_extraction_worksheet_samples($request->input('limit'));
        $samples = $data['samples'];

        foreach ($samples as $sample) {
            $sample->extraction_worksheet_id = $worksheet->id;
            $sample->save();
        }
        return redirect('dr_extraction_worksheet');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DrExtractionWorksheet  $drExtractionWorksheet
     * @return \Illuminate\Http\Response
     */
    public function show(DrExtractionWorksheet $drExtractionWorksheet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DrExtractionWorksheet  $drExtractionWorksheet
     * @return \Illuminate\Http\Response
     */
    public function edit(DrExtractionWorksheet $drExtractionWorksheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DrExtractionWorksheet  $drExtractionWorksheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DrExtractionWorksheet $drExtractionWorksheet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DrExtractionWorksheet  $drExtractionWorksheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(DrExtractionWorksheet $drExtractionWorksheet)
    {
        //
    }


    public function gel_documentation(Request $request, DrExtractionWorksheet $drExtractionWorksheet)
    {
        $drExtractionWorksheet->date_gel_documentation = date('Y-m-d');
        $drExtractionWorksheet->save();
        $sample_ids = $request->input('samples');
        DrSample::where('extraction_worksheet_id', $drExtractionWorksheet->id)->whereIn('id', $sample_ids)->update(['passed_gel_documentation' => true]);
        DrSample::where('extraction_worksheet_id', $drExtractionWorksheet->id)->whereNotIn('id', $sample_ids)->update(['passed_gel_documentation' => false]);

        session(['toast_message' => 'Gel documentation has been submitted.']);
        redirect('dr_worksheet/create/' . $drExtractionWorksheet->id);
    }
}