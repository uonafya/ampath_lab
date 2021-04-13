<?php

namespace App\Http\Controllers;

use App\CancerWorksheet;
use App\CancerPatient;
use App\CancerSample;
use App\CancerSampleView;
use App\Imports\CancerWorksheetImport;
use App\Lookup;
use App\Machine;
use App\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CancerWorksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($state=0, $date_start=NULL, $date_end=NULL, $worksheet_id=NULL)
    {
         // $state = session()->pull('worksheet_state', null); 
         $worksheets = CancerWorksheet::with(['creator'])->withCount(['sample'])
         ->when($worksheet_id, function ($query) use ($worksheet_id){
             return $query->where('cancer_worksheets.id', $worksheet_id);
         })
         ->when($state, function ($query) use ($state){
             if($state == 1 || $state == 12) $query->orderBy('cancer_worksheets.id', 'asc');
             if($state == 12){
                 return $query->where('status_id', 1)->whereRaw("cancer_worksheets.id in (
                     SELECT DISTINCT worksheet_id
                     FROM cancer_samples_view
                     WHERE parentid > 0 AND site_entry != 2
                 )");
             }
             return $query->where('status_id', $state);
         })
         ->when($date_start, function($query) use ($date_start, $date_end){
             if($date_end)
             {
                 return $query->whereDate('cancer_worksheets.created_at', '>=', $date_start)
                 ->whereDate('cancer_worksheets.created_at', '<=', $date_end);
             }
             return $query->whereDate('cancer_worksheets.created_at', $date_start);
         })
         ->orderBy('cancer_worksheets.id', 'desc')
         ->paginate();
 
         $worksheets->setPath(url()->current());
 
         $worksheet_ids = $worksheets->pluck(['id'])->toArray();
         $samples = $this->get_worksheets($worksheet_ids);
         $reruns = $this->get_reruns($worksheet_ids);
         $data = Lookup::worksheet_lookups();
 
         $worksheets->transform(function($worksheet, $key) use ($samples, $reruns, $data){
             $status = $worksheet->status_id;
             $total = $worksheet->sample_count;
 
             if(($status == 2 || $status == 3) && $samples){
                 $neg = $samples->where('worksheet_id', $worksheet->id)->where('result', 1)->first()->totals ?? 0;
                 $pos = $samples->where('worksheet_id', $worksheet->id)->where('result', 2)->first()->totals ?? 0;
                 $failed = $samples->where('worksheet_id', $worksheet->id)->where('result', 3)->first()->totals ?? 0;
                 $redraw = $samples->where('worksheet_id', $worksheet->id)->where('result', 5)->first()->totals ?? 0;
                 $noresult = $samples->where('worksheet_id', $worksheet->id)->where('result', 0)->first()->totals ?? 0;
 
                 $rerun = $reruns->where('worksheet_id', $worksheet->id)->first()->totals ?? 0;
             }
             else{
                 $neg = $pos = $failed = $redraw = $noresult = $rerun = 0;
 
                 if($status == 1){
                     $noresult = $worksheet->sample_count;
                     $rerun = $reruns->where('worksheet_id', $worksheet->id)->first()->totals ?? 0;
                 }
             }
             $worksheet->rerun = $rerun;
             $worksheet->neg = $neg;
             $worksheet->pos = $pos;
             $worksheet->failed = $failed;
             $worksheet->redraw = $redraw;
             $worksheet->noresult = $noresult;
             $worksheet->mylinks = $this->get_links($worksheet->id, $status, $worksheet->datereviewed);
             $worksheet->machine = $data['machines']->where('id', $worksheet->machine_type)->first()->output ?? '';
             $worksheet->status = $data['worksheet_statuses']->where('id', $status)->first()->output ?? '';
 
             return $worksheet;
         });
 
         $data = Lookup::worksheet_lookups();
         $data['status_count'] = CancerWorksheet::selectRaw("count(*) AS total, status_id, machine_type")
             ->groupBy('status_id', 'machine_type')
             ->orderBy('status_id', 'asc')
             ->orderBy('machine_type', 'asc')
             ->get();
         $data['worksheets'] = $worksheets;
         $data['myurl'] = url('cancerworksheet/index/' . $state . '/');
         $data['link_extra'] = '';
 
         return view('tables.worksheets', $data)->with('pageTitle', 'Cancer Worksheets');
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
    public function show($worksheet, $print=false)
    {
        $worksheet = CancerWorksheet::find($worksheet);
        $worksheet->load(['creator']);
        // $sample_array = CancerSampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '<>', 2)->get()/*->pluck('id')->toArray()*/;
        // // $samples = Sample::whereIn('id', $sample_array)->with(['patient', 'batch.facility'])->get();
        
        $samples = CancerSample::with(['patient'])
                    // ->whereIn('id', $sample_array)
                    ->where('worksheet_id', $worksheet->id)
                    ->orderBy('run', 'desc')
                    // // ->when(true, function($query){
                    // //     if(in_array(env('APP_LAB'), [2])) return $query->orderBy('facility_id')->orderBy('batch_id', 'asc');
                    // //     if(in_array(env('APP_LAB'), [3])) $query->orderBy('datereceived', 'asc');
                    // //     if(!in_array(env('APP_LAB'), [8, 9, 1])) return $query->orderBy('batch_id', 'asc');
                    // // })
                    ->orderBy('id', 'asc')
                    ->get();

        $data = ['worksheet' => $worksheet, 'samples' => $samples, 'i' => 0];

        if($print) $data['print'] = true;
        
        // // if($worksheet->machine_type == 1){
        // //     return view('worksheets.other-table', $data)->with('pageTitle', 'Worksheets');
        // // }
        // // else{
            return view('worksheets.abbot-table', $data)->with('pageTitle', 'Worksheets');
        // // }
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

    public function upload(CancerWorksheet $worksheet)
    {
        if(!in_array($worksheet->status_id, [1, 4])){
            session(['toast_error' => 1, 'toast_message' => 'You cannot update results for this worksheet.']);
            return back();
        }
        $worksheet->load(['creator']);
        $users = User::whereIn('user_type_id', [1, 4])->where('email', '!=', 'rufus.nyaga@ken.aphl.org')->get();
        return view('forms.upload_results', ['worksheet' => $worksheet, 'users' => $users])->with('pageTitle', 'Worksheet Upload');
    }

    /**
     * Update the specified resource in storage with results file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CancerWorksheet  $worksheet
     * @return \Illuminate\Http\Response
     */
    public function save_results(Request $request, CancerWorksheet $worksheet)
    {
        if(!in_array($worksheet->status_id, [1, 4])){
            session(['toast_error' => 1, 'toast_message' => 'You cannot update results for this worksheet.']);
            return back();
        }
        
        $file = $request->upload->path();
        $path = $request->upload->store('public/results/hpv'); 

        $c = new CancerWorksheetImport($worksheet, $request);
        Excel::import($c, $path);
        
        return redirect('cancerworksheet/approve/' . $worksheet->id);
    }

    public function approve_results(CancerWorksheet $worksheet)
    {        
        $worksheet->load(['reviewer', 'creator', 'runner', 'sorter', 'bulker']);

        // $samples = Sample::where('worksheet_id', $worksheet->id)->with(['approver'])->get();
        
        $samples = CancerSample::with(['approver', 'final_approver'])
                    ->where('worksheet_id', $worksheet->id) 
                    ->where('site_entry', '<>', 2) 
                    ->orderBy('run', 'desc')
                    ->when(true, function($query){
                        if(in_array(env('APP_LAB'), [2])) return $query->orderBy('facility_id');
                        if(in_array(env('APP_LAB'), [3])) $query->orderBy('datereceived', 'asc');
                    })
                    ->orderBy('id', 'asc')
                    ->get();

        $s = $this->get_worksheets($worksheet->id);

        $neg = $s->where('result', 1)->first()->totals ?? 0;
        $pos = $s->where('result', 2)->first()->totals ?? 0;
        $failed = $s->where('result', 3)->first()->totals ?? 0;
        $redraw = $s->where('result', 5)->first()->totals ?? 0;
        $noresult = $s->where('result', 0)->first()->totals ?? 0;

        $total = $neg + $pos + $failed + $redraw + $noresult;

        $subtotals = ['neg' => $neg, 'pos' => $pos, 'failed' => $failed, 'redraw' => $redraw, 'noresult' => $noresult, 'total' => $total];

        $data = Lookup::worksheet_approve_lookups();
        $data['samples'] = $samples;
        $data['subtotals'] = $subtotals;
        $data['worksheet'] = $worksheet;

        return view('tables.confirm_results', $data)->with('pageTitle', 'Approve Results');
    }

    public function cancel_upload(CancerWorksheet $worksheet)
    {
        if($worksheet->status_id != 2){
            session(['toast_message' => 'The upload for this worksheet cannot be reversed.']);
            session(['toast_error' => 1]);
            return back();
        }

        if($worksheet->uploadedby != auth()->user()->id && auth()->user()->user_type_id != 0){
            session(['toast_message' => 'Only the user who uploaded the results can reverse the upload.']);
            session(['toast_error' => 1]);
            return back();
        }

        $samples = CancerSample::where(['repeatt' => 1, 'worksheet_id' => $worksheet->id])->get();

        foreach ($samples as $sample) {
            $sample->remove_rerun();
        }

        $sample_array = CancerSampleView::select('id')->where('worksheet_id', $worksheet->id)->where('site_entry', '!=', 2)->get()->pluck('id')->toArray();
        CancerSample::whereIn('id', $sample_array)->update(['result' => null, 'interpretation' => null, 'datemodified' => null, 'datetested' => null, 'repeatt' => 0, 'dateapproved' => null, 'approvedby' => null]);
        $worksheet->status_id = 1;
        $worksheet->neg_control_interpretation = $worksheet->pos_control_interpretation = $worksheet->neg_control_result = $worksheet->pos_control_result = $worksheet->daterun = $worksheet->dateuploaded = $worksheet->uploadedby = $worksheet->datereviewed = $worksheet->reviewedby = $worksheet->datereviewed2 = $worksheet->reviewedby2 = null;
        $worksheet->save();

        session(['toast_message' => 'The upload has been reversed.']);
        return redirect("/cancerworksheet/upload/" . $worksheet->id);
    }


    

    public function wstatus($status)
    {
        switch ($status) {
            case 1:
                return "<strong><font color='#FFD324'>In-Process</font></strong>";
                break;
            case 2:
                return "<strong><font color='#0000FF'>Tested</font></strong>";
                break;
            case 3:
                return "<strong><font color='#339900'>Approved</font></strong>";
                break;
            case 4:
                return "<strong><font color='#FF0000'>Cancelled</font></strong>";
                break;            
            default:
                break;
        }
    }

    public function get_links($worksheet_id, $status, $datereviewed)
    {
        if($status == 1)
        {
            $d = "<a href='" . url('cancerworksheet/' . $worksheet_id) . "' title='Click to view Samples in this Worksheet' target='_blank'>Details</a> | "
                . "<a href='" . url('cancerworksheet/print/' . $worksheet_id) . "' title='Click to Print this Worksheet' target='_blank'>Print</a> | "
                . "<a href='" . url('cancerworksheet/cancel/' . $worksheet_id) . "' title='Click to Cancel this Worksheet' onClick=\"return confirm('Are you sure you want to Cancel Worksheet {$worksheet_id}?'); \" >Cancel</a> | "
                . "<a href='" . url('cancerworksheet/upload/' . $worksheet_id) . "' title='Click to Upload Results File for this Worksheet'>Update Results</a>";
        }
        else if($status == 2)
        {
            $d = "<a href='" . url('cancerworksheet/approve/' . $worksheet_id) . "' title='Click to Approve Samples Results in worksheet for Rerun or Dispatch' target='_blank'> Approve Worksheet Results ";

            if($datereviewed) $d .= "(Second Review)";

            $d .= "</a>";

        }
        else if($status == 3)
        {
            $d = "<a href='" . url('cancerworksheet/' . $worksheet_id) . "' title='Click to view Samples in this Worksheet' target='_blank'>Details</a> | "
                . "<a href='" . url('cancerworksheet/approve/' . $worksheet_id) . "' title='Click to View Approved Results & Action for Samples in this Worksheet' target='_blank'>View Results</a> | "
                . "<a href='" . url('cancerworksheet/print/' . $worksheet_id) . "' title='Click to Print this Worksheet' target='_blank'>Print</a> ";

        }
        else if($status == 4 || $status == 5)
        {
            $d = "<a href='" . url('cancerworksheet/' . $worksheet_id) . "' title='Click to View Cancelled Worksheet Details' target='_blank'>Details</a> ";
        }
        else{
            $d = '';
        }
        return $d;
    }

    public function get_worksheets($worksheet_id=NULL)
    {
        if(!$worksheet_id) return false;
        $samples = CancerSampleView::selectRaw("count(*) as totals, worksheet_id, result")
            ->whereNotNull('worksheet_id')
            ->when($worksheet_id, function($query) use ($worksheet_id){                
                if (is_array($worksheet_id)) {
                    return $query->whereIn('worksheet_id', $worksheet_id);
                }
                return $query->where('worksheet_id', $worksheet_id);
            })
            ->where('receivedstatus', '!=', 2)
            ->where('site_entry', '!=', 2)
            ->groupBy('worksheet_id', 'result')
            ->get();

        return $samples;
    }

    public function get_reruns($worksheet_id=NULL)
    {
        if(!$worksheet_id) return false;
        $samples = CancerSampleView::selectRaw("count(*) as totals, worksheet_id")
            ->whereNotNull('worksheet_id')
            ->when($worksheet_id, function($query) use ($worksheet_id){                
                if (is_array($worksheet_id)) {
                    return $query->whereIn('worksheet_id', $worksheet_id);
                }
                return $query->where('worksheet_id', $worksheet_id);
            })
            ->where('parentid', '>', 0)
            ->where('receivedstatus', '!=', 2)
            ->where('site_entry', '!=', 2)
            ->groupBy('worksheet_id')
            ->get();

        return $samples;
    }

    private function get_samples_for_run($limit = 94){
        $samples = CancerSample::whereNull('worksheet_id')->where('receivedstatus', '<>', 2)->whereNull('result')
                                    ->where('site_entry', '<>', 2)
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
