@extends('layouts.app')
@section('title', 'Users')
@section('content')
    <div class="container">
        <div class="rows">
            <!-- Users -->
            @if (count($users) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Staff</h1>
                    </div>
                        @if( Auth::user()->hasRole("AdminNever") || Auth::user()->hasRole("HRNever"))
                            <div class="container" style="margin-top:10px;">
                                <a href="{{ url('/create-profile?new=true')}}" class="pull-right btn btn-default"><i class="fa fa-plus"></i> Add Staff</a>
                                <a href="{{ url('/import-staff')}}" class="pull-right btn btn-default"><i class="fa fa-plus"></i> Import Staff</a>
                                <a href="{{ url('/import-approvals')}}" class="pull-right btn btn-default"><i class="fa fa-plus"></i> Import Approvals</a>
                            </div>
                        @endif
                    <div class="panel-body">
                        <div class="row">
                            <form action="/users/search" method="GET">
                                {{ csrf_field() }}
                                <div class="input-group">
                                    <input type="text" class="form-control" name="filter" placeholder="Search users"> 
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-default">
                                            <span class="glyphicon glyphicon-search"></span>
                                        </button>
                                    </span>
                                </div>
                            </form>
                        </div>
                        {{ $users->appends(Request::only('filter'))->links() }}
                        <table class="table table-striped User-table">
                            <thead>
                                <th>#</th>
                                <th>Name</th>
                                <th>Job Title</th>
                                <th>Department</th>
                                <th>Location</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td style="vertically-align:center;">{{$user->sn}}</td>
                                        <td style="vertically-align:center;">{{$user->name}} ({{$user->username}})</td>
                                        <td style="vertically-align:center;">{{$user->designation}}</td>
                                        <td style="vertically-align:center;">{{$user->department}}</td>
                                        <td style="vertically-align:center;">{{ $user->area_office !== "Select" ? $user->area_office . ',' : '' }} {{ $user->region !== "Select" ? $user->region : '' }}</td>
                                        <td style="vertically-align:center;">{{substr($user->last_login_date, 0, 2) == "20" ? $user->last_login_date : ""}}</td>
                                        <!-- Users Delete Button -->
                                        <td>
                                          @if(Auth::user()->hasRole('Admin') or Auth::user()->hasRole('HR'))
                                            <a href="/complete-your-profile?id={{$user->id}}"  class="d-inline btn-link">Profile</a>
                                            <a href="/applications?filter={{$user->username}}&my=true"  class="d-inline btn-link">Applications</a>
                                          @endif
                                          @if(Auth::user()->hasRole('OC') or Auth::user()->hasRole('HR'))
                                              <a href="/leave-history?id={{$user->id}}"  class="d-inline btn-link">History</a>
                                          @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
        {{ $users->appends(Request::only('filter'))->links() }}
    </div>
@endsection
