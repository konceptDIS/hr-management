@extends('layouts.app')
@section('title', 'Create Holiday')
@section('content')
    <div class="container">
    @if(Session::has('flash_message'))
        <div id="error-message" class="alert alert-info">
          {{Session::get('flash_message')}}
        </div>
      @endif
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    New Holiday
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Holiday Form -->
                    <form action="{{ url('holidays/new/') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Holiday Name -->
                        <div class="form-group">
                            <label for="holiday-name" class="col-sm-3 control-label">Name</label>

                            <div class="col-sm-6">
                                <input type="text" name="name" id="holiday-name" class="form-control" value="{{ old('name') }}">
                            </div>
                        </div>

                        <!-- Holiday Date -->
                        <div class="form-group">
                            <label for="holiday-date" class="col-sm-3 control-label">Date</label>

                            <div class="col-sm-6">
                                <input type="text" name="date" id="date" autocomplete="off" class="form-control datepicker" placeholder="dd/mm/yyyy"
                                 value="{{ old('date') }}">
                            </div>
                        </div>

                        @if(Auth::user()->hasRole("HR"))
                        <!-- Add Holiday Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>Add Holiday
                                </button>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

    </div>
    </div>
@endsection
