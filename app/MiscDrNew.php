<?php

namespace App;

use Illuminate\Support\Facades\Mail;
use App\Mail\DrugResistanceResult;
use App\Mail\DrugResistance;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use DB;
use Str;

class MiscDrNew extends Common
{

	public static $hyrax_url = 'https://sanger.api.exatype.com/sanger/v2'; 
	public static $ui_url = 'https://sanger.exatype.com';

	public static function get_hyrax_key()
	{
		if(Cache::has('dr_api_v2_token')){}
		else{
			self::login();
		}
		return Cache::get('dr_api_v2_token');
	}

	public static function login()
	{
		Cache::forget('dr_api_v2_token');
		$client = new Client(['base_uri' => self::$hyrax_url]);

		$response = $client->request('POST', 'authorisations', [
            // 'debug' => true,
            'http_errors' => false,
            'connect_timeout' => 15,
			'headers' => [
				// 'Accept' => 'application/json',
			],
			'json' => [
				'data' => [
					'type' => 'authorisations',
					'attributes' => [
						'email' => env('DR_USERNAME'),
						'password' => env('DR_PASSWORD'),
					],
				],
			],
		]);

		// dd($response->getBody());		

		if($response->getStatusCode() < 400)
		{
			$body = json_decode($response->getBody());
			$key = $body->data->attributes->api_key ?? null;
			if(!$key) dd($body);
			Cache::put('dr_api_v2_token', $key, 60);
			return;
		}
		else{
			dd($response->getStatusCode());
			$body = json_decode($response->getBody());
			dd($body);
		}
	}


	public static function create_plate($worksheet)
	{
		ini_set('memory_limit', '-1');
		$client = new Client(['base_uri' => self::$hyrax_url]);

		$files = self::upload_worksheet_files($worksheet);

		$sample_data = $files['sample_data'];
		$errors = $files['errors'];

		if($errors){
			session(['toast_error' => 1, 'toast_message' => 'The upload has errors.']);
			return $errors;
		}

		$postData = [
				'data' => [
					'type' => 'job-create-v2',
					'attributes' => [
						'job_name' => "job_plate_{$worksheet->id}",
						'assay' => 'tf_cdc_pr_rt_in',
						'plates' => [
							{
								'plate_name' => "plate_{$worksheet->id}",
							}
						],
						'samples' => $sample_data,
					],
				],
			];

		// self::dump_log($postData);

		$response = $client->request('POST', 'jobs', [
            'http_errors' => false,
            // 'debug' => true,
			'headers' => [
				// 'Accept' => 'application/json',
				// 'x-hyrax-daemon-apikey' => self::get_hyrax_key(),
				'X-Hyrax-Apikey' => self::get_hyrax_key(),
			],
			'json' => $postData,
		]);

		return self::processResponse($worksheet, $response);
	}

	public static function upload_worksheet_files($worksheet)
	{
		$path = storage_path('app/public/results/dr/' . $worksheet->id . '/');

		// $samples = $worksheet->sample_view;
		$samples = $worksheet->sample;
		// $samples->load(['result']);

		$primers = ['F1', 'F2', 'F3', 'R1', 'R2', 'R3'];

		$sample_data = [];
		$print_data = [];
		$errors = [];

		$contigs = [
			'PRRT' => [
				'contig_array' => [
					'contig_name' => 'PRRT',
					'contig_alias' => 'PRRT',
					'contig_code' => 'PRRT',
					'plate_name' => "plate_{$worksheet->id}",
					'ab1s' => null,
				],
				'primers' => ['F1', 'F2', 'F3', 'R1', 'R2', 'R3']
			],
			'INT' => [
				'contig_array' => [
					'contig_name' => 'IN',
					'contig_alias' => 'INT',
					'contig_code' => 'IN',
					'plate_name' => "plate_{$worksheet->id}",
					'ab1s' => null,
				],
				'primers' => ['F1', 'F2', 'R1', 'R2']
			],
		];

		foreach ($samples as $key => $sample) {
			foreach ($contigs as $contig_key => $contig) {

				$s = [
					'sample_name' => "{$sample->mid}",
					'sample_type' => 'data',
					'contigs' => $contig->contig_array,
				];

				if($sample->control == 1) $s['sample_type'] = 'negative';
				if($sample->control == 2) $s['sample_type'] = 'positive';

				$abs = [];
				$abs2 = [];

				foreach ($primers as $primer) {
					$sample_file = self::find_ab_file($path, $sample, $primer);
					// if($ab) $abs[] = $ab;
					if($sample_file){
						$abs[] = [
							'primer_name' => $sample_file->primer_name,
							'file_link_id' => $sample_file->exatype_file_id,
							'path' => $sample_file->file_name,
						];
					}
					else{
						// $errors[] = "Sample {$sample->id} ({$sample->mid}) Primer {$primer} could not be found.";
						if(env('APP_LAB') == 1) $errors[] = "Sample {$sample->id} ({$sample->mid}) Primer {$primer} could not be found.";
						else{
							$errors[] = "Sample {$sample->id} ({$sample->nat}) Primer {$primer} could not be found.";
						}
					}
				}
				if(!$abs) continue;
				$s['contigs']['ab1s'] = $abs;
				$sample_data[] = $s;
			}
		}
		// self::dump_log($print_data);
		// die();
		return ['sample_data' => $sample_data, 'errors' => $errors];
	}

