<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Leave Management</title>

    <!-- Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700" rel='stylesheet' type='text/css'>

    <!-- Styles -->
      <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    {{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}

    <style>
        body {
            font-family: 'Lato';
        }

        .leave-box{
            min-height: 2em;
            background-color: lightgray;
            text-align: center;
        }

        .leave-box>h1{
            font-weight: bold;
            font-size: 1em;
        }

        .fa-btn {
            margin-right: 6px;
        }

        .d-inline{
            display:inline-block;
            padding-left: 5px;
        }

        .text-right{
            text-align: right;
        }

        .text-center{
            text-align: center;
        }

        .table > tbody > tr > td {
            vertical-align: middle;
        }

        .table > tbody > tr > td:hover {
            background-color: lightblue;
        }
    </style>
</head>
<body id="app-layout">
    <nav class="navbar navbar-default">
        <div class="container">
            <div class="navbar-header">

                <!-- Collapsed Hamburger -->
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <!-- Branding Image -->
                <a class="navbar-brand" href="{{ url('/') }}">
                    Leave Management
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                @if(Auth::guest()==FALSE)
                <li><a href="{{ url('/home')}}">Home</a></li>
                <li><a href="{{ url('/new-request')}}">Apply</a></li>
                <li><a href="{{ url('/holidays')}}">Holidays</a></li>
                    <li class="hidden"><a href="{{ url('/my-requests')}}">My Applications</a></li>
                    <li class="hidden"><a href="{{ url('/stand-in-requests')}}">Stand-in Requests</a></li>
                    <li class="hidden"><a href="{{ url('/pending-supervisor-approval')}}">Supervisor Approval</a></li>
                    <li class="hidden"><a href="{{ url('/pending-hr-approval')}}">HR Approval</a></li>
                    <li class="hidden"><a href="{{ url('/pending-md-approval')}}">MD Approval</a></li>
                @endif
                @if(Auth::user()!=null && Auth::user()->hasRole("Admin"))
                <li><a href="{{ url('/users')}}">Users</a></li>
                @endif
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="nav navbar-nav navbar-right">
                    <!-- Authentication Links -->
                    @if (Auth::guest())
                        <li><a href="{{ url('/login') }}">Login</a></li>
                        <li class="hidden"><a href="{{ url('/register') }}">Register</a></li>
                    @else
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                {{ Auth::user()->name }} <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ url('/logout') }}" 
         onclick="event.preventDefault();
         document.getElementById('logout-form').submit();">
          Logout
    </a>
     <form id="logout-form" 
            action="{{ url('/logout') }}" 
        method="POST" 
        style="display: none;">
                    {{ csrf_field() }}
      </form></li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
    <div>
        @yield('content')
    </div>
    <div>
        @yield('content2')
    </div>
    <!-- JavaScripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    {{-- <script src="{{ elixir('js/app.js') }}"></script> --}}
    <script src="https://code.jquery.com/jquery-3.0.0.min.js" integrity="sha256-JmvOoLtYsmqlsWxa7mDSLMwa6dZ9rrIdtrrVYRnDRH0=" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.1/js/bootstrap-datepicker.min.js"></script>
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
  $(document).ready(function() {
    $('.datepicker').datepicker({ minDate: -0, maxDate: "+1Y +10D",       numberOfMonths: 3,
      showButtonPanel: true, changeMonth: true,
      changeYear: true });
 
     $('.datepicker2').datepicker({maxDate: 0,       numberOfMonths: 1,
      showButtonPanel: true, changeMonth: true,
      changeYear: true });
  });
</script>
</body>
</html>