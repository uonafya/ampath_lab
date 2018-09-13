<?php

namespace App;

use GuzzleHttp\Client;
use Carbon\Carbon;

use App\Common;
use App\Viralsample;
use App\ViralsampleView;

use App\Viralworksheet;

use App\DrPatient;
use App\Lookup;

class MiscViral extends Common
{

    protected $rcategories = [
        '0' => [],
        '1' => ['< LDL copies/ml', '< LDL copies', ],
        '2' => ['<550', '< 550 ', '<150', '<160', '<75', '<274', '<400', ' <400', '< 400', '<188', '<218', '<839', '< 21', '<40', '<20', '>20', '< 20', '22 cp/ml', '<218', '<1000'],
        '3' => ['>1000'],
        '4' => ['> 10000000', '>10,000,000', '>10000000', '>10000000'],
        '5' => ['Failed', 'failed', 'Failed PREP_ABORT', 'Failed Test', 'Invalid', 'Collect New Sample', 'Collect New sample']
    ];

    protected $compound_categories = [
        [
            'search_array' =>  ['Target  Not Detected', 'Target N ot Detected', 'Target Not  Detected', 'Target Not Detecetd', 'Target Not Detected', '< LDL copies/ml', '< LDL copies', 'Not Detected', '< LDL copies/ml', '<LDL copies/ml', '< LDL copies/ml', ' < LDL copies/ml', '< LDL'],
            'update_array' => ['rcategory' => 1, 'result' => '< LDL copies/ml', 'interpretation' => 'Target  Not Detected']
        ],
        [
            'search_array' =>  ['Less than 20 copies/ml', 'Less than Low Detectable Level'],
            'update_array' => ['rcategory' => 1, 'result' => '< LDL copies/ml', 'interpretation' => 'Less than 20 copies/ml']
        ],
        [
            'search_array' =>  ['REJECTED'],
            'update_array' => ['rcategory' => 5, 'result' => 'Collect New Sample', 'interpretation' => 'REJECTED']
        ],
        [
            'search_array' =>  ['Aborted'],
            'update_array' => ['rcategory' => 5, 'result' => 'Collect New Sample', 'interpretation' => 'Aborted']
        ],
        [
            'search_array' =>  ['REJECTED', 'Redraw New Sample', 'collect new samp', 'collect new saple', 'insufficient', 'Failed Collect New sample', 'failed', 'Collect New Sample'],
            'update_array' => ['rcategory' => 5, 'result' => 'Collect New Sample', 'labcomment' => 'Failed Test']
        ],
    ];

	public static function requeue($worksheet_id)
	{
		$samples = Viralsample::where('worksheet_id', $worksheet_id)->get();

        Viralsample::where('worksheet_id', $worksheet_id)->update(['repeatt' => 0]);

		// Default value for repeatt is 0

		foreach ($samples as $sample) {
			if($sample->result == "Failed" || $sample->result == "Invalid" || $sample->result == ""){
				$sample->repeatt = 1;
				$sample->save();
			}
		}
		return true;
	}

	public static function save_repeat($sample_id)
	{
        $original = Viralsample::find($sample_id);
        if($original->run == 5) return false;

		$sample = new Viralsample;        
        $fields = \App\Lookup::viralsamples_arrays();
        $sample->fill($original->only($fields['sample_rerun']));
        $sample->run++;
        if($original->parentid == 0) $sample->parentid = $original->id;
        
		$sample->save();
		return $sample;
	}

	public static function check_batch($batch_id, $issample=FALSE)
	{		
        if($issample){
            $sample = Viralsample::find($batch_id);
            $batch_id = $sample->batch_id;
        }
        $double_approval = \App\Lookup::$double_approval; 
        if(in_array(env('APP_LAB'), $double_approval)){
            $where_query = "( receivedstatus=2 OR  (result IS NOT NULL AND result != 'Failed' AND result != '' AND repeatt = 0 AND approvedby IS NOT NULL AND approvedby2 IS NOT NULL) )";
        }
        else{
            $where_query = "( receivedstatus=2 OR  (result IS NOT NULL AND result != 'Failed' AND result != '' AND repeatt = 0 AND approvedby IS NOT NULL) )";
        }


		$total = Viralsample::where('batch_id', $batch_id)->where('parentid', 0)->get()->count();
		$tests = Viralsample::where('batch_id', $batch_id)
		->whereRaw($where_query)
		->get()
		->count();

		if($total == $tests){ 
            // DB::table('viralbatches')->where('id', $batch_id)->update(['batch_complete' => 2]);
			\App\Viralbatch::where('id', $batch_id)->update(['batch_complete' => 2]);
            return true;
            // self::save_tat(\App\SampleView::class, \App\Sample::class, $batch_id);
		}
        else{
            return false;
        }
	}