	public static function find_ab_file($path, $sample, $primer, $contig)
	{
		$files = scandir($path);
		if(!$files) return null;

		ini_set('memory_limit', '-1');
		$client = new Client(['base_uri' => self::$hyrax_url]);

		foreach ($files as $file) {
			if($file == '.' || $file == '..') continue;

			$new_path = $path . '/' . $file;
			if(is_dir($new_path)){
				$a = self::find_ab_file($new_path, $sample, $primer, $contig);

				if(!$a) continue;
				return $a;
			}
			else{
				if(\Str::startsWith($file, [$sample->mid . '-', $sample->mid . '_']) && \Str::contains($file, $primer))
				{
					$ab_file = fopen($new_path, 'r');
					$response = $client->request('POST', 'file-link/upload/', [
						'headers' => [
							'X-Hyrax-Apikey' => self::get_hyrax_key(),
						],
						'body' => $ab_file
					]);

					$body = json_decode($response->getBody());
					$sample_file = DrSampleFile::firstOrCreate(['sample_id' => $sample->id, 'primer' => $primer], ['sample_id' => $sample->id, 'primer' => $primer, 'exatype_file_id' => $body->attributes->key, 'file_name' => $file]);

					return $sample_file;
				}
				continue;
			}
		}
		return false;
	}

	public static function processResponse($worksheet, $response)
	{
		$body = json_decode($response->getBody());

		if($response->getStatusCode() >= 400){
			session(['toast_error' => 1, 'toast_message' => 'Something went wrong. Status code ' . $response->getStatusCode()]);
			return false;			
		}

		$worksheet->exatype_job_id = $body->data->attributes->data->id;
		$worksheet->plate_id = $body->data->attributes->data->plates[0]->id;
		$worksheet->time_sent_to_exatype = date('Y-m-d H:i:s');
		$worksheet->status_id = 5;
		$worksheet->save();

		foreach ($body->data->attributes->data->samples as $key => $value) {

			if(env('APP_LAB') == 100){
				$patient = \App\Viralpatient::where('patient', $value->sample_name)
					->whereRaw("id IN (SELECT patient_id FROM dr_samples WHERE worksheet_id={$worksheet->id})")
					->first();
				$sample = $patient->dr_sample()->first();
				if(!$sample){
					echo 'Cannot find ' . $value->sample_name . "\n";
					continue;
				}
				$sample->exatype_id = $value->id;
				$sample->save();
			}
			else{
				$sample_id = \Str::after($value->sample_name, env('DR_PREFIX', ''));
				$sample = DrSample::find($sample_id);
				if($sample->worksheet->id != $worksheet->id){
					if(env('APP_LAB') != 1) continue;
					$sample = DrSample::where(['worksheet_id' => $worksheet->id, 'parentid' => \Str::after($value->sample_name, env('DR_PREFIX', ''))])->first();
					if(!$sample) continue;
				}

				$sample->exatype_id = $value->id;
				$sample->save();
			}

			foreach ($value->contigs as $contig) {
				$sample->contig()->create([
					'exatype_id' => $contig->id,
					'contig' => $contig->contig_alias,
				]);
			}
		}
		session(['toast_message' => 'The worksheet has been successfully created at Exatype.']);
		return $body;
	}



