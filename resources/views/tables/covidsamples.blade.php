@extends('layouts.master')

@component('/tables/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('content')

<div class="content">

    <div class="row">
        <div class="col-md-12">
            Click To View: 
            <a href="{{ $myurl2 }}">
                All Samples
            </a> |
            <a href="{{ $myurl2 }}/2">
                Dispatched Samples
            </a> |
            <a href="{{ $myurl2 }}/0">
                Samples Pending Receipt at the Lab
            </a>
        </div>
    </div>

    <br />

    <div class="row">
        <div class="col-md-4"> 
            <div class="form-group">
                <label class="col-sm-2 control-label">Select Date</label>
                <div class="col-sm-8">
                    <div class="input-group date">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" id="filter_date" required class="form-control">
                    </div>
                </div> 

                <div class="col-sm-2">                
                    <button class="btn btn-primary" id="submit_date">Filter</button>  
                </div>                         
            </div> 
        </div>

        <div class="col-md-8"> 
            <div class="form-group">

                <label class="col-sm-1 control-label">From:</label>
                <div class="col-sm-4">
                    <div class="input-group date">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" id="from_date" required class="form-control">
                    </div>
                </div> 

                <label class="col-sm-1 control-label">To:</label>
                <div class="col-sm-4">
                    <div class="input-group date">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" id="to_date" required class="form-control">
                    </div>
                </div> 

                <div class="col-sm-2">                
                    <button class="btn btn-primary" id="date_range">Filter</button>  
                </div>                         
            </div> 

        </div>
    </div>

    @if(auth()->user()->user_type_id != 5)

        <form action="{{ url('covidsample/index') }}" method="POST" class="my_form">
            @csrf

            @isset($to_print)
                <input type="hidden" name="to_print" value="1">
            @endisset

            <input type="hidden" name="type" value="{{ $type }}">

            <div class="row">

                <div class="alert alert-success">
                    <center>
                        Select facility and/or partner and/or subcounty. <br />
                        If you wish to get for a particular day, set only the From field. Set the To field also to get for a date range. <br />
                        Click on filter to get the list of batches based on selected criteria. <br />
                        The Download As Excel depends on all the selected criteria.
                    </center>
                </div>
                
                <br />

                <div class="col-md-4"> 
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Select Facility</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="facility_id" id="facility_id">
                                <option></option>
                                @if(isset($facility) && $facility)
                                    <option value="{{ $facility->id }}" selected>{{ $facility->facilitycode }} {{ $facility->name }}</option>
                                @endif
                            </select>
                        </div>                        
                    </div> 
                </div>
                <div class="col-md-4"> 
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Select Subcounty</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="subcounty_id" id="subcounty_id">
                                <option></option>
                                @foreach ($subcounties as $subcounty)
                                    <option value="{{ $subcounty->id }}"

                                    @if (isset($subcounty_id) && $subcounty_id == $subcounty->id)
                                        selected
                                    @endif

                                    > {{ $subcounty->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>                        
                    </div> 
                </div>
                <div class="col-md-4"> 
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Select Partner</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="partner_id" id="partner_id">
                                <option></option>
                                @foreach ($partners as $partner)
                                    <option value="{{ $partner->id }}"

                                    @if (isset($partner_id) && $partner_id == $partner->id)
                                        selected
                                    @endif

                                    > {{ $partner->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>                        
                    </div> 
                </div>
            </div>

            <br />

            <div class="row">

                <div class="col-md-9"> 
                    <div class="form-group">

                        <label class="col-sm-1 control-label">From:</label>
                        <div class="col-sm-4">
                            <div class="input-group date">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input type="text" id="from_date" name="from_date" class="form-control">
                            </div>
                        </div> 

                        <label class="col-sm-1 control-label">To:</label>
                        <div class="col-sm-4">
                            <div class="input-group date">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input type="text" id="to_date" name="to_date" class="form-control">
                            </div>
                        </div> 

                        <div class="col-sm-2">                
                            <button class="btn btn-primary" id="date_range" name="submit_type" value="date_range" type='submit'>Filter</button>  
                        </div>                         
                    </div> 
                </div>

                <div class="col-md-3">
                    <!-- <div class="form-group">              
                        <button class="btn btn-primary" name="submit_type" value="excel" type='submit'>Download as Excel</button> 
                    </div>   -->              
                </div>
            </div>

        </form>

    @endif
    
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    Covid-19 Samples
                </div>
                <div class="panel-body">
                    <div class="table-responsive">

                        <table class="table table-striped table-bordered table-hover @isset($datatable) data-table @endisset" >
                            <thead>
                                <tr class="colhead">
                                    <th rowspan="2">Lab ID</th>
                                    <th rowspan="2">Facility</th>
                                    <th colspan="4">Date</th>
                                    <th rowspan="2">Entered By</th>
                                    <th rowspan="2">Received By</th>
                                    <th rowspan="2">Received</th>
                                    <th rowspan="2">Results</th>                                    
                                    <th rowspan="2">Task</th>
                                    <th rowspan="2">Delete</th>
                                </tr>
                                <tr>
                                    <th>Collected</th>
                                    <th>Received</th>
                                    <th>Tested</th>
                                    <th>Dispatched</th>    
                                </tr>
                            </thead>
                            <tbody>

                                @foreach($samples as $sample)
                                    <tr>
                                        <td> {{ $sample->id }} </td>
                                        <td> {{ $sample->name }} </td>
                                        <td> {{ $sample->my_date_format('datecollected') }} </td>
                                        <td> {{ $sample->my_date_format('datereceived') }} </td>
                                        <td> {{ $sample->my_date_format('datetested') }} </td>
                                        <td> {{ $sample->my_date_format('datedispatched') }} </td>
                                        @if($sample->surname == '' || !$sample->surname)
                                            <td> {{ $sample->entered_by }} </td>
                                        @else
                                            <td> {{ $sample->surname . ' ' . $sample->oname }} </td>
                                        @endif

                                        <td> {{ $sample->rsurname . ' ' . $sample->roname }} </td>

                                        <td> 
                                            @if($sample->receivedstatus == 1)
                                                Received
                                            @elseif($sample->receivedstatus == 2)
                                                Rejected
                                            @endif
                                        </td>

                                        <td> {{ $sample->results }} </td>
                                        <td>
                                            {!! $sample->edit_link !!}                                            
                                        </td>
                                        <td> {!! $sample->delete_form !!} </td>
                                    </tr>


                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @empty($datatable)
                        {{ $samples->links() }}
                    @endempty
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts') 

    @component('/tables/scripts')
        @slot('js_scripts')
            <script src="{{ asset('js/datapicker/bootstrap-datepicker.js') }}"></script>
        @endslot

    @endcomponent

    <script type="text/javascript">
        $(document).ready(function(){
            localStorage.setItem("base_url", "{{ $myurl ?? '' }}/");
            
            set_select_facility("facility_id", "{{ url('/facility/search') }}", 3, "Search for facility", false);

            // $("#check_all").on('click', function(){
            //     var str = $(this).html();
            //     if(str == "Check All"){
            //         $(this).html("Uncheck All");
            //         $(".checks").prop('checked', true);
            //     }
            //     else{
            //         $(this).html("Check All");
            //         $(".checks").prop('checked', false);           
            //     }
            // });

            $(".date").datepicker({
                startView: 0,
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: true,
                autoclose: true,
                format: "yyyy-mm-dd"
            });

            $('#submit_date').click(function(){
                var d = $('#filter_date').val();
                window.location.href = localStorage.getItem('base_url') + d;
            });

            $('#date_range').click(function(){
                var from = $('#from_date').val();
                var to = $('#to_date').val();
                window.location.href = localStorage.getItem('base_url') + from + '/' + to;
            });

            $("#check_all").on('click', function(){
                var str = $(this).html();
                if(str == "Check All"){
                    $(this).html("Uncheck All");
                    $(".checks").prop('checked', true);
                }
                else{
                    $(this).html("Check All");
                    $(".checks").prop('checked', false);           
                }
            });

        });
        
    </script>

@endsection