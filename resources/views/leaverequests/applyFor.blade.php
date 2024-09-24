@extends('layouts.app')
@section('title', 'Apply For')
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
                    <form action="{{ url('apply-for') }}" id="my-form" method="POST" class="form-horizontal" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        @if(Auth::user()->hasRole('HR'))
                        <!-- pre approved -->
                        <div class="form-group">
                            <label for="pre_approved" class="col-sm-3 control-label">Already approved?</label>
                            <div class="col-sm-6">
                            <select name="pre_approved" id="leaverequest-pre_approved" class="form-control choosen-select">
                                <option value="0">Select</option>
                                <option value="true">No</option>
                                <option value="false">Yes</option>
                            </select>                    
                            </div>
                        </div>
                        @endif
                        <!-- Leave Request Type -->
                        <div class="form-group">
                            <label for="leaverequest-type" class="col-sm-3 control-label">Type</label>
                            <input type="text" name="id" id="id"  class="hidden" value="{{ old('id', isset($application->id) ? $application->id : null) }}"/> 
                            <input type="text" name="created_by" id="created_by"  
                            class="hidden" value="{{ old('created_by', isset($application->created_by) ? $application->created_by : null) }}"/> 

                            <div class="col-sm-6">

                                <select name="leave_type" id="leaverequest-leave_type" class="form-control choosen-select">
                                <option value="0">Select</option>
                                @foreach($leavetypes as $type)
                                    @if($type->balance>=0)
                                        @if($selected_leave_type!==null && $selected_leave_type->id==$type->id)
                                            <option value="{{ $type->name }}" selected="selected">{{ $type->name }} ({{ $type->balance}} days left)</option>
                                        @else
                                            <option value="{{ $type->name }}">{{ $type->name }} ({{ $type->balance}} days left)</option>
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
                            <label for="leaverequest-start_date" class="col-sm-3 control-label">Start date</label>
                            <div class="col-sm-6">
                                <input type="text" autocomplete="off" name="start_date" placeholder="dd/mm/yyyy" id="leaverequest-start_date" class="form-control datepicker3" 
                                value="{{ old('start_date', isset($application->start_date) ? $application->getStartDate() : null) }}">
                            </div>
                        </div>

                        <!-- Days -->
                        <div class="form-group">
                            <label for="leaverequest-days_requested" class="col-sm-3 control-label">Days</label>
                            <div class="col-sm-6">
                                <input type="number" min="1" max="{{$max}}" name="days_requested" id="leaverequest-days_requested" class="form-control" 
                                value="{{ old('days_requested', isset($application->days_requested) ? $application->days_requested : null) }}">
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
                                <input type="text" placeholder="firstname.lastname" name="stand_in_username" id="leaverequest-stand_in_username" class="form-control" 
                                value="{{ old('stand_in_username', isset($application->stand_in_username) ? $application->stand_in_username : null)  }}">
                            </div>
                        </div>

                        <!-- Supervisor -->
                        <div class="form-group">
                            <label for="leaverequest-approver_username" class="col-sm-3 control-label">Approver Username</label>

                            <div class="col-sm-6">
                                <input type="text" placeholder="firstname.lastname" name="approver_username" id="leaverequest-approver_username" class="form-control" 
                                value="{{ old('approver_username', isset($application->approver_username) ? $application->approver_username : null) }}">
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
