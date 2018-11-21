@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover data-table" >
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Patient</th>
                                    <th>Facility</th>
                                    <th>Batch No</th>
                                    <th>Received Status</th>
                                    <th>Date Collected</th>
                                    <th>Date Received</th>
                                    <th>Worksheet</th>
                                    <th>Date Tested</th>
                                    <th>Date Modified</th>
                                    <th>Date Dispatched</th>
                                    <th>Run</th>
                                    <th>Result</th>
                                    <th>Task</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($samples as $key => $sample)
                                    <tr>
                                        <td> {{ $key+1 }} </td>
                                        <td> {{ $patient->patient ?? '' }} </td>
                                        <td> {{ $patient->facility->name ?? '' }} </td>
                                        <td>  {!! $sample->batch->hyper_link ?? $sample->batch_id !!} </td>
                                        <td>
                                            @foreach($received_statuses as $received_status)
                                                @if($sample->receivedstatus == $received_status->id)
                                                    {{ $received_status->name ?? '' }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td> {{ $sample->my_date_format('datecollected') ?? '' }} </td>
                                        <td>                                            
                                            @if($sample->batch)
                                                {{ $sample->batch->my_date_format('datereceived') ?? '' }}
                                            @endif 
                                         </td>
                                        <td> {{ $sample->worksheet_id ?? '' }} </td>
                                        <td> {{ $sample->my_date_format('datetested') ?? '' }} </td>
                                        <td> {{ $sample->my_date_format('datemodified') ?? '' }} </td>
                                        <td>                                           
                                            @if($sample->batch)
                                                {{ $sample->batch->my_date_format('datedispatched') ?? '' }}
                                            @endif 
                                         </td>
                                        <td> {{ $sample->run ?? '' }} </td>
                                        <td> {{ $sample->result ?? '' }} </td>
                                        <td>
                                            @if($sample->batch && $sample->batch->batch_complete == 1)
                                                <a href="{{ url('/viralsample/print/' . $sample->id ) }} " target='_blank'>Print</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts') 

    @component('/tables/scripts')

    @endcomponent

@endsection