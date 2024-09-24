@extends('layouts.app')
@section('title', 'Create Area Office')
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

                    <!-- New area Form -->
                    <form action="{{ url('areaoffices/new/') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- area Name -->
                        <div class="form-group">
                            <label for="area-name" class="col-sm-3 control-label">Name</label>
                            <div class="col-sm-6">
                              <input type="text" name="id" id="area-name" class="hidden form-control" value="{{ old('id', isset($areaOffice->id) ? $areaOffice->id : null) }}">
                              <input type="text" name="name" id="area-name" class="form-control" value="{{ old('name', isset($areaOffice->name) ? $areaOffice->name : null) }}">
                            </div>
                        </div>

                        <!-- area region -->
                        <div class="form-group">
                            <label for="area-region" class="col-sm-3 control-label">Region</label>
                            <div class="col-sm-6">
                              <select class="form-control" name="region_id" id="region_id" value="{{ old('region_id', isset($areaOffice->region_id) ? $areaOffice->region_id : null) }}">
                                <option value="0">Select Region</option>
                                @foreach($regions as $region)
                                  @if($region->id == $areaOffice->region_id)
                                    <option value="{{$region->id}}" selected>{{$region->name}}</option>
                                  @else
                                    <option value="{{$region->id}}">{{$region->name}}</option>
                                  @endif
                                @endforeach
                              </select>
                            </div>
                        </div>

                        <!-- Add area Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>Add Area Office
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

    </div>
    </div>
@endsection
