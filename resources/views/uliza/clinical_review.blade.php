@extends('uliza.main_layout')

@section('content')

<div class="col-md-6">
	<div class="card mr-2">
		<div class="card-body">
			<div class="d-flex align-items-center justify-content-center p-1 text-white bg-primary rounded box-shadow">
				<div class="text-center">
					<h6 class="mb-0 text-white">Clinical Summary Form</h6>
				</div>
			</div>
			<div class="card my-1">
				<div class="card-body p-2">
					<div class="d-flex justify-content-between align-items-center w-100">
						<button class="btn btn-dark btn-sm" disabled="disabled" type="button">
							<strong>Facility: </strong>SOTIK HEALTH CENTRE
						</button>
					</div>
				</div>
			</div>
			<div class="ml-0 px-3" style="max-height:73vh; overflow-y: scroll;">
				<form autocomplete="off" novalidate="" class="ng-untouched ng-pristine ng-valid">
					<div class="form-row mb-3">
						<div class="col-md-7 input-group">
							<div class="input-group-prepend">
								<span class="input-group-text" id="facility_name">Facility Name:</span>
							</div>
							<input aria-describedby="facility_name" class="form-control" name="facility_name" readonly="" type="text">
						</div>
						<div class="col-md-5 input-group">
							<div class="input-group-prepend">
								<span class="input-group-text" id="mfl_code">MFL Code:</span>
							</div>
							<input aria-describedby="mfl_code" class="form-control" name="mfl_code" readonly="" type="text">
						</div>
					</div>
					<div class="form-row mb-3">
						<div class="col-md-7 input-group">
							<div class="input-group-prepend">
								<span class="input-group-text" id="cccno">
									Patient’s CCC No: <br>
									<small>(Do not write name)</small>
								</span>
							</div>
							<input aria-describedby="cccno" class="form-control" name="cccno" readonly="" type="text">
						</div>
						<div class="col-md-5 input-group">
							<div class="input-group-prepend">
								<span class="input-group-text" id="reporting_date">Case Reporting Date:</span>
							</div>
							<input class="form-control" name="reporting_date" readonly="">
						</div>
					</div>
					<div class="form-row mb-3">
						<div class="col-md-2">Patient Details</div>
						<div class="col-md-10">
							<div class="form-row mb-3">
								<div class="col-md-6 input-group">
									<div class="input-group-prepend">
										<span class="input-group-text" id="dob">Date of Birth:</span>
									</div>
									<input class="form-control" name="dob" readonly="">
								</div>
								<div class="col-md-6 input-group">
									<div class="input-group-prepend">
										<span class="input-group-text" id="artstart_date">ART Start Date:</span>
									</div>
									<input class="form-control" name="artstart_date" readonly="">
								</div>
							</div>
							<div class="form-row">
								<div class="col-md-4 input-group">
									<div class="input-group-prepend">
										<span class="input-group-text" for="gender">Gender:</span>
									</div>
									<input class="form-control" name="gender" readonly="">
								</div>
								<div class="col-md-4 input-group">
									<div class="input-group-prepend">
										<span class="input-group-text" id="curr_weight">Current Weight (Kg):</span>
									</div>
									<input aria-describedby="curr_weight" class="form-control" name="curr_weight" readonly="" type="text">
								</div>
								<div class="col-md-4 input-group">
									<div class="input-group-prepend">
										<span class="input-group-text" id="height">Height (cm):</span>
									</div>
									<input aria-describedby="height" class="form-control" name="height" readonly="" type="text">
								</div>
							</div>
						</div>
					</div>
					<div class="form-row mb-3">
						<div class="col-md-12 input-group">
							<div class="input-group-prepend">
								<span class="input-group-text text-left" id="clinician_name">Clinician’s Name:</span>
							</div>
							<input aria-describedby="clinician_name" class="form-control" name="clinician_name" readonly="" type="text">
						</div>
					</div>
					<div class="form-row mb-3">
						<div class="col-md-12 input-group">
							<div class="input-group-prepend">
								<span class="input-group-text" id="facility_email">Facility Email Address:</span>
							</div>
							<input aria-describedby="facility_email" class="form-control" name="facility_email" readonly="" type="text">
						</div>
					</div>
					<div class="form-row mb-3">
						<div class="col-md-5 input-group">
							<div class="input-group-prepend">
								<span class="input-group-text" id="facility_tel">Facility Tel No:</span>
							</div>
							<input aria-describedby="facility_tel" class="form-control" name="facility_tel" readonly="" type="text">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">What is the primary reason for this consultation:</label>
						<div class="col-md-8">
							<textarea class="form-control" name="primary_reason" readonly="" rows="3"></textarea>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Clinical Evaluation: history, physical, diagnostics, working diagnosis:(excluding the information in the table below)
						</label>
						<div class="col-md-8">
							<textarea class="form-control" name="clinical_eval" readonly="" rows="3"></textarea>
						</div>
					</div>
					<div class="form-row mb-3">

						<div class="col-md-12 card">
							<div class="card-header">
								Clinical Evaluation: history, physical, diagnostics, working diagnosis (excluding the information in the table below Complete the table below chronologically, including all ART regimens and laboratory results (and any previous history available for transfer-in patients)
							</div>
							<div class="card-body">
								<table class="table table-bordered table-hover table-sm m-0 p-0 ng-star-inserted" headertitle="Clinical Visits" id="cases-grid" indexcolumnheader="#" showtitle="false" _nghost-c10="">
									<thead>
										<tr>
											<th scope="col"> # </th>
											<th scope="col"> Date (dd/mm/yyyy) </th>
											<th scope="col"> CD4 </th>
											<th scope="col"> HB </th>
											<th scope="col"> CrCl/ eGFR </th>
											<th scope="col"> Viral Load </th>
											<th scope="col"> Weight </th>
											<th scope="col"> ARV Regimen </th>
											<th scope="col"> Reason for Switch </th>
											<th scope="col"> New OI </th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<div class="form-row mb-3">
						<div class="col-md-12 input-group">
							<p class="font-weight-bold">
								Adherence and Treatment Failure Evaluation
							</p>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Number of adherence counseling/assessment sessions done in the last 3-6 months:
						</label>
						<div class="col-md-8">
							<input class="form-control" name="no_adhearance_counseling" readonly="" type="number">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">Number of home visits conducted in last 3-6 months, and findings:</label>
						<div class="col-md-8">
							<input class="form-control" name="no_homevisits" readonly="" type="number">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Support structures (e.g. treatment buddy, support group attendance, caregivers) in place for this patient?
						</label>
						<div class="col-md-8">
							<textarea class="form-control" name="support_structures" readonly="" rows="4"></textarea>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">Evidence of adherence concerns (e.g. missed appointments, pill counts?):</label>
						<div class="col-md-8">
							<textarea class="form-control" name="adherence_concerns" readonly="" rows="4"></textarea>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">Number of DOTS done in last 3-6 months:</label>
						<div class="col-md-8">
							<input class="form-control" name="no_dotsdone" readonly="" type="number">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Likely root cause/s of poor adherence, for this patient (e.g. stigma, disclosure, side effects, alcohol or other drugs, mental health issues, caregiver changes, religious beliefs, inadequate preparation, etc):
						</label>
						<div class="col-md-8">
							<textarea class="form-control" name="likely_rootcauses" readonly="" rows="4"></textarea>
						</div>
					</div>
					<div class="form-row mb-3">
						<div class="col-md-12 input-group">
							<p class="font-weight-bold">
								Evaluation for other causes of treatment failure, e.g.
							</p>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">Inadequate dosing/dose adjustments (particularly for children)::</label>
						<div class="col-md-8">
							<textarea class="form-control" name="inadequate_dosing" readonly="" rows="4"></textarea>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">Drug-drug interactions:</label>
						<div class="col-md-8">
							<textarea class="form-control" name="drug_interactions" readonly="" rows="4"></textarea>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">Drug-food interactions:</label>
						<div class="col-md-8">
							<textarea class="form-control" name="food_interactions" readonly="" rows="4"></textarea>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">Impaired absorption (e.g. chronic severe diarrhea):</label>
						<div class="col-md-8">
							<textarea class="form-control" name="impaired_absorption" readonly="" rows="4"></textarea>
						</div>
					</div>
					<div class="form-row mb-3">
						<div class="col-md-12 input-group">
							<p class="font-weight-bold">Other Relevant ART History.</p>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">Comment on treatment interruptions, if any:</label>
						<div class="col-md-8">
							<textarea class="form-control" name="treatment_interruptions" readonly="" rows="4"></textarea>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Has Drug Resistance/Sensitivity Testing been done for this patient? If yes, state date done and attach the detailed results.
						</label>
						<div class="col-md-8">
							<textarea class="form-control" name="drt_testing" readonly="" rows="4"></textarea>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Has facility multidisciplinary team discussed the patient’s case?. If yes, comment on date, deliberations and recommendations.
							<br>
							(indicate how treatment failure was established and confirmed, proposed regimen and dosage, current source of drugs if patient already on 3rd line). If yes, state date done and attach the detailed results:
						</label>
						<div class="col-md-8">
							<textarea class="form-control" name="mdt_discussions" readonly="" rows="6"></textarea>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							MDT members who participated in the case discussion (names and titles)
						</label>
						<div class="col-md-8">
							<textarea class="form-control" name="mdt_members" readonly="" rows="4"></textarea>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>







<div class="col-md-6">

	<div class="card mr-2">
		<div class="card-body">
			<div class="d-flex align-items-center justify-content-center p-1 text-white bg-success rounded box-shadow">
				<div class="text-center">
					<h6 class="mb-0 text-white">Additional Information Form</h6>
				</div>
			</div>
			<div class="card mt-1">
				<div class="card-body">
					<table class="table data-table">
						<thead>
							<tr>
								<th> (dd/mm/yyyy) Date </th>
								<th> Time </th>
								<th> Status </th>
								<th> Actions </th>
							</tr>
						</thead>
					</table>
				</div>
			</div>
			<br>
			<div class="card mr-2">
				<div class="card-body">
					<div class="d-flex align-items-center justify-content-center p-1 text-white bg-success rounded box-shadow">
						<div class="text-center">
							<h6 class="mb-0 text-white">Clinical TWG Feedback Form</h6>
						</div>
					</div>
					<div class="card my-1 ml-2">
						<div class="card-body p-2">
							<div class="d-flex justify-content-end align-items-center w-100">
								<button class="btn btn-warning btn-sm" type="button" disabled="">
									Submit Review
								</button>
							</div>
						</div>
					</div>

					<div class="ml-2 px-3" style="overflow-y: scroll; max-height: 73vh;">
						<form autocomplete="off" novalidate="" class="ng-untouched ng-pristine ng-invalid">

							<div class="form-row mb-3">
								<div class="col-md-12 input-group">
									<div class="input-group-prepend">
										<span class="input-group-text text-left" id="facility_name">Facility Name:</span>
									</div>
									<input aria-describedby="facility_Name" class="form-control" name="facility_name" readonly="" type="text">
								</div>
							</div>

							<div class="form-row mb-3">
								<div class="col-md-12 input-group">
									<div class="input-group-prepend">
										<span class="input-group-text text-left" id="cccno">Patient's CCCNo:</span>
									</div>
									<input aria-describedby="cccno" class="form-control" name="cccno" readonly="" type="text">
								</div>
							</div>

							<div class="form-row mb-3">
								<div class="col-md-12 input-group">
									<div class="input-group-prepend">
										<span class="input-group-text text-left" id="clinician_name">Name of Clinician Consulting:</span>
									</div>
									<input aria-describedby="clinician_name" class="form-control" name="clinician_name" readonly="" type="text">
								</div>
							</div>

							<div class="form-row mb-3">
								<div class="col-md-12 input-group">
									<div class="input-group-prepend">
										<span class="input-group-text text-left" id="facility_email">Contact details of clinician (email) :</span>
									</div>
									<input aria-describedby="facility_email" class="form-control" name="facility_email" readonly="" type="text">
								</div>
							</div>

							<div class="form-row mb-3">
								<div class="col-md-12 input-group">
									<div class="input-group-prepend">
										<span class="input-group-text text-left" id="facility_tel">
											Contact details of clinician (telephone):
										</span>
									</div>
									<input aria-describedby="facility_tel" class="form-control" name="facility_tel" readonly="" type="text">
								</div>
							</div>

							<div class="form-row mb-3">
								<div class="col-md-12 input-group">
									<div class="input-group-prepend">
										<span class="input-group-text text-left" id="artstart_date">ART Start Date :</span>
									</div>
									<input aria-describedby="artstart_date" class="form-control" name="artstart_date" readonly="" type="text">
								</div>
							</div>

							<div class="form-row mb-3">
								<div class="col-md-12 input-group">
									<div class="input-group-prepend">
										<span class="input-group-text text-left" id="natno">NASCOP's NAT-No :</span>
									</div>
									<input aria-describedby="natno" class="form-control" name="natno" readonly="" type="text">
								</div>
							</div>

							<div class="form-row mb-3 required">
								<div class="col-md-12 input-group">
									<div class="input-group-prepend">
										<span class="input-group-text text-left" for="review_date">Date of Review :</span>
									</div>
									<input bsdatepicker="" class="form-control" name="review_date" required="" type="text">
								</div>
							</div>

							<div class="form-row mb-3">
								<div class="col-md-12 input-group">
									<p class="font-weight-bold">A. Case Summary of consultation</p>
									<br>
									<p class="font-italic">
										(A summary of the clinical consultation and reason for consultation or description of the problem for which consultation
										is sought.) 
										<br>
										Note: this section is primarily to give the TWG reviewers a snap-shot of the patient history. This is a summary of the known information, but not an interpretation/judgment of the management:
									</p>
								</div>
							</div>

							<div class="form-group row">
								<label class="col-md-4 col-form-label">Case Summary of consultation:</label>
								<div class="col-md-8">
									<textarea class="form-control" name="casesummary" required="" rows="5"></textarea>
								</div>
							</div>

							<div class="form-row mb-3">
								<div class="col-md-12 input-group">
									<p class="font-weight-bold">
										B. Observations/Interpretation on summary provided. A summary of observations about the management the patient has received.
									</p>
									<br>
									<p class="font-italic">
										(Should include interpretation of clinical parameters e.g. weight changes or clinical symptoms and presentations, interpretation of laboratory data, radiologic or other investigations, observation of how patient has been managed etc.) <br />
										Note: this section is primarily for teaching purposes for the facility staff, to show the thought process of the TWG reviewer when evaluating the patient history. <br>
									</p>

									<p class="font-weight-bold">
										Comment on what was done well and any apparent gaps in care were. Use bullet points for ease of reading.
									</p>
								</div>
							</div>

							<div class="form-group row">
								<label class="col-md-4 col-form-label">Observations/Interpretation on summary provided:</label>
								<div class="col-md-8">
									<textarea class="form-control" name="observationsofsummary" required="" rows="5"></textarea>
								</div>
							</div>

							<div class="form-row mb-3">
								<div class="col-md-12 input-group">
									<p class="font-weight-bold">C. Recommendations for management:</p>
								</div>
							</div>

							<div class="form-row mb-3 required">
								<label class="col-md-12">Diagnosis:</label>
							</div>

							<div class="form-row form-group mb-3">
								@foreach($reasons as $reason)
									<div class="col-md-6">
										<input class="form-check-input ml-1" v-model="myForm.diagnosis" name="diagnosis" required="required" type="radio" id="diagnosis_A{{ $reason->id }}" value="{{ $reason->id }}">
										<label class="form-check-label ml-5" for="diagnosis_A{{ $reason->id }}">{{ $reason->name }}</label>
									</div>
								@endforeach
							</div>


							<div class="form-group row">
								<label class="col-md-4 col-form-label">
									Supportive Management: (Includes palliative care, social, psychosocial etc.)
								</label>
								<div class="col-md-8">
									<textarea class="form-control" name="supportivemanagement" required="" rows="5"></textarea>
								</div>
							</div>

							<div class="form-group row">
								<label class="col-md-4 col-form-label">Definative Management: (Includes recommended investigations, medicines).</label>
								<div class="col-md-8">
									<textarea class="form-control" name="definativemanagement" required="" rows="5"></textarea>
								</div>
							</div>

							<div class="form-group row">
								<label class="col-md-4 col-form-label">
									Additional Information Required:(Includes recommended investigations, medicines.)
								</label>
								<div class="col-md-8">
									<textarea class="form-control" name="additionalinfo" required="" rows="5"></textarea>
								</div>
							</div>

							<div class="form-group row ng-star-inserted">
								<label class="col-md-4 col-form-label">NASCOP Comments</label>
								<div class="col-md-8">
									<textarea class="form-control" name="nascop_comments" required="" rows="5"></textarea>
								</div>
							</div>


							<div class="form-group row ng-star-inserted">
								<div class="col-md-6">
									<label class=" col-form-label">
										Case-Summary (Recomendation feedback)
									</label>
								</div>

								<div class="col-md-6">
									<select class="custom-select" name="recommendation_id" required="">
										<option selected="">Choose...</option>
										<option value="1" class="ng-star-inserted">Additional Information Required From Facility</option>
										<option value="5" class="ng-star-inserted">Additional Information Required From RTWG</option>
										<option value="3" class="ng-star-inserted">Provide Feedback To Facility Directly</option>
										<option value="2" class="ng-star-inserted">Refer To Technical Reviewer</option>
										<option value="6" class="ng-star-inserted">Send Feedback To RTWG</option>
									</select>
								</div>
							</div>


						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection