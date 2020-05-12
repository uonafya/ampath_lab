@extends('layouts.master')

@section('content')
<style type="text/css">
	.list-group {
		margin-top: 10px;
    	margin-bottom: 10px;
	}
	.list-group-item {
		margin-bottom: 4px;
    	margin-top: 4px;
	}
</style>
<div class="p-lg">
	<div class="content animate-panel" data-child="hpanel" style="background-color: white;">
	<!-- <div class="animate-panel"  data-child="hpanel" data-effect="fadeInDown"> -->
        <div class="row">
		    <div class="col-lg-6">
		        <div class="hpanel">
		            <div class="alert alert-success">
		                <center><i class="fa fa-bolt"></i> DAILY SAMPLES LOG: {{ @Date('M d, Y') }}</center>
		            </div>
		            <div class="panel-body no-padding">
		            	<div id="dailyprogress"></div>
		            </div>
		        </div>
		    </div>
		    <div class="col-lg-6">
		        <div class="hpanel">
		            <!-- <div class="panel-heading hbuilt">
		                <center>Pending Tasks</center>
		            </div> -->
		            <div class="alert alert-warning">
		                <center><i class="fa fa-bolt"></i> <strong>PENDING TASKS</strong></center>
		            </div>

		            <div class="panel-body no-padding">
		            	@if(auth()->user()->lab_user)
		            	<ul class="list-group">
		                @if(session('testingSystem') == 'Viralload')
	            		    <!-- <li class="list-group-item" style="{{-- $style --}}">
	            		    	<span class="badge badge-{{-- $badge --}}">{{-- $widgets['overduetesting'] --}}</span>
			                    <a href="{{ url('home/overdue/testing') }}">VL Samples Overdue for Testing ( > 14 Days since Receipt at Lab )</a>
		                    </li> -->

	            		    <!-- <li class="list-group-item" style="{{-- $style --}}">
	            		    	<span class="badge badge-{{-- $badge --}}">{{-- $widgets['overduedispatched'] --}}</span>
			                    <a href="{{-- url('home/overdue/dispatch') --}}">VL Samples Overdue for Result Update & Dispatch ( > 14 Days since Receipt at Lab ) </a>
		                    </li> -->

		                	<li class="list-group-item" style="{{ $style }}">
		                        <span class="badge badge-{{ $badge }}">{{ $widgets['pendingSamples']['plasma'] }}</span>
		                        <a href="{{ url('home/pending/samples/plasma') }}">Frozen Plasma Samples Awaiting Testing</a>
		                    </li>

		                    <li class="list-group-item" style="{{ $style }}">
		                        <span class="badge badge-{{ $badge }}">{{ $widgets['pendingSamples']['EDTA'] }}</span>
		                        <a href="{{ url('home/pending/samples/EDTA') }}">Venous Blood (EDTA) Samples Awaiting Testing</a>
		                    </li>

		                    <li class="list-group-item" style="{{ $style }}">
		                        <span class="badge badge-{{ $badge }}">{{ $widgets['pendingSamples']['DBS'] }}</span>
		                        <a href="{{ url('home/pending/samples/DBS') }}">DBS Samples Awaiting Testing</a>
		                    </li>

		                    <li class="list-group-item" style="{{ $style }}">
		                        <span class="badge badge-{{ $badge }}">{{ $widgets['batchesForApproval'] }}</span>
		                        <a href="{{ url('viralbatch/site_approval') }}">Site Entry Batches Awaiting Approval for Testing</a>
		                    </li>

		                    <!-- <li class="list-group-item" style="{{-- $style --}}">
		                        <span class="badge badge-{{-- $badge --}}">{{-- $widgets['batchesNotReceived'] --}}</span>
		                        <a href="#">Batches Marked as Not Received at Lab</a>
		                    </li> -->

		                    <li class="list-group-item" style="{{ $style }}">
		                        <span class="badge badge-{{ $badge }}">{{ $widgets['batchesForDispatch'] }}</span>
		                        <a href="{{ url('viralbatch/dispatch') }}">Complete Batches Awaiting Dispatch</a>
		                    </li>

		                    <li class="list-group-item" style="{{ $style }}">
		                        <span class="badge badge-{{ $badge }}">{{ $widgets['samplesForRepeat'] }}</span>
		                        <a href="{{ url('home/repeat') }}">Invalid/Failed Samples from Previous Runs to be Rerun</a>
		                    </li>

		                    <li class="list-group-item" style="{{ $style }}">
		                        <span class="badge badge-{{ $badge }}">{{ $widgets['rejectedForDispatch'] }}</span>
		                        <a href="{{ url('home/rejected') }}">Rejected Samples Awaiting Dispatch</a>
		                    </li>

		                    <li class="list-group-item" style="{{ $style }}">
		                    	<span class="badge badge-{{ $badge }}">{{ $widgets['pendingSamplesOverTen'] }}</span>
		                    	<a href="#">Samples Over 10 Days Since Receipt and not Tested</a>
		                    </li>

		                    <li class="list-group-item" style="{{ $widgets['get_style']($widgets['delayed_batches']) }}">
		                    	<span class="badge badge-{{ $widgets['get_badge']($widgets['delayed_batches']) }}">{{ $widgets['delayed_batches'] }}</span>
		                    	<a href="{{ url('viralbatch/delayed') }}">Incomplete Tested Batches </a>
		                    </li>

		            	@elseif(session('testingSystem') == 'EID')
	            		    <!-- <li class="list-group-item" style="{{-- $style --}}">
	            		    	<span class="badge badge-{{-- $badge --}}">{{-- $widgets['overduetesting'] --}}</span>
			                    <a href="{{-- url('home/overdue/testing') --}}">EID Samples Overdue for Testing ( > 14 Days since Receipt at Lab )</a>
		                    </li> -->

	            		    <!-- <li class="list-group-item" style="{{-- $style --}}">
	            		    	<span class="badge badge-{{-- $badge --}}">{{-- $widgets['overduedispatched'] --}}</span>
			                    <a href="{{-- url('home/overdue/dispatch') --}}">EID Samples Overdue for Result Update & Dispatch ( > 14 Days since Receipt at Lab ) </a>
		                    </li> -->

	            		    <li class="list-group-item" style="{{ $style }}">
	            		    	<span class="badge badge-{{ $badge }}">{{ $widgets['pendingSamples'] }}</span>
			                    <a href="{{ url('home/pending/samples') }}">Samples awaiting testing</a>
		                    </li>

		                    <li class="list-group-item" style="{{ $style }}">
		                        <span class="badge badge-{{ $badge }}">{{ $widgets['batchesForApproval'] }}</span>
		                        <a href="{{ url('batch/site_approval') }}">Site entry batches awaiting approval for testing.</a>
		                    </li>

		                    <li class="list-group-item" style="{{ $style }}">
		                        <span class="badge badge-{{ $badge }}">{{ $widgets['batchesForDispatch'] }}</span>
		                        <a href="{{ url('batch/dispatch') }}">Complete batches awaiting dispatch.</a>
		                    </li>

		                    <li class="list-group-item" style="{{ $style }}">
		                        <span class="badge badge-{{ $badge }}">{{ $widgets['samplesForRepeat'] }}</span>
		                        <a href="{{ url('home/repeat') }}">Invalid/Failed Samples from previous runs to be rerun.</a>
		                    </li>

		                    <li class="list-group-item" style="{{ $style }}">
		                        <span class="badge badge-{{ $badge }}">{{ $widgets['rejectedForDispatch'] }}</span>
		                        <a href="{{ url('home/rejected') }}">Rejected samples awaiting dispatch.</a>
		                    </li>

		                    <li class="list-group-item" style="{{ $style }}">
		                    	<span class="badge badge-{{ $badge }}">{{ $widgets['pendingSamplesOverTen'] }}</span>
		                    	<a href="#">Samples Over 10 Days Since Receipt and not Tested</a>
		                    </li>

		                    <li class="list-group-item" style="{{ $widgets['get_style']($widgets['delayed_batches']) }}">
		                    	<span class="badge badge-{{ $widgets['get_badge']($widgets['delayed_batches']) }}">
			                    	{{ $widgets['delayed_batches'] }}
			                    </span>
		                    	<a href="{{ url('batch/delayed') }}">Incomplete Tested Batches </a>
		                    </li>
		                @elseif(Session('testingSystem') == 'Covid')

		                    <li class="list-group-item" style="{{ $widgets['get_style']($widgets['covid_pending_receipt']) }}">
		                        <span class="badge badge-{{ $widgets['get_badge']($widgets['covid_pending_receipt']) }}">{{ $widgets['covid_pending_receipt'] }}</span>
		                        <a href="{{ url('covid_sample/index/0') }}">Samples Pending Approval for Testing.</a>
		                    </li>


		                    <li class="list-group-item" style="{{ $widgets['get_style']($widgets['covid_pending_testing']) }}">
		                        <span class="badge badge-{{ $widgets['get_badge']($widgets['covid_pending_testing']) }}">{{ $widgets['covid_pending_testing'] }}</span>
		                        <a href="{{ url('covid_sample') }}">Samples Pending Testing.</a>
		                    </li>


		                    <li class="list-group-item" style="{{ $widgets['get_style']($widgets['covid_pending_results_update']) }}">
		                        <span class="badge badge-{{ $widgets['get_badge']($widgets['covid_pending_results_update']) }}">{{ $widgets['covid_pending_results_update'] }}</span>
		                        <a href="{{ url('covid_worksheet/index/1') }}">Worksheets Pending Results Update.</a>
		                    </li>

		                @elseif(Session('testingSystem') == 'CD4')

		                    <li class="list-group-item" style="{{ $widgets['get_style']($widgets['CD4samplesInQueue']) }}">
		                        <span class="badge badge-{{ $badge }}">{{ $widgets['get_badge']($widgets['CD4samplesInQueue']) }}</span>
		                        <a href="{{ url('cd4/sample/dispatch/3') }}">Samples In-Queue.</a>
		                    </li>

		                    <li class="list-group-item" style="{{ $widgets['get_style']($widgets['CD4resultsForDispatch']) }}">
		                        <span class="badge badge-{{ $badge }}">{{ $widgets['get_badge']($widgets['CD4resultsForDispatch']) }}</span>
		                        <a href="{{ url('cd4/sample/dispatch/1') }}">Results Awaiting Printing for Dispatch.</a>
		                    </li>

		                    <li class="list-group-item" style="{{ $widgets['get_style']($widgets['CD4worksheetFor2ndApproval']) }}">
		                        <span class="badge badge-{{ $badge }}">{{ $widgets['get_badge']($widgets['CD4worksheetFor2ndApproval']) }}</span>
		                        <a href="{{ url('cd4/worksheet/state/1') }}">Worksheets Awaiting 2nd Review.</a>
		                    </li>
						@endif
						@if(in_array(Session('testingSystem'), ['EID', 'Viralload']))
							@if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 4  || Auth::user()->user_type_id == 0)
								<li class="list-group-item alert alert-danger" style="background-color: #d9534f">
									<span class="badge badge-{{ $widgets['get_badge']($widgets['sample_manifest']) }}">
										{{ $widgets['sample_manifest'] }}
									</span>
									<a href="{{ url($widgets['prefix'] . 'batch/sample_manifest') }}" style="color:honeydew;">Print Sample Manifest </a>
								</li>
							@endif
						@endif
		            	</ul>
		            	@endif
		            </div>
		        </div>
		    </div>
		</div>
		<div class="row">
		    <div class="col-lg-6">
		        <div class="hpanel">
		            <div class="alert alert-success">
		                <center><i class="fa fa-bolt"></i> WEEKLY SAMPLES LOG</center>
		            </div>
		            <div class="panel-body no-padding">
		            	<div id="weeklyprogress"></div>
		            </div>
		        </div>
		    </div>
		    <div class="col-lg-6">
		        <div class="hpanel">
		            <div class="alert alert-success">
		                <center><i class="fa fa-bolt"></i> MONTHLY SAMPLES LOG</center>
		            </div>
		            <div class="panel-body no-padding">
		            	<div id="monthlyprogress"></div>
		            </div>
		        </div>
		    </div>
			
		</div>
    </div>