	public static function check_original($sample_id)
	{
		$lab = auth()->user()->lab_id;

		$sample = Viralsample::select('samples.*')
		->join('batches', 'samples.batch_id', '=', 'batches.id')
		->where(['batches.lab_id' => $lab, 'samples.id' => $sample_id])
		->get()
		->first();

		return $sample;
	}

	public static function check_previous($sample_id)
	{
		$lab = auth()->user()->lab_id;

		$samples = Viralsample::select('samples.*')
		->join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')
		->where(['lab_id' => $lab, 'parentid' => $sample_id])
		->get();

		return $samples;
	}

	public static function check_run($sample_id, $run=2)
	{
		$lab = auth()->user()->lab_id;

		$sample = Viralsample::select('samples.*')
		->join('viralbatches', 'viralsamples.batch_id', '=', 'viralbatches.id')
		->where(['lab_id' => $lab, 'parentid' => $sample_id, 'run' => $run])
		->get()
		->first();

		return $sample;
	}


    public static function get_totals($result, $batch_id=NULL, $complete=true)
    {
        $samples = Viralsample::selectRaw("count(*) as totals, batch_id")
            ->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                if (is_array($batch_id)) {
                    return $query->whereIn('batch_id', $batch_id);
                }
                else{
                    return $query->where('batch_id', $batch_id);
                }
            })
            ->when(true, function($query) use ($result){
                if ($result == 0) {
                    return $query->whereRaw("(result is null or result = '')");
                }
                else if ($result == 1) {
                    return $query->where('result', '< LDL copies/ml');
                }
                else if ($result == 2) {
                    return $query->where('result', '!=', 'Failed')
                    ->where('result', '!=', 'Collect New Sample')
                    ->where('result', '!=', '< LDL copies/ml')
                    ->where('result', '!=', '')
                    ->whereNotNull('result');
                }
                else if ($result == 3) {
                    return $query->where('result', 'Failed');
                } 
                else if ($result == 5) {
                    return $query->where('result', 'Collect New Sample');
                }               
            })
            ->when($complete, function($query){
                return $query->where('batch_complete', 2);
            })
            ->whereRaw("(receivedstatus != 2 or receivedstatus is null)")
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }
    

    public static function get_subtotals($batch_id=NULL, $complete=true)
    {
        $samples = Viralsample::selectRaw("count(viralsamples.id) as totals, batch_id, rcategory")
            ->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                if (is_array($batch_id)) {
                    return $query->whereIn('batch_id', $batch_id);
                }
                else{
                    return $query->where('batch_id', $batch_id);
                }
            })
            ->when($complete, function($query){
                return $query->where('batch_complete', 2);
            })
            ->where('repeatt', 0)
            ->whereRaw("(receivedstatus != 2 or receivedstatus is null)")
            ->groupBy('batch_id', 'rcategory')
            ->get();

        return $samples;
    }

    public static function sample_result($result, $error)
    {
        if($result == 'Not Detected' || $result == 'Target Not Detected' || $result == 'Not detected' || $result == '<40 Copies / mL' || $result == '< 40Copies / mL ' || $result == '< 40 Copies/ mL')
        {
            $res= "< LDL copies/ml";
            $interpretation= $result;
            $units="";                        
        }

        else if($result == 'Collect New Sample')
        {
            $res= "Collect New Sample";
            $interpretation="Collect New Sample";
            $units="";                         
        }

        else if($result == 'Failed' || $result == '')
        {
            $res= "Failed";
            $interpretation = $error;
            $units="";                         
        }

        else{
            $res = preg_replace("/[^<0-9]/", "", $result);
            $interpretation = $result;
            $units="cp/mL";
        }

        return ['result' => $res, 'interpretation' => $interpretation, 'units' => $units];
    }

    public static function exponential_result($result)
    {
        if($result == 'Invalid'){
            $res= "Collect New Sample";
            $interpretation="Invalid";
            $units="";              
        }
        else if($result == '< Titer min' || $result == 'Target Not Detected'){
            $res= "< LDL copies/ml";
            $interpretation= $result;
            $units="";            
        }
        else{
            $a = explode('e+', $result);
            $u = explode(' ', $a[1]);
            $power = (int) $u[0];
            $res = (int) $a[0] * (10**$power);
            $interpretation = $result;
            $units = $u[1] ?? 'cp/mL';
        }

        return ['result' => $res, 'interpretation' => $interpretation, 'units' => $units];
    }

    

    public static function get_rejected($batch_id=NULL, $complete=true)
    {
        $samples = Viralsample::selectRaw("count(viralsamples.id) as totals, batch_id")
            ->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                if (is_array($batch_id)) {
                    return $query->whereIn('batch_id', $batch_id);
                }
                else{
                    return $query->where('batch_id', $batch_id);
                }
            })
            ->when($complete, function($query){
                return $query->where('batch_complete', 2);
            })
            ->where('receivedstatus', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }

    public static function get_maxdatemodified($batch_id=NULL, $complete=true)
    {
        $samples = Viralsample::selectRaw("max(datemodified) as mydate, batch_id")
            ->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                if (is_array($batch_id)) {
                    return $query->whereIn('batch_id', $batch_id);
                }
                else{
                    return $query->where('batch_id', $batch_id);
                }
            })
            ->when($complete, function($query){
                return $query->where('batch_complete', 2);
            })
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }

    public static function get_maxdatetested($batch_id=NULL, $complete=true)
    {
        $samples = Viralsample::selectRaw("max(datetested) as mydate, batch_id")
            ->join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')
            ->when($batch_id, function($query) use ($batch_id){
                if (is_array($batch_id)) {
                    return $query->whereIn('batch_id', $batch_id);
                }
                else{
                    return $query->where('batch_id', $batch_id);
                }
            })
            ->when($complete, function($query){
                return $query->where('batch_complete', 2);
            })
            ->where('receivedstatus', '!=', 2)
            ->groupBy('batch_id')
            ->get();

        return $samples;
    }



    public function set_justification($justification = null)
    {
        if($justification == 0) return 8;
        return $justification;
    }

    public function set_prophylaxis($prophylaxis = null)
    {
        if($prophylaxis == 0) return 16;
        return $prophylaxis;
    }

    public function set_age_cat($age = null)
    {
        if($age > 0.00001 && $age < 2) return 6; 
        else if($age >= 2 && $age < 10) return 7; 
        else if($age >= 10 && $age < 15) return 8; 
        else if($age >= 15 && $age < 20) return 9; 
        else if($age >= 19 && $age < 25) return 10;
        else if($age >= 25) return 11;
        else{ return 0; }
    }

    public function set_rcategory($result, $repeatt=null)
    {
        if(!$result) return ['rcategory' => 0];
        $numeric_result = preg_replace('/[^0-9]/', '', $result);
        if(is_numeric($numeric_result)){
            $result = (int) $numeric_result;
            if($result > 0 && $result < 1001) return ['rcategory' => 2];
            else if($result > 1000 && $result < 5001) return ['rcategory' => 3];
            else if($result > 5000) return ['rcategory' => 4];
        }
        $data = $this->get_rcategory($result);
        if(!isset($data['rcategory'])) dd($result);
        if($repeatt == 0 && $data['rcategory'] == 5) $data['labcomment'] = 'Failed Test';
        return $data;
    }
    

    public function get_rcategory($result)
    {
        foreach ($this->compound_categories as $key => $value) {
            if(in_array($result, $value['search_array'])) return $value['update_array'];
        }

        foreach ($this->rcategories as $key => $value) {
            if(in_array($result, $value)) return ['rcategory' => $key];
        }
        return [];
    }

    public static function generate_dr_list()
    {
        ini_set("memory_limit", "-1");

        $min_date = Carbon::now()->subMonths(3)->toDateString();

        $samples = ViralsampleView::select('patient_id', 'datereceived', 'result', 'rcategory', 'age', 'pmtct', 'datetested')
            ->where('batch_complete', 1)
            ->whereIn('rcategory', [3, 4])
            ->where('datereceived', '>', $min_date)
            // ->whereYear('datereceived', date('Y'))
            ->where('repeatt', 0)
            ->whereRaw("patient_id NOT IN (SELECT distinct patient_id from dr_patients)")
            ->get();

        foreach ($samples as $sample) {
            $data = $sample->only(['patient_id', 'datereceived', 'result', 'rcategory']);
            if($sample->age < 19){
                $pat = new DrPatient;
                $pat->fill($data);
                $pat->dr_reason_id = 2;
                $pat->save();
                continue;
            }
            else if($sample->pmtct == 1 || $sample->pmtct == 2){
                $pat = new DrPatient;
                $pat->fill($data);
                $pat->dr_reason_id = 3;
                $pat->save();
                continue;
            }
            else{
                if(self::get_previous_test($sample->patient_id, $sample->datetested)){
                    $pat = new DrPatient;
                    $pat->fill($data);
                    $pat->dr_reason_id = 1;
                    $pat->save();
                    continue; 
                }
            }
        }
    }

    public static function get_previous_test($patient_id, $datetested)
    {
        /*$sql = "SELECT * FROM viralsamples WHERE patient_id={$patient_id} AND datetested=
                    (SELECT max(datetested) FROM viralsamples WHERE patient_id={$patient_id} AND repeatt=0  AND rcategory between 1 AND 4 AND datetested < '{$datetested}')
        "; 


        $sample = \DB::select($sql)->first();*/

        $sample = Viralsample::where('patient_id', $patient_id)
                    ->whereRaw("datetested=
                    (SELECT max(datetested) FROM viralsamples WHERE patient_id={$patient_id} AND repeatt=0  AND rcategory between 1 AND 4 AND datetested < '{$datetested}')")
                    ->get()->first();

        if(!$sample || $sample->rcategory == 1 || $sample->rcategory == 2) return false;

        $recent_date = Carbon::parse($datetested);
        $prev_date = Carbon::parse($sample->datetested);

        $months = $recent_date->diffInMonths($prev_date);
        if($months < 3){

            /*$sql = "SELECT * FROM viralsamples WHERE patient_id={$patient_id} AND datetested=
                        (SELECT max(datetested) FROM viralsamples WHERE patient_id={$patient_id} AND repeatt=0  AND rcategory between 1 AND 4 AND datetested < '{$sample->datetested}')
            "; 
            $sample = \DB::select($sql);*/

            $sample2 = Viralsample::where('patient_id', $patient_id)
                    ->whereRaw("datetested=
                    (SELECT max(datetested) FROM viralsamples WHERE patient_id={$patient_id} AND repeatt=0  AND rcategory between 1 AND 4 AND datetested < '{$sample->datetested}')")
                    ->get()->first();

            if(!$sample2 || $sample2->rcategory == 1 || $sample2->rcategory == 2) return false;

            return true;
        }
        else{
            return true;
        }
        return false;
    }

    public static function patient_sms()
    {
        ini_set("memory_limit", "-1");
        $samples = ViralsampleView::whereNotNull('patient_phone_no')
                    ->where('patient_phone_no', '!=', '')
                    ->whereNull('time_result_sms_sent')
                    ->where('batch_complete', 1)
                    ->where('datereceived', '>', '2018-05-01')
                    ->get();

        foreach ($samples as $key => $sample) {
            if($sample->receivedstatus == 1 && !$sample->rcategory) continue;
        }
    }

    public static function send_sms($sample)
    {
        // English
        if($sample->preferred_language == 1){
            if($sample->rcategory == 1 || $sample->rcategory == 2){
                if($sample->age > 15 && $sample->age < 24){
                    $message = $sample->patient_name . ", Congratulations your VL is good, remember to keep your appointment date!!!";
                }
                else{
                    $message = $sample->patient_name . ", Congratulations!Your VL is good! Continue taking your drugs and keeping your appointment as instructed by the doctor.";                        
                }
            }
            else if($sample->rcategory == 3 || $sample->rcategory == 4){
                if($sample->age > 15 && $sample->age < 24){
                    $message = $sample->patient_name . ", Your VL results are ready. Please come to the facility as soon you can!";
                }
                else{
                    $message = $sample->patient_name . ", Your VL results are ready. Please visit the health facility as soon as you can.";                        
                }
            }
            else if($sample->rcategory == 5 || $sample->receivedstatus == 2){
                $message = $sample->patient_name . " Jambo,  please come to the clinic as soon as you can! Thank you.";
            }
        }
        // Kiswahili
        else{
            if($sample->rcategory == 1 || $sample->rcategory == 2){
                if($sample->age > 15 && $sample->age < 24){
                    $message = $sample->patient_name . ", Pongezi! Matokeo yako ya VL iko kiwango kizuri! Endelea kuzingatia maagizo!";
                }
                else{
                    $message = $sample->patient_name . ", Pongezi! Matokeo yako ya VL iko kiwango kizuri! Endelea kuzingatia maagizo ya daktari. Kumbuka tarehe yako ya kuja cliniki!";                        
                }
            }
            else if($sample->rcategory == 3 || $sample->rcategory == 4){
                if($sample->age > 15 && $sample->age < 24){
                    $message = $sample->patient_name . ", Matokeo yako ya VL yako tayari. Tafadhali tembelea kituo!";
                }
                else{
                    $message = $sample->patient_name . ", Matokeo yako ya VL yako tayari. Tafadhali tembelea kituo cha afya umwone daktari!";                        
                }
            }
            else if($sample->rcategory == 5 || $sample->receivedstatus == 2){
                $message = $sample->patient_name . " Jambo, kuja kliniki utakapoweza. Asante.";
            }             
        }

        if(!$message) return;

        $client = new Client(['base_uri' => self::$sms_url]);

        $response = $client->request('post', '', [
            'auth' => [env('SMS_USERNAME'), env('SMS_PASSWORD')],
            'http_errors' => false,
            'json' => [
                'sender' => env('SMS_SENDER_ID'),
                'recipient' => $sample->patient_phone_no,
                'message' => $message,
            ],
        ]);

        $body = json_decode($response->getBody());
        if($response->getStatusCode() == 201){
            $s = Viralsample::find($sample->id);
            $s->time_result_sms_sent = date('Y-m-d H:i:s');
            $s->pre_update();
        }
    }

    public static function get_worksheet_samples($machine_type, $calibration, $sampletype, $temp_limit=null)
    {
        $machines = Lookup::get_machines();
        $machine = $machines->where('id', $machine_type)->first();

        $test = in_array(env('APP_LAB'), Lookup::$worksheet_received);
        $user = auth()->user();

        if($machine == NULL || $machine->vl_limit == NULL) return false;
        // session(['toast_message' => 'An error has occurred.', 'toast_error' => 1]);

        $limit = $machine->vl_limit;
        if($calibration) $limit = $machine->vl_calibration_limit;

        if($temp_limit) $limit = $temp_limit;
        
        $year = date('Y') - 1;
        if(date('m') < 7) $year --;
        $date_str = $year . '-12-31';

        if($test){
            $repeats = ViralsampleView::selectRaw("viralsamples_view.*, facilitys.name, users.surname, users.oname, IF(parentid > 0 OR parentid=0, 0, 1) AS isnull")
                ->leftJoin('users', 'users.id', '=', 'viralsamples_view.user_id')
                ->leftJoin('facilitys', 'facilitys.id', '=', 'viralsamples_view.facility_id')
                ->where('datereceived', '>', $date_str)
                ->when($sampletype, function($query) use ($sampletype){
                    if($sampletype == 1) return $query->whereIn('sampletype', [3, 4]);
                    if($sampletype == 2) return $query->whereIn('sampletype', [1, 2]);                    
                })
                ->where('site_entry', '!=', 2)
                ->where('parentid', '>', 0)
                ->whereRaw("(worksheet_id is null or worksheet_id=0)")
                ->where('input_complete', true)
                ->whereIn('receivedstatus', [1, 3])
                ->whereRaw("(result IS NULL OR result='0')")
                ->orderBy('viralsamples_view.id', 'asc')
                ->limit($limit)
                ->get();
            $limit -= $repeats->count();
        }

        $samples = ViralsampleView::selectRaw("viralsamples_view.*, facilitys.name, users.surname, users.oname, IF(parentid > 0 OR parentid IS NULL, 0, 1) AS isnull")
            ->leftJoin('users', 'users.id', '=', 'viralsamples_view.user_id')
            ->leftJoin('facilitys', 'facilitys.id', '=', 'viralsamples_view.facility_id')
            ->where('datereceived', '>', $date_str)
            ->when($test, function($query) use ($user){
                return $query->where('received_by', $user->id)->where('parentid', 0);
            })
            ->when($sampletype, function($query) use ($sampletype){
                if($sampletype == 1) return $query->whereIn('sampletype', [3, 4]);
                if($sampletype == 2) return $query->whereIn('sampletype', [1, 2]);                    
            })
            ->where('site_entry', '!=', 2)
            ->whereRaw("(worksheet_id is null or worksheet_id=0)")
            ->where('input_complete', true)
            ->whereIn('receivedstatus', [1, 3])
            ->whereRaw("(result IS NULL OR result='0')")
            ->orderBy('isnull', 'asc')
            ->orderBy('highpriority', 'asc')
            ->orderBy('datereceived', 'asc')
            ->orderBy('site_entry', 'asc')
            ->orderBy('viralsamples_view.id', 'asc')
            ->limit($limit)
            ->get();

        if($test && $repeats->count() > 0) $samples = $repeats->merge($samples);
        $count = $samples->count();

        $create = false; 
        if($count == $machine->vl_limit || ($calibration && $count == $machine->vl_calibration_limit)) $create = true;

        return [
            'count' => $count,
            'create' => $create, 'machine_type' => $machine_type, 'calibration' => $calibration, 
            'sampletype' => $sampletype, 'machine' => $machine, 'samples' => $samples
        ];

    }
    
}
