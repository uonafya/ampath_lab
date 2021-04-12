@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')
@php
	$disabled = "";
	dd($data);
@endphp
@isset($data->view)
	@php
		$disabled = "disabled";
	@endphp
@endisset
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="row">
                                              
                    </div>
                    <div class="table-responsive">
                    @if(!isset($data->view))
                        <form action="{{ url('/cd4/worksheet') }}" class="form-horizontal" method="POST" id='worksheet_form'>
                            @csrf
                    @endif
                   	@if(!isset($data->view) && $data->samples->count() == 0)
                   		<center><div class="alert alert-warning">No samples availabe to run a worksheet</div></center>
                   	@else
                    	
                    @if(!isset($data->view))
                        </form>
	                @endif
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