	public static function get_plate_result($worksheet)
	{
		ini_set('memory_limit', '-1');
		$client = new Client(['base_uri' => self::$hyrax_url]);

		$response = $client->request('GET', "jobs/{$worksheet->exatype_job_id}", [
			'headers' => [
				// 'Accept' => 'application/json',
				'X-Hyrax-Apikey' => self::get_hyrax_key(),
			],
		]);

		$body = json_decode($response->getBody());

		/*$included = print_r($body, true);

		$file = fopen(public_path('dr_res.json'), 'w+');
		fwrite($file, $included);
		fclose($file);
		die();*/

		// dd($body);

		if($response->getStatusCode() != 200){
			session(['toast_error' => 1, 'toast_message' => 'Something went wrong. Status code ' . $response->getStatusCode()]);
			return false;
		}

		foreach ($body->included as $key => $value) {
			if($value->type == 'sanger-plate'){
				$worksheet->exatype_status_id = MiscDr::get_worksheet_status($value->status->id);
				$worksheet->save();
			}
			else if($value->type == 'basecall-result'){
				$contig = DrContig::where(['exatype_id' => $value->id])->first();
				foreach ($value->attributes->warnings as $warning) {
					self::create_warning(2, $contig, $warning, 0);
				}
				foreach ($value->attributes->warnings as $warning) {
					self::create_warning(2, $errors, $error, 1);
				}
			}
			else if($value->type == 'contig'){
				$contig = DrContig::where(['exatype_id' => $value->id])->first();
				$contig->exatype_status_id = MiscDr::get_contig_status($value->status->id);
				$contig->chromatogram_id = $value->chromatogram_id;
				$contig->save();
			}
			else if($value->type == 'drug-call-result'){
				$sample = DrSample::where(['exatype_id' => $value->id])->first();
				foreach ($value->attributes->drug_calls as $drug_call) {	
					$c = DrCall::firstOrCreate([
						'sample_id' => $sample->id,
						'drug_class' => $drug_call->drug_class,
						'drug_class_id' => self::get_drug_class($drug_call->drug_class),
					]);

					if(isset($call->mutations) && $call->mutations){
						$sample->has_mutations = true;
						$c->mutations = $call->mutations ?? [];
						$c->save();
					}

					foreach ($drug_call->drugs as $drug) {						
						$d = DrCallDrug::firstOrCreate([
							'call_id' => $c->id,
							'short_name' => $drug->short_name,
							'short_name_id' => MiscDr::get_short_name_id($drug->short_name),
							'call' => $drug->call,
							'score' => $drug->score ?? null,
						]);
					}
				}
			}
			else if($value->type == 'sample-qc'){
				$sample = DrSample::where(['exatype_id' => $value->id])->first();

			}
			else if($value->type == 'sanger-sample'){
				$sample = DrSample::where(['exatype_id' => $value->attributes->id])->first();
				$sample->pdf_download_link = $value->attributes->pdf_download->generate;
				$sample->status_id = MiscDr::get_sample_status($value->status->id);
				$sample->save();

			}
			else if($value->type == 'aligner-result'){
				$sample = DrSample::where(['exatype_id' => $value->id])->first();
				foreach ($value->attributes->aligned_sequence as $aligned_sequence) {
					$a = $aligned_sequence->read;
				}

			}
			else if($value->type == 'job-qc'){
				$sample = DrSample::where(['exatype_id' => $value->id])->first();

			}

		}

		$w = $body->data->attributes;
		$worksheet->exatype_job_status_id = MiscDr::get_job_status($w->status->id);
		$worksheet->plate_controls_pass = $w->plate_controls_pass;
		$worksheet->qc_run = $w->plate_qc_run;
		$worksheet->qc_pass = $w->plate_qc->pass ?? 0;
		$worksheet->qc_distance_pass = $w->plate_qc->distance_pass ?? 0;

		if($worksheet->exatype_status_id == 4) return null;

		if($worksheet->exatype_status_id != 5){

			if($w->errors){
				foreach ($w->errors as $error) {
					self::create_warning(1, $worksheet, $error);
				}
			}

			if($w->warnings){
				foreach ($w->warnings as $error) {
					self::create_warning(1, $worksheet, $error);
				}
			}
		}

		$worksheet->status_id = 6;
		$worksheet->save();

		// dd($body->included);

		foreach ($body->included as $key => $value) {

			$sample = DrSample::where(['exatype_id' => $value->id])->first();

			if(!$sample) continue;
			if(in_array($sample->status_id, [1])) continue;

			// echo " {$sample->id} ";

			// if($worksheet->exatype_status_id == 5 && !$worksheet->plate_controls_pass && !$sample->control) continue;

			$s = $value->attributes;
			$sample->status_id = self::get_sample_status($s->status_id);	

			if($sample->status_id == 3)	$sample->qc_pass = 0;			

			if(isset($s->sample_qc_pass)){
				$sample->qc_pass = $s->sample_qc_pass ?? null;

				$sample->qc_stop_codon_pass = $s->sample_qc->stop_codon_pass ?? null;
				$sample->qc_plate_contamination_pass = $s->sample_qc->plate_contamination_pass ?? null;
				$sample->qc_frameshift_codon_pass = $s->sample_qc->frameshift_codon_pass ?? null;
			}

			if(isset($s->sample_qc_distance)){
				$sample->qc_distance_to_sample = $s->sample_qc_distance[0]->to_sample_id ?? null;
				$sample->qc_distance_from_sample = $s->sample_qc_distance[0]->from_sample_id ?? null;
				$sample->qc_distance_difference = $s->sample_qc_distance[0]->difference ?? null;
				$sample->qc_distance_strain_name = $s->sample_qc_distance[0]->strain_name ?? null;
				$sample->qc_distance_compare_to_name = $s->sample_qc_distance[0]->compare_to_name ?? null;
				$sample->qc_distance_sample_name = $s->sample_qc_distance[0]->sample_name ?? null;
			}

			if(isset($s->errors) && $s->errors){
				$sample->has_errors = true;

				foreach ($s->errors as $error) {
					self::create_warning(2, $sample, $error);
				}
			}

			if(isset($s->warnings) && $s->warnings){
				$sample->has_warnings = true;

				foreach ($s->warnings as $error) {
					self::create_warning(2, $sample, $error);
				}
			}

			if(isset($s->calls) && $s->calls){
				// $sample->has_calls = true;

				foreach ($s->calls as $call) {
					// $c = DrCall::where(['sample_id' => $sample->id, 'drug_class' => $call->drug_class])->first();
					// if(!$c) $c = new DrCall;

					// $c->fill([
					// 	'sample_id' => $sample->id,
					// 	'drug_class' => $call->drug_class,
					// 	'other_mutations' => $call->other_mutations,
					// 	'major_mutations' => $call->major_mutations,
					// ]);

					// $c->save();

					// dd($call);

					$c = DrCall::firstOrCreate([
						'sample_id' => $sample->id,
						'drug_class' => $call->drug_class,
						'drug_class_id' => self::get_drug_class($call->drug_class),
						// 'mutations' => $call->mutations ?? [],
						// 'other_mutations' => self::escape_null($call->other_mutations),
						// 'major_mutations' => self::escape_null($call->major_mutations),
					]);

					if(isset($call->mutations) && $call->mutations){
						$sample->has_mutations = true;
						$c->mutations = $call->mutations ?? [];
						$c->save();
					}

					foreach ($call->drugs as $drug) {
						$d = DrCallDrug::firstOrCreate([
							'call_id' => $c->id,
							'short_name' => $drug->short_name,
							'short_name_id' => self::get_short_name_id($drug->short_name),
							'call' => $drug->call,
							'score' => $drug->score ?? null,
						]);
					}
				}
			}

			if(isset($s->genotype) && $s->genotype){
				// $sample->has_genotypes = true;

				foreach ($s->genotype as $genotype) {
					$g = DrGenotype::firstOrCreate([
						'sample_id' => $sample->id,
						'locus' => $genotype->locus,
					]);

					foreach ($genotype->residues as $residue) {
						$r = DrResidue::firstOrCreate([
							'genotype_id' => $g->id,
							'residue' => $residue->residues[0] ?? null,
							'position' => $residue->position,
						]);
					}
				}
			}

			if($s->pending_action == "PendChromatogramManualIntervention"){
				$sample->pending_manual_intervention = true;
			}

			if(!$s->pending_action && $sample->pending_manual_intervention){
				$sample->pending_manual_intervention = false;
				$sample->had_manual_intervention = true;
			}				

			$sample->assembled_sequence = $s->assembled_sequence ?? '';
			$sample->chromatogram_url = $s->chromatogram_url ?? '';
			$sample->exatype_version = $s->exatype_version ?? '';
			$sample->algorithm = $s->algorithm ?? '';
			// $sample->pdf_download_link = $s->sample_pdf_download->signed_url ?? '';
			$sample->save();

			// echo " {$sample->id} ";
		
		}
		session(['toast_message' => 'The worksheet results have been successfully retrieved from Exatype.']);
		return $body;

		// dd($body);
	}



	public static function create_warning($type, $model, $warning, $is_error = 0)
	{
		if($type == 1){
			$class = DrWorksheetWarning::class;
			$column = 'worksheet_id';
		}
		else if($type == 2){
			$class = DrContigWarning::class;
			$column = 'contig_id';					
		}
		else{
			$class = DrWarning::class;
			$column = 'sample_id';			
		}

		$e = $class::firstOrCreate([
			$column => $model->id,
			'warning_id' => self::get_sample_warning($warning, $is_error),
			'detail' => $warning->message ?? '',
		]);

		if(!$e->warning_id){
			$e->detail .= " error_name " . $warning->title;
			$e->save();
		}
		return $e;
	}

	public static function get_sample_warning($warning, $is_error)
	{
		$warning_id = DB::table('dr_warning_codes')->where(['code' => $warning->code])->first()->id ?? 0;
		if(!$warning_id){
			DB::table('dr_warning_codes')->insert(['code' => $warning->code, 'type' => $warning->type, 'message' => $warning->message, 'error' => $is_error]);
			return self::get_sample_warning($warning);
		}else{
			return $warning_id;
		}
	}
}
