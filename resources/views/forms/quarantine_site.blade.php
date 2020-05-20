@extends('layouts.master')

@component('/forms/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('custom_css')
    <style type="text/css">
        .hpanel {
            margin-bottom: 4px;
        }
        .hpanel.panel-heading {
            padding-bottom: 2px;
            padding-top: 4px;
        }
    </style>
@endsection

@section('content')

    <div class="content">
        <div>

        @if(isset($sample))
            <form method="POST" class="form-horizontal" action='{{ url("/covid_sample/{$sample->id}") }}' >
            @method('PUT')
        @else
            <form method="POST" class="form-horizontal" action='{{ url("/covid_sample/") }}'>
        @endif
            <?php $m = $sample ?? null; ?>

        @csrf

        @if ($errors->any())
            <div class="row">
                <div class="col-lg-12">
                    <div class="hpanel">
                        <div class="panel-body" style="padding-bottom: 6px;">
                            <div class="alert alert-danger">
                                <center>
                                    The sample was not saved due to the following errors: <br />
                                    @foreach ($errors->all() as $error)
                                        {{ $error }} <br />
                                    @endforeach
                                </center>
                            </div>
                        </div>
                    </div>
                </div>                
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-body" style="padding-bottom: 6px;">

                        <div class="alert alert-warning">
                            <center>
                                Please fill the form correctly. <br />
                                Fields with an asterisk(*) are mandatory.
                            </center>
                        </div>
                        <br />
  
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Facility</label>
                            <div class="col-sm-8">
                                <select class="form-control" name="facility_id" id="facility_id">
                                    @if(isset($sample) && $sample->patient->facility)
                                        <option value="{{ $sample->patient->facility->id }}" selected>{{ $sample->patient->facility->facilitycode }} {{ $sample->patient->facility->name }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        @include('partial.select', ['model' => $m, 'prop' => 'amrs_location', 'label' => '(*for Ampath Sites only) AMRS Location', 'items' => $amrslocations, 'form_class' => 'ampath-div'])

                        @include('partial.input', ['model' => $m, 'prop' => 'provider_identifier', 'label' => '(*for Ampath Sites only) AMRS Provider Identifier', 'form_class' => 'ampath-div'])

                        <div class="form-group">
                            <label class="col-sm-4 control-label">High Priority</label>
                            <div class="col-sm-8">
                            <input type="checkbox" class="i-checks" name="highpriority" value="1"
                                @if(isset($sample) && $sample->highpriority)
                                    checked
                                @endif
                             />
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                        <center>Patient Information</center>
                    </div>
                    <div class="panel-body" style="padding-bottom: 6px;">

                        @include('partial.select', ['model' => $m, 'default_val' => $sample->patient->nationality ?? null, 'prop' => 'nationality', 'label' => 'Nationality', 'items' => $nationalities])

                        @include('partial.select', ['model' => $m, 'default_val' => $sample->patient->identifier_type ?? null, 'prop' => 'identifier_type', 'label' => 'Identifier Type', 'items' => $identifier_types])

                        @include('partial.input', ['model' => $m, 'prop' => 'identifier', 'default_val' => $sample->patient->identifier ?? null, 'required' => true, 'label' => 'Patient Identifier'])

                        @include('partial.select', ['model' => $m, 'default_val' => $sample->patient->county_id ?? null, 'prop' => 'county_id', 'label' => 'County', 'items' => $countys])

                        @if(auth()->user()->quarantine_site)
                            <input type="hidden" name="quarantine_site_id" value="{{ auth()->user()->facility_id }}">
                        @else
                            @include('partial.select', ['model' => $m, 'default_val' => $sample->patient->quarantine_site_id ?? null, 'prop' => 'quarantine_site_id', 'label' => 'Quarantine Site', 'items' => $quarantine_sites])
                        @endif



                        @include('partial.input', ['model' => $m, 'prop' => 'patient_name', 'default_val' => $sample->patient->patient_name ?? null, 'label' => 'Patient Name'])

                        @include('partial.input', ['model' => $m, 'prop' => 'email_address', 'default_val' => $sample->patient->email_address ?? null, 'label' => 'Email Address'])

                        @include('partial.input', ['model' => $m, 'prop' => 'phone_no', 'default_val' => $sample->patient->phone_no ?? null, 'label' => 'Phone Number'])


                        @include('partial.date', ['model' => $m, 'prop' => 'dob', 'label' => 'Date of Birth', 'default_val' => $sample->patient->dob ?? null, 'class' => 'date-dob'])

                        @include('partial.input', ['model' => $m, 'prop' => 'age', 'is_number' => true, 'label' => 'Age'])

                        @include('partial.select', ['model' => $m, 'prop' => 'sex', 'default_val' => $sample->patient->sex ?? null, 'required' => true, 'label' => 'Sex', 'items' => $gender, 'prop2' => 'gender_description'])

                        @include('partial.input', ['model' => $m, 'prop' => 'residence', 'default_val' => $sample->patient->residence ?? null, 'label' => 'Area of Residence'])

                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                        <center>Clinical Information</center>
                    </div>
                    <div class="panel-body" style="padding-bottom: 6px;">

                        @include('partial.select', ['model' => $m, 'required' => true, 'prop' => 'justification', 'label' => 'Justification', 'items' => $covid_justifications, 'default_val' => $sample->patient->justification ?? null])

                        @include('partial.select', ['model' => $m, 'required' => true, 'prop' => 'test_type', 'label' => 'Test Type', 'items' => $covid_test_types, ])

                        @include('partial.date', ['model' => $m, 'prop' => 'date_symptoms', 'label' => 'Date Symptoms Began Showing', 'default_val' => $sample->patient->date_symptoms ?? null,])

                        @include('partial.date', ['model' => $m, 'prop' => 'date_admission', 'label' => 'Date of Admission to Hospital', 'default_val' => $sample->patient->date_admission ?? null,])

                        @include('partial.input', ['model' => $m, 'prop' => 'hospital_admitted', 'default_val' => $sample->patient->hospital_admitted ?? null, 'label' => 'Hospital Admitted'])

                        @include('partial.date', ['model' => $m, 'prop' => 'date_isolation', 'label' => 'Date of Isolation', 'default_val' => $sample->patient->date_isolation ?? null,])

                        @include('partial.select', ['model' => $m, 'prop' => 'health_status', 'label' => 'Health Status', 'items' => $health_statuses])

                        @include('partial.date', ['model' => $m, 'prop' => 'date_death', 'label' => 'Date of Death', 'default_val' => $sample->patient->date_death ?? null,])

                        @include('partial.select_multiple', ['model' => $m, 'prop' => 'symptoms', 'label' => 'Symptoms', 'items' => $covid_symptoms])

                        @include('partial.input', ['model' => $m, 'prop' => 'temperature', 'is_number' => true, 'label' => 'Temperature (Celcius)'])

                        @include('partial.select_multiple', ['model' => $m, 'prop' => 'observed_signs', 'label' => 'Observed Signs', 'items' => $observed_signs])

                        @include('partial.select_multiple', ['model' => $m, 'prop' => 'underlying_conditions', 'label' => 'Underlying Conditions', 'items' => $underlying_conditions])



                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                        <center>Exposure and travel information in the 14 days prior to symptom onset (prior to reporting if asymptomatic)</center>
                    </div>
                    <div class="panel-body" style="padding-bottom: 6px;">

                        @include('partial.input', ['model' => $m, 'prop' => 'occupation', 'default_val' => $sample->patient->occupation ?? null, 'label' => 'Occupation'])

                        <div class="travel_item" id="first_travel_item">
                            <div class="col-sm-4">
                                <div class="input-group date date-normal">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" placeholder="Date of Travel" class="form-control requirable" disabled name="travel[travel_date][]">
                                </div>
                            </div> 
                            <div class="col-sm-4">
                                <!-- <input class="form-control requirable" disabled placeholder="City Visited" name="travel[city_visited][]" type="text" value="{{ $sample->city_visited ?? '' }}"> -->
                                <select class="form-control requirable city_select" disabled name="travel[city_id][]" ></select>
                            </div>  
                            <div class="col-sm-4">
                                <input class="form-control requirable" disabled placeholder="Duration Visited (In Days)" number="number" name="travel[duration_visited][]" type="text" >
                            </div>
                            <div class="col-sm-12"><br/><br/></div>                      
                        </div>

                        @if(isset($sample) && $sample->patient->travel)
                            @foreach($sample->patient->travel as $key => $travel)
                                <input type="hidden" name="travel[travel_id][]" value="{{ $travel->id }}">
                                <div class="col-sm-4">
                                    <div class="input-group date date-normal">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" required placeholder="Date of Travel" class="form-control requirable" value="{{ $travel->travel_date ?? '' }}" name="travel[travel_date][]">
                                    </div>
                                </div> 
                                <div class="col-sm-4">
                                    <!-- <input class="form-control requirable" required placeholder="City Visited" name="travel[city_visited][]" type="text" value="{{ $travel->city_visited ?? '' }}"> -->
                                    <select class="form-control requirable saved_city_select" id="saved_city_select_{{ $key }}" name="travel[city_id][]" >
                                        @if($travel->town)
                                            <option value="{{ $travel->city_id }}"> {{ $travel->town->name }} </option>
                                        @endif
                                    </select>

                                </div>  
                                <div class="col-sm-4">
                                    <input class="form-control requirable" required placeholder="Duration Visited (In Days)" number="number" name="travel[duration_visited][]" type="text" value="{{ $travel->duration_visited ?? '' }}">
                                </div>
                                <div class="col-sm-12"><br/><br/></div>    
                            @endforeach
                        @endif

                        <div id="travel_container"></div>

                        <button class="btn btn-success btn-lg" id="add_travel"> Add Travel Detail </button>
                        <button class="btn btn-warning btn-lg" id="remove_travel"> Remove Travel Detail </button>

                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                        <center>Sample Information</center>
                    </div>
                    <div class="panel-body" style="padding-bottom: 6px;">

                        @include('partial.select', ['model' => $m, 'required' => true, 'prop' => 'sample_type', 'label' => 'Sample Type', 'items' => $covid_sample_types, ])

                        @include('partial.date', ['model' => $m, 'required' => true, 'prop' => 'datecollected', 'label' => 'Date of Collection',])

                        @if(auth()->user()->lab_user || auth()->user()->other_lab)

                            @include('partial.date', ['model' => $m, 'required' => true, 'prop' => 'datereceived', 'label' => 'Date of Received',])

                            @include('partial.select', ['model' => $m, 'required' => true, 'prop' => 'receivedstatus', 'label' => 'Received Status', 'items' => $receivedstatus, ])

                            @include('partial.select', ['model' => $m, 'row_attr' => "id='rejection'", 'prop' => 'rejectedreason', 'label' => 'Rejected Reason', 'items' => $viralrejectedreasons, ])

                            @if(auth()->user()->other_lab || env('APP_LAB') == 7)

                                @include('partial.date', ['model' => $m, 'prop' => 'datetested', 'label' => 'Date Tested',])

                                @include('partial.select', ['model' => $m, 'prop' => 'result', 'label' => 'Result', 'items' => $results, ])

                            @endif

                            {{--<div class="form-group" id="rejection" >
                                <label class="col-sm-4 control-label">Rejected Reason</label>
                                <div class="col-sm-8">
                                        <select class="form-control" required name="rejectedreason" id="rejectedreason" disabled>

                                        <option></option>
                                        @foreach ($viralrejectedreasons as $rejectedreason)
                                            <option value="{{ $rejectedreason->id }}"

                                            @if (isset($sample) && $sample->rejectedreason == $rejectedreason->id)
                                                selected
                                            @endif

                                            > {{ $rejectedreason->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>--}}
                        @endif
                        
                        @if(auth()->user()->user_type_id == 5)

                            @include('partial.input', ['model' => $m, 'prop' => 'entered_by', 'label' => 'Entered By'])

                        @endif

                    </div>
                </div>
            </div>
        </div>
                
        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-body" style="padding-bottom: 6px;">

                        @include('partial.input', ['model' => $m, 'prop' => 'name', 'label' => 'Quarantine Site'])


                        <div class="form-group">
                            <center>
                                <div class="col-sm-4 col-sm-offset-4">
                                    <button class="btn btn-primary" type="submit"> Save </button>
                                </div>
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>

      </div>
    </div>

@endsection

@section('scripts')

    @component('/forms/scripts')
    @endcomponent

@endsection
