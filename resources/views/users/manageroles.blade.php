@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Manage User Roles
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New User Form -->
                    <form action="{{ url('users/addroles/') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- User Name -->
                        <div class="form-group">
                            <label for="user-name" class="col-sm-3 control-label">Name</label>
                            <div class="col-sm-6">
                                <input type="text" name="username" id="user-name" class="form-control" value="{{ old('username') }}">
                            </div>
                        </div>

                        <!-- Roles -->
                        <div class="form-group">
                            <div class="col-sm-6">
                                <select>
                                @if(sizeOf($roles) >0)
                                    @foreach($roles as $role)
                                        <option>{{$role->name}}</option>
                                    @endforeach
                                @endif
                                </select>
                            </div>
                        </div>

                        <!-- Add User Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>Add User
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

    </div>
    </div>
@endsection
