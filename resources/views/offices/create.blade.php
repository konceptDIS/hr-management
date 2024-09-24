@extends('layouts.app')
@section('title', 'Create Office')
@section('content')
    <div class="container">
      @if(Session::has('flash_message'))
        <div class="alert alert-info">
          {{Session::get('flash_message')}}
        </div>
      @endif
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    {{$title}}
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New office Form -->
                    <form action="{{ url('offices/new/') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- office Name -->
                        <div class="form-group">
                            <label for="office-name" class="col-sm-3 control-label">Name</label>
                            <div class="col-sm-6">
                                <input type="text" name="name" id="office-name" class="form-control" value="{{ old('name', isset($office->name) ? $office->name : null) }}">
                            </div>
                        </div>

                        <!-- abbreviation -->
                        <div class="form-group">
                            <label for="office-abbrèviation" class="col-sm-3 control-label">Abbreviation</label>
                            <div class="col-sm-6">
                                <input type="text" name="abbreviation" id="office-abbrv"
                                class="form-control" value="{{ old('abbreviation', isset($office->abbreviation) ? $office->abbreviation : null) }}">
                            </div>
                        </div>

                        <!-- parent section -->
                        <div class="form-group">
                            <label for="office-parent" class="col-sm-3 control-label">Belongs to</label>
                            <div class="col-sm-6">
                               <select name="parent_section_id" id="office-parent" class="form-control" value="{{ old('parent_section_id', isset($office->parent_section_id) ? $office->parent_section_id : null) }}">
                                      <option value="0">Is not underany Section</option>
                                 @foreach($parent as $o)
                                    @if($o->id == $office->parent_section_id)
                                      <option value="{{$o->id}}" selected>{{$o->name}}</option>
                                    @else
                                      <option value="{{$o->id}}">{{$o->name}}</option>
                                    @endif
                                 @endforeach
                               </select>
                            </div>
                        </div>

                        <!-- type -->
                                                <div class="form-group">
                                                    <label for="office-type" class="col-sm-3 control-label">Type</label>
                                                    <div class="col-sm-6">
                                                        <select name="type" id="office-type" class="form-control" value="{{ old('type', isset($office->type) ? $office->type : null) }}">
                                                          <option value="unit">Unit</option>
                                                          <option value="department">Department</option>
                                                        </select>
                                                    </div>
                                                </div>



                        <!-- Add office Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>Add office
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
