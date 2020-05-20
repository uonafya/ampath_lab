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
                                    <th>Name</th>
                                    <th>Emails</th>
                                    <th>Edit</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($quarantine_sites as $key => $quarantine_site)
                                    <tr>
                                        <td> {{ $key+1 }} </td>
                                        <td> {{ $quarantine_sites->name }} </td>
                                        <td> {{ $quarantine_sites->email }} </td>
                                        <td>
                                            <a href="{{ url('/quarantine_site/' . $quarantine_site->id . '/edit' ) }} " target='_blank'>Edit</a>
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