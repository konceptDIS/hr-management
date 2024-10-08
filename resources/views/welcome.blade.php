{{--@extends('layouts.app')--}}

{{--@section('content')--}}
<!-- <div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Welcome</div>

                <div class="panel-body">
                    Your Application's Landing Page.
                </div>
            </div>
        </div>
    </div>
</div> -->
{{--@endsection--}}
<html lang="en">
<head>
  <title>AEDC | Leave Management</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700" rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  @if(Session::has('flash_message'))
    <script type="text/javascript">
        $(window).on('load',function(){
            $('#myModal').modal('show');
        });
    </script>
  @endif
<style>
    /*.body, html {
        height: 100%;
        font-family: 'Lato';
    }
*/
body.modal-open .modal {
    display: flex !important;
    height: 100%;
    }

    body.modal-open .modal .modal-dialog {
        margin: auto;
    }


    .bg {
        font-family: 'Lato';
        /* The image used */
        background-image: url("{{ URL::asset('/images/beach.jpg') }}");

        /* Full height */
        height: 100%;

        /* Center and scale the image nicely */
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
    }


    /*.cont{
        height: 80px;
        position: fixed;
        top:2%;
        left:2%;
        width:100%;
        background-color: pink;
        opacity: 1;
    }
    */
    .logo{
        float:left;
        height: 35px;
        margin-right:20px;
    }

    .p{
        font-size: 30px;
    }

    .sign-in{
        font-size:1em;
        width:100px;
        border-color: white;
        border-style: solid;
        border-width: 2px;
        font-weight: bold;
        margin-top: 5px;
    }
</style>
</head>
<body class="bg">
<div class="container-fluid" >
    <div class="row" style="margin-top:20px;">
        <div class="col-md-12">
            <div class="col-sm-2">
                <a class="hidden btn btn-large btn-danger pull-right sign-in" href="http://localhost:8000/login" style="text-align: center;">Sign In</a>
                <!-- Trigger the modal with a button -->
                <button type="button" class="btn btn-large btn-danger sign-in" data-toggle="modal" style="text-align: center;" data-target="#myModal">Log In</button>
            </div>
            <div class="col-sm-10" >
                <div class="pull-right" >
                    <img src="/images/logo.png" class="logo pull-right" />
                </div>
                <p class="p pull-right" style="margin-right: 20px; color:white; font-weight: bold;">HR Management System</p>
            </div>
            
        </div>
    </div>
    @if(Session::has('flash_message'))
      <div id="error-message" class="hidden alert alert-info">
        {{Session::get('flash_message')}}
      </div>
    @endif
    <div class="row">
     <!-- Modal -->
            <div id="myModal" class="modal fade" role="dialog">
              <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Login</h4>
                  </div>
                  <div class="modal-body">
                     @if(Session::has('flash_message'))
                       <div class="alert alert-info">
                           {{Session::get('flash_message')}}
                       </div>
                     @endif
                   <form class="form-horizontal" role="form" method="POST" action="{{ url('/login') }}">
                                    {!! csrf_field() !!}

                                    <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                                        <label class="col-md-4 control-label">Username</label>

                                        <div class="col-md-6">
                                            <input type="text" class="form-control" placeholder="firstname.lastname" name="username" value="{{ old('username') }}">

                                            @if ($errors->has('username'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('username') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                        <label class="col-md-4 control-label">Password</label>

                                        <div class="col-md-6">
                                            <input type="password" class="form-control" placeholder="password" name="password">

                                            @if ($errors->has('password'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('password') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-6 col-md-offset-4">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="remember">
                                                </label>
                                                <span style="margin-top: 0px;">Remember Me</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                    <div class="form-group">
                                        <div >
                                            <a class="pull-left btn btn-link" href="https://passwordreset.microsoftonline.com/">Forgot Your Password?</a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-btn fa-sign-in"></i>Login
                                            </button>
                                        </div>
                                    </div>
                                    </div>
                                </form>
                  </div>
                </div>
              </div>
            </div>
    </div>
</div>
<!-- <div class="container">

<div class="bg">
    <div class="cont">
    <img src="/images/logo.png" class="logo" />
    <p class="p" style="color:white;">Leave Management System</p>
    <div style="clear:both;"></div>

    <div class="row">
        <div class="col-md-10"></div>
            <div class="col-md-3" style="background-color: red;">
                    <a class="btn btn-large btn-primary" href="http://localhost:8000/login" style="text-align: center;">Sign In</a>
            </div>
    </div>
</div>
</div>
</div> -->
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/5a1e8bffbb0c3f433d4cbeb0/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
</body>
</html>
