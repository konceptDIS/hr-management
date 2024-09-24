@extends('layouts.app')
@section('title', 'Refuse Page')
@section('content')
    <div class="container">
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Reason for {{$action-name}}
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New region Form -->
                    <form action="{{ url('{{$url}}') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}
                        <input type="hidden" id="leave_request_id" name="leave_request_id" value="{{id}}"
                        <!-- region Name -->
                        <div class="form-group">
                            <label for="reason" class="col-sm-3 control-label">Reason</label>
                            <div class="col-sm-6">
                                <input type="text" name="reason" id="reason" class="form-control" value="{{ old('reason') }}">
                            </div>
                        </div>

                        <!-- Add region Button -->
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
