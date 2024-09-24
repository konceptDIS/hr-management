@extends('layouts.app')
@section('title', 'Apply')
@section('script')

    <script type="text/javascript">

        function checkUpload(size)
        {
            if(size>2)
            {
            var n = size.toFixed(2);
                alert('Your file size is: ' + n + "MB. Please try with a file 1MB or less.");
                document.getElementById("btn").style.display='none';

            }
            else
            {
                //alert('File size is OK');
                $('#btn').show();
            }
        }
        $('#documents').bind('change', function() {
            // alert("Fired!");
            var fileSize = this.files[0].size/1024/1024;
            // alert(fileSize);
            checkUpload(fileSize);
        });

        $('#staff_id').bind('change', function(){
            //fetch supervisor
            //populate ui
            //fetch list of potential stand-ins
            //change name of stand in textbox
            //put drop down
        });

        jQuery("#leaverequest-leave_type").change(function() {
          let type = jQuery('#leaverequest-leave_type').val().split('-')[0];
          let url = '/get-resumption-date?days=' + jQuery('#leaverequest-days_requested').val() + '&date=' + jQuery('#leaverequest-start_date').val() + '&type=' + type;
          jQuery.get(
                url,
                function(data, status){
                  // console.log("Data: " + data + "\nStatus: " + status);
                  $('#leaverequest-end_date').val(data);
                });
        });

        window.addEventListener('load', e =>{
          const startDate = document.getElementById('leaverequest-start_date');
          if(startDate && startDate.readonly == true){
            $('.datepicker').datepicker( "option", "disabled", true );
          }
        });


        function constructMessage(){
          let msg = "";
          try{
            let type = jQuery('#leaverequest-leave_type').val().split('-')[0];
            let days = jQuery('#leaverequest-leave_type').val().split('-')[1];
            msg = `The maximum number of ${type} leave days available to you is ${days}`
          }catch(error){
            msg = error;
          }
          return msg;
        }

        function isValidResumptionDateRequest(){
          const daysReq = document.getElementById('leaverequest-days_requested');
          const days = jQuery('#leaverequest-leave_type').val().split('-')[1];
          const valid = Number(daysReq.value) <= Number(days);
          //console.log({ days_requested: daysReq.value, days_available: days, valid});
          return valid;
        }
        jQuery("#leaverequest-days_requested").blur(function() {
          if(isValidResumptionDateRequest() === false){
            alert(constructMessage());
            return false;
          }
          let type = jQuery('#leaverequest-leave_type').val().split('-')[0];
          let url = '/get-resumption-date?days=' + jQuery('#leaverequest-days_requested').val() + '&date=' + jQuery('#leaverequest-start_date').val() + '&type=' + type;
          jQuery.get(
                url,
                function(data, status){
                  // console.log("Data: " + data + "\nStatus: " + status);
                  $('#leaverequest-end_date').val(data);
                });
        });
        const form = document.getElementById('my-form');

        form.addEventListener('submit', (e) => {
          if(isValidResumptionDateRequest() === false){
            e.preventDefault();
            alert(constructMessage());
            return false;
          }
        });

        const btn = document.getElementById('btn');
        btn.addEventListener('click', e => {
          const sd = document.getElementById('leaverequest-start_date');
          const ed = document.getElementById('leaverequest-end_date');
          const si = document.getElementById('leaverequest-stand_in_username');
          const su = document.getElementById('leaverequest-supervisor_username');
          if(sd.value == "" || sd.value == " ") { e.preventDefault(); return false;}
          if(ed.value == "" || ed.value == " ") { e.preventDefault(); return false;}
          if(si.value == "" || si.value == " ") { e.preventDefault(); return false;}
          if(su.value == "" || su.value == " ") { e.preventDefault(); return false;}
          if(isValidResumptionDateRequest() === false){
            e.preventDefault();
            alert(constructMessage());
            return false;
          }
        });
    </script>

