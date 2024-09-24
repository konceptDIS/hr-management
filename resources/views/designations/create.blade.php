@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    {{$title}}
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New designation Form -->
                    <form action="{{ url('designations/new/') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- designation Name -->
                        <div class="form-group">
                            <label for="designation-name" class="col-sm-3 control-label">Name</label>
                            <div class="col-sm-6">
                                <input type="text" name="name" id="designation-name" class="form-control" value="{{ old('name', isset($designation->name) ? $designation->name : null) }}">
                            </div>
                        </div>

                        <!-- designation Date -->
                        <div class="form-group">
                          <label for="fo_equivalent-id" class="col-sm-3 control-label">Leave Days</label>
                          <div class="col-sm-6">
                            <select class="form-control" name="fo_equivalent" id="fo_equivalent-id" value="{{ old('fo_equivalent', isset($designation->fo_equivalent) ? $designation->fo_equivalent : null) }}">
                              @foreach($fo_levels as $fo)
                                @if($fo == $designation->fo_equivalent)
                                  <option value="{{$fo->id}}" selected>{{$fo->name}}</option>
                                @else
                                  <option value="{{$fo->id}}">{{$fo->name}}</option>
                                @endif
                              @endforeach
                            </select>
                          </div>
                        </div>

                        @if(Auth::user()->hasRole("HR"))
                        <!-- Add designation Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>Add Designation
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