</div>
@endsection()

@section('scripts')
<script src="{{ asset('vendor/highcharts/highcharts.js' )}}"></script>
<script src="{{ asset('vendor/highcharts/modules/data.js' )}}"></script>
<script src="{{ asset('vendor/highcharts/modules/series-label.js' )}}"></script>
<script src="{{ asset('vendor/highcharts/modules/exporting.js' )}}"></script>

<script type="text/javascript">
	Highcharts.chart('dailyprogress', {
    chart: {
        type: 'column'
    },
    title: {
        text: ''
    },
    xAxis: {
        categories: @php
                        echo json_encode($chart['categories'])
                    @endphp,
        crosshair: true
    },
    yAxis: {
        min: 0,
        title: {
            text: 'No. of Samples'
        }
    },
    tooltip: {
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
    },
    series: [
    		@php
                echo json_encode($chart['series'])
            @endphp
            ]
});
	Highcharts.chart('weeklyprogress', {
    chart: {
        type: 'column'
    },
    title: {
        text: ''
    },
    xAxis: {
        categories: @php
                        echo json_encode($week_chart['categories'])
                    @endphp,
        crosshair: true
    },
    yAxis: {
        min: 0,
        title: {
            text: 'No. of Samples'
        }
    },
    tooltip: {
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
    },
    series: [
    		@php
                echo json_encode($week_chart['series'])
            @endphp
            ]
});
	Highcharts.chart('monthlyprogress', {
    chart: {
        type: 'column'
    },
    title: {
        text: ''
    },
    xAxis: {
        categories: @php
                        echo json_encode($month_chart['categories'])
                    @endphp,
        crosshair: true
    },
    yAxis: {
        min: 0,
        title: {
            text: 'No. of Samples'
        }
    },
    tooltip: {
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
    },
    series: [
    		@php
                echo json_encode($month_chart['series'])
            @endphp
            ]
});
</script>
@endsection