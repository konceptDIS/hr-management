<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>AEDC | Leave Management - @yield("title") - @if(Auth::guest() == false){{Auth::user()->username}}@endif</title>

    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Latest compiled and minified CSS -->
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous"> -->

    <!-- Font Awesome -->
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- iCheck -->
    <link href="../vendors/iCheck/skins/flat/green.css" rel="stylesheet">
    <!-- bootstrap-progressbar -->
    <link href="../vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    <!-- JQVMap -->
    <link href="../vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet"/>
    <!-- bootstrap-daterangepicker -->
    <link href="../vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="../build/css/custom.min.css" rel="stylesheet">

    <!-- jQuery UI -->
    <link rel="stylesheet" href="/css/jquery-ui.css">

    <!--Choosen-->
    <link rel="stylesheet" href="/css/chosen.css">


    <style>
    .submit-btn{
      margin-top:5px;
    }

    .close-no-ui{
      padding-top:5px;
    }
    cb-label {
      display: block;
      padding-left: 15px;
      text-indent: -15px;
    }
    cb-input {
      width: 13px;
      height: 13px;
      padding: 0;
      margin:0;
      vertical-align: bottom;
      position: relative;
      top: -1px;
      *overflow: hidden;
    }
    body.modal-open .modal {
    display: flex !important;
    height: 100%;
    }

    body.modal-open .modal .modal-dialog {
        margin: auto;
        z-index: 0;
    }

    body.modal-open div.modal-backdrop {
        z-index: 0;
    }

    .heading{
      font-size: 1.5em;
    }

    .request-row{
      margin-top: 10px;
      margin-bottom: 10px;
    }

    .circle{
      width: 100%;
    height: 100px;
    border-radius: 50%;
    font-size: 50px;
    color: #fff;
    line-height: 500px;
    text-align: center;
    background: #000
    }
    </style>

    @yield("style")

  </head>

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a href="/" class="site_title"></i> <span>Leave Management</span></a>
            </div>

            <div class="clearfix"></div>

            <!-- menu profile quick info -->
            @if(Auth::guest()==FALSE)
            <div class="profile clearfix">
              <div class="profile_pic">
                <img src="/images/user.ico" style="margin:auto;display:block;" alt="..." class="img-circle profile_img">
              </div>
            </div>
            @endif
            <!-- /menu profile quick info -->

            <br />

            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              <div class="menu_section">
                <ul class="nav side-menu">
                  	<li><a href="{{ url('/home')}}"><i class="fa fa-home"></i> Home </a></li>
                    @if(Auth::guest()==FALSE)
                  	  <li><a href="{{ url('/new-request')}}"><i class="fa fa-edit"></i> Apply </a></li>
                  	  <li><a href="{{ url('/holidays')}}"><i class="fa fa-calendar"></i> Holidays </a></li>
                      <li><a href='/pdf/Leave-Management-User-Guide.pdf' download="Leave-Management-User-Guide.pdf" http="/pdf/Leave-Management-User-Guide.pdf')}}"><i class="fa fa-question"></i>User Guide</a>
                      <li><a href="{{ url('/leave-history?username=' . Auth::user()->username)}}"><i class="fa fa-list"></i>Leave History</a></li>
                      <li><a href="{{ url('/change-log')}}"><i class="fa fa-list" style="padding-top:5px;"></i>Change Log</a></li>
                    @endif
                    @if(Auth::user()!=null && (Auth::user()->hasRole("Admin") || Auth::user()->hasRole("HR") || Auth::user()->hasRole("OC")))
                      <li><a href="{{ url('/users')}}"><i class="fa fa-list" style="padding-top:5px;"></i>Staff List</a></li>
                    @endif
                    @if(Auth::guest()==FALSE && (Auth::user()->hasRole("Admin") || Auth::user()->hasRole("HR")))
                        <li><a href="{{ url('/offices')}}"><i class="fa fa-building-o" style="padding-top:5px;"></i>Sections</a></li>
                        <li><a href="{{ url('/areaoffices')}}"><i class="fa fa-building-o" style="padding-top:5px;"></i>Area Offices</a></li>
                        <li><a href="{{ url('/leave-rules')}}"><i class="fa fa-tasks"></i>Leave Rules</a></li>
                        <li><a href="{{ url('/leaveapprovals')}}"><i class="fa fa-check"></i>Leave Approvals</a></li>
                        <li><a href="{{ url('/leaveapprovalsOnlineAll')}}"><i class="fa fa-check"></i>Online Approvals</a></li>
                        <li><a href="{{ url('/applications')}}"><i class="fa fa-check"></i>Leave Applications</a></li>
                        <li><a href="{{ url('/adAccountsWhoHaveNeverLoggedIn')}}"><i class="fa fa-check"></i>Never Logged In</a></li>
                        <li><a href="{{ url('/accountsNotInAD')}}"><i class="fa fa-check"></i>Not in AD</a></li>
                        <li><a href="{{ url('/usersWhoHaveNeverAppliedForLeave')}}"><i class="fa fa-check"></i>Never Applied</a></li>
                        <li><a href="{{ url('/usersWhoHaveNeverBeenApprovedLeave')}}"><i class="fa fa-check"></i>Never Approved Leave</a></li>
                    @endif
                </ul>
              </div>
            </div>
            <!-- /sidebar menu -->

          </div>
        </div>

        <!-- top navigation -->
        <div class="top_nav">
          <div class="nav_menu">
            <nav>
              <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
              </div>

              <ul class="nav navbar-nav navbar-right">
              @if (Auth::guest())
                        <li><a href="{{ url('/login') }}">Login</a></li>
                        <li class="hidden"><a href="{{ url('/register') }}">Register</a></li>
                    @else
                <li class="">
                  <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <img src="/images/user.ico" alt="">{{ Auth::user()->name }}
                    <span class=" fa fa-angle-down"></span>
                  </a>
                  <ul class="dropdown-menu dropdown-usermenu pull-right">
                    <li><a href="/complete-your-profile"> Profile</a></li>

                                <li>
                                	<a href="{{ url('/logout') }}"
							         onclick="event.preventDefault();
							         document.getElementById('logout-form').submit();">
							          Logout
							    	</a><form id="logout-form"
            action="{{ url('/logout') }}"
        method="POST"
        style="display: none;">
                    {{ csrf_field() }}
      </form>
						    	</li>
						    @endif
						    </ul>
						    </li>
            </nav>
          </div>
        </div>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main" >
        @yield("content")
         </div>
        <!-- /page content -->

        <!-- footer content -->
        <footer>
          <div class="pull-right">
            AEDC - Leave Management System
          </div>
          <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
      </div>
    </div>

    <!-- jQuery -->
    <script src="../vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <!-- <script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script> -->
    <!-- Latest compiled and minified JavaScript -->
    <script src="/js/bootstrap.min.js"></script>
    <!-- FastClick -->
    <script src="../vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="../vendors/nprogress/nprogress.js"></script>
    <!-- Chart.js -->
    <script src="../vendors/Chart.js/dist/Chart.min.js"></script>
    <!-- gauge.js -->
    <script src="../vendors/gauge.js/dist/gauge.min.js"></script>
    <!-- bootstrap-progressbar -->
    <script src="../vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
    <!-- iCheck -->
    <script src="../vendors/iCheck/icheck.min.js"></script>
    <!-- Skycons -->
    <script src="../vendors/skycons/skycons.js"></script>
    <!-- Flot -->
    <script src="../vendors/Flot/jquery.flot.js"></script>
    <script src="../vendors/Flot/jquery.flot.pie.js"></script>
    <script src="../vendors/Flot/jquery.flot.time.js"></script>
    <script src="../vendors/Flot/jquery.flot.stack.js"></script>
    <script src="../vendors/Flot/jquery.flot.resize.js"></script>
    <!-- Flot plugins -->
    <script src="../vendors/flot.orderbars/js/jquery.flot.orderBars.js"></script>
    <script src="../vendors/flot-spline/js/jquery.flot.spline.min.js"></script>
    <script src="../vendors/flot.curvedlines/curvedLines.js"></script>
    <!-- DateJS -->
    <script src="../vendors/DateJS/build/date.js"></script>
    <!-- JQVMap -->
    <script src="../vendors/jqvmap/dist/jquery.vmap.js"></script>
    <script src="../vendors/jqvmap/dist/maps/jquery.vmap.world.js"></script>
    <script src="../vendors/jqvmap/examples/js/jquery.vmap.sampledata.js"></script>
    <!-- bootstrap-daterangepicker -->
    <script src="../vendors/moment/min/moment.min.js"></script>
    <script src="../vendors/bootstrap-daterangepicker/daterangepicker.js"></script>


    <!---->
    <script src="/js/modernizr.min.js"></script>
    <script src="/js/jquery-1.12.4.js"></script>
    <script src="/js/jquery-ui.js"></script>
    <script src="/js/chosen.jquery.js"></script>

    <!-- Custom Theme Scripts -->
    <script src="../build/js/custom.min.js"></script>

    <script>
    $(document).ready(function() {
      $('.datepicker').datepicker({ minDate: -0, maxDate: "+1Y +10D",       numberOfMonths: 3,
        showButtonPanel: true, changeMonth: true,
        dateFormat: 'dd/mm/yy',
        changeYear: true });

       $('.datepicker2').datepicker({maxDate: 0,       numberOfMonths: 1,
        showButtonPanel: true, changeMonth: true,dateFormat: 'dd/mm/yy',
        changeYear: true });


       $('.datepicker3').datepicker({maxDate: -0, maxDate: "+1Y +10D",        numberOfMonths: 1,
        showButtonPanel: true, changeMonth: true,dateFormat: 'dd/mm/yy',
        changeYear: true });

      //   $date = $('#resumption_date').text;
    });
    //Stop modal login page from closing so that login errors can display
    $(document).ready(function() {
      $(".show-modal").click(function(){
        $("#myModal").modal({
            backdrop: 'static',
            keyboard: false
        });
      });
    });
  $(document).ready(function(){

    $('.no-box').hide();
    $(".show-no-ui").click(function(){ //when user wants to see deny ui
        var btnId = $(this).attr('id');
        var uiToShow = "#deny-ui-" + btnId.substring(9);
        var uiToHide = "#accept-btn-" + btnId.substring(9);
        // alert(uiToShow);
        $(uiToShow).show();
        $(uiToHide).hide();
        $(btnId).hide();
        /*$('.no-box').show();
        $('.accept-btn').hide();
        $('.show-no-ui').hide();*/
    });

    $(".close-no-ui").click(function(){//user decides to cancel the denial attempt

          $('.no-box').hide();
          $('.accept-btn').show();
          $('.show-no-ui').show();
          // var btnId = $(this).attr('id');
          // alert("Cancel btn id=" + btnId);
          //
          // var uiToHide = "#deny-ui-" + btnId.substring(11);
          // var uiToShow = "#accept-btn-" + btnId.substring(11);
          // var uiToShow1 = "#deny-btn-" + btnId.substring(11);
          //
          // alert("Hide: " + uiToHide +". Show: " + uiToShow + " & " + uiToShow1);
          //
          // $(uiToShow).show();
          // $(uiToHide).hide();
          // $(uiToShow1).show();
    });

    jQuery(".submit-btn").click(function(){//denial reason submit btn clicked
      var btnId = jQuery(this).attr('id');
      // alert(btnId);
      var acceptBtn = "#accept-btn-" + btnId.substring(10);
      var denyBtn = "#deny-btn-" + btnId.substring(10);

      jQuery(acceptBtn).show();
      jQuery(denyBtn).show();
    });

  });


  jQuery('.show-modal').on('shown.bs.modal', function() {
          jQuery(document).off('focusin.modal');
      });
  jQuery(function(){
      jQuery(".choosen-select").chosen();
  });
  jQuery("#department-lol").change(function() {

  });

  jQuery("#department-lol").change(function() {
    var id = $('#department').find(":selected").val();
    var $sections = $("#section");
    var $sectionCont = $("#section-cont");
    // alert(options);
    // alert("id is " + id);
    $.get(
          '/get-units?id=' + id,
          function(data, status){
            // alert("Data: " + data + "\nStatus: " + status);
            var toAppend = '<option value="0">Select Unit</option>';
            $.each(data,function(counter, object)
            {
              toAppend += '<option value=' + object.id + '>' + object.name + '</option>';
            });
            // alert(toAppend);
            $sections.empty();
            $sections.append(toAppend);
            $sections.trigger("chosen:updated");
          });
    $sectionCont.show();
  });

  jQuery("#region").change(function() {
    var id = $('#region').find(":selected").val();
    var $sections = $("#area_office");
    var $sectionCont = $("#areaoffice-cont");
    // alert(options);
    // alert("id is " + id);
    $.get(
          '/get-area-offices?id=' + id,
          function(data, status){
            // alert("Data: " + data + "\nStatus: " + status);
            var toAppend = '<option value="0">Select Area Offices</option>';
            $.each(data,function(counter, object)
            {
              toAppend += '<option value=' + object.id + '>' + object.name + '</option>';
            });
            // alert(toAppend);
            $sections.empty();
            $sections.append(toAppend);
            $sections.trigger("chosen:updated");
          });
    $sectionCont.show();
  });
</script>
@yield("script")
    <!-- /gauge.js -->
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
