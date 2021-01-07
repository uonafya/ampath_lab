<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Page title -->
    <title>EID/VL | LAB</title>

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <!--<link rel="shortcut icon" type="image/ico" href="favicon.ico" />-->

    <!-- Vendor styles -->
    <link rel="stylesheet" href="{{ public_path('vendor/fontawesome/css/font-awesome.css') }}" />
    <link rel="stylesheet" href="{{ public_path('vendor/animate.css/animate.css') }}" />
    <link rel="stylesheet" href="{{ public_path('vendor/bootstrap/dist/css/bootstrap.css') }}" /> 

    <style type="text/css">
        body.light-skin #menu {
            width: 240px;
        }
        #wrapper {
            margin: 0px 0px 0px 230px;
        }
        #toast-container > div {
            color: black;
        }
        .navbar-nav>li>a {
            padding: 15px 15px;
            font-size: 13px;
            color: black;
        }
        .btn {
            padding: 4px 8px;
            font-size: 12px;
        }
        .hpanel {
            margin-bottom: 4px;
        }
        .hpanel.panel-heading {
            padding-bottom: 2px;
            padding-top: 4px;
        }
    </style>

</head>
<!-- <body class="light-skin fixed-navbar sidebar-scroll"> -->
<body>

<!-- Main Wrapper -->
<!-- <div id="wrapper"> -->

    <!-- <div class="content"> -->

        <div class="row">

            <table class="table table-bordered table-striped" border="0" style="border: 0px; width: 100%;">
                <tr>
                    <td align="center">
                        <img src="{{ public_path('img/naslogo.jpg') }}" alt="NASCOP">
                    </td>
                </tr>
                <tr>
                    <td align="center">
                        <h5>MINISTRY OF HEALTH</h5>
                        <h5>NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)</h5>
                    </td>
                </tr>
            </table>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th> No </th>
                        <th> CCC Number </th>
                        <th> Batch </th>
                        <th> Sex </th>
                        <th> DOB </th>
                        <th> Date Collected </th>
                        <th> Edarp Response Message </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($samples as $key => $sample)
                        <tr>
                            <td> {{ $key+1 }} </td>
                            <td> {{ $sample->patient }} </td>
                            <td> {{ $sample->batch_id }} </td>
                            <td> {{ substr($sample->gender, 0, 1) }} </td>
                            <td> {{ $sample->my_date_format('dob') }} </td>
                            <td> {{ $sample->my_date_format('datecollected') }} </td>
                            <td> {{ $sample->edarp_error }} </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>            
        </div>
                
        <br />
        <br />

    <!-- </div> -->


    <!-- Footer-->
    <footer class="footer">
        <center>&copy; NASCOP 2010 - {{ @Date('Y') }} | All Rights Reserved</center>
    </footer>

<!-- </div> -->

<script src="{{ public_path('vendor/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ public_path('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ public_path('vendor/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ public_path('vendor/iCheck/icheck.min.js') }}"></script>


</body>
</html>
