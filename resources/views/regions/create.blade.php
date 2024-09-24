@extends('layouts.app')
@section('title', 'Create Regions')
@section('content')
    <div class="container">
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    New Region
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New region Form -->
                    <form action="{{ url('regions/new/') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- region Name -->
                        <div class="form-group">
                            <label for="region-name" class="col-sm-3 control-label">Name</label>

                            <div class="col-sm-6">
                                <input type="text" name="name" id="region-name" class="form-control" value="{{ old('name') }}">
                            </div>
                        </div>

                        <!-- Add region Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>Add Region
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

    </div>
    </div>
@endsection
