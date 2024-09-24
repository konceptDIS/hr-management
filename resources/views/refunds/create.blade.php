@extends('layouts.app')
@section('title', 'Apply for Leave Refund')
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
                    <h2>{{ $title}}</h2>
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')
                    <!-- Leave Recall Form -->
                    <form action="{{ url('refunds/new/' . $application_id) }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Date recalled -->
                        <div class="form-group">
                            <label for="date_recalled" class="col-sm-3 control-label">Date recalled</label>
                            <div class="col-sm-6">
                                <input type="text" name="date_recalled" placeholder="dd/mm/yyyy" id="date_recalled" class="form-control datepicker" value="{{ old('date_recalled') }}">
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="form-group">
                            <label for="leaverequest-reason" class="col-sm-3 control-label">Reason</label>
                            <div class="col-sm-6">
                              <textarea style="width:100%;" placeholder="Please provide a reason here" rows="5" 
                              id="recall-reason" name="reason"></textarea>
                            </div>
                        </div>
                        

                        <!-- Add Recall Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default">
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