@endsection
@section('content')
    <div class="container">
      @if(Session::has('flash_message'))
        <div id="error-message" class="alert alert-info">
          {{Session::get('flash_message')}}
        </div>
      @endif

        <div class="col-sm-offset-0 col-sm-10">
            <div class="panel panel-default">
                <div class="panel-heading">
                    {{ $title}}
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Leave Request Form -->
                    <form action="{{ url('new-request') }}" id="my-form" method="POST" class="form-horizontal" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <!-- Leave Request Type -->
                        <div class="form-group">
                            <label for="leaverequest-type" class="col-sm-3 control-label">Type</label>
                            <input type="text" name="id" id="id"  class="hidden" value="{{ old('id', isset($application->id) ? $application->id : null) }}"/>

                            <div class="col-sm-6">

                                <select name="leave_type" id="leaverequest-leave_type" class="form-control choosen-select" {{ $editable ? '' : 'readonly' }}>
                                <option value="0">Select</option>
                                @foreach($leavetypes as $type)
                                    @if($type->balance>=0)
                                        @if($selected_leave_type!==null && $selected_leave_type->id==$type->id)
                                            <option value="{{ $type->name }}-{{ $type->balance}}" selected="selected">{{ $type->name }} ({{ $type->balance}} days left)</option>
                                        @else
                                            <option value="{{ $type->name }}-{{ $type->balance}}">{{ $type->name }} ({{ $type->balance}} days left)</option>
                                        @endif
                                    @endif
                                @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- Reason -->
                        <div class="form-group">
                            <label for="leaverequest-reason" class="col-sm-3 control-label">Reason</label>
                            <div class="col-sm-6">
                              <textarea style="width:100%;" placeholder="You can provide a reason here (optional)" rows="5" id="leaverequest-reason" name="reason">{{ old('reason', isset($application->reason) ? $application->reason : null)}}</textarea>
                            </div>
                        </div>
                        <!-- Start date -->
                        <div class="form-group">
                            <label for="leaverequest-start_date"  class="col-sm-3 control-label">Start date</label>
                            <div class="col-sm-6">
                                <input type="text" autocomplete="off" name="start_date" placeholder="dd/mm/yyyy" id="leaverequest-start_date" class="form-control datepicker"
                                value="{{ old('start_date', isset($application->start_date) ? $application->getStartDate() : null) }}" {{ $editable ? '' : 'readonly' }}>
                            </div>
                        </div>

                        <!-- Days -->
                        <div class="form-group">
                            <label for="leaverequest-days_requested" class="col-sm-3 control-label">Days</label>
                            <div class="col-sm-6">
                                <input type="number" min="1" name="days_requested" id="leaverequest-days_requested" class="form-control"
                                value="{{ old('days_requested', isset($application->days_requested) ? $application->days_requested : null) }}" {{ $editable ? '' : 'readonly' }}>
                            </div>
                        </div>

                        <!-- End date -->
                        <div class="form-group">
                            <label for="leaverequest-end_date" class="col-sm-3 control-label">Resumption date</label>

                            <div class="col-sm-6">
                                <input type="text" readonly placeholder="Will be calculated for you" name="end_date" id="leaverequest-end_date" class="form-control"
                                value="{{ old('end_date', isset($application->end_date) ? $application->getEndDate() : null) }}">
                            </div>
                        </div>

                        <!-- Stand In -->
                        <div class="form-group">
                            <label for="leaverequest-stand_in_username" class="col-sm-3 control-label">Stand In Username</label>

                            <div class="col-sm-6">
                                <input type="text" list="usernames" placeholder="firstname.lastname" name="stand_in_username" id="leaverequest-stand_in_username" class="form-control"
                                value="{{ old('stand_in_username', isset($application->stand_in_username) ? $application->stand_in_username : null)  }}">
                            </div>
                        </div>

                        <!-- Supervisor -->
                        <div class="form-group">
                            <label for="leaverequest-supervisor_username" class="col-sm-3 control-label">Supervisor Username</label>

                            <div class="col-sm-6">
                                <input type="text" list="usernames" placeholder="firstname.lastname" name="supervisor_username" id="leaverequest-supervisor_username" class="form-control"
                                value="{{ old('supervisor_username', isset($application->supervisor_username) ? $application->supervisor_username : null) }}">
                            </div>
                        </div>
                        <!--usernames data list -->
                        <datalist id="usernames">
                            @foreach($users as $user)
                                <option value="{{ $user->username }}">
                            @endforeach
                        </datalist>
                        <!-- Dont validate usernames -->
                        <div class="{{ session('show_bypass_validation_option') ? '' : 'hidden'}} form-group">
                            <label for="leaverequest-dont-validate" class="col-sm-3 control-label">Bypass Username Validation</label>

                            <div class="col-sm-6">
                                <input type="checkbox" name="dont_validate_usernames" id="dont_validate_usernames" class="form-control" />
                            </div>
                        </div>
                        <!-- upload -->
                        <div class="form-group">
                            <label for="documents" class="col-sm-3 control-label">Attach document(s)</label>
                            <div class="col-sm-9">
                               <input type="file" id="documents[]" name="documents[]" multiple accept=".jpg, .jpeg, .png, .docx, .pdf"/>
                            </div>
                        </div>

                        <!-- Add Leave Request Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default" id="btn">
                                    <i class="fa fa-btn fa-plus"></i>Submit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
</div>
</div>
@endsection
