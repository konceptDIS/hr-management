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
                        @if( Auth::user()->hasRole("Admin") || Auth::user()->hasRole("HR"))
                            <div class="container" style="margin-top:10px;">
                                <a href="{{ url('/create-profile?new=true')}}" class="pull-right btn btn-default"><i class="fa fa-plus"></i> Add Staff</a>
                                <a href="{{ url('/import-staff')}}" class="pull-right btn btn-default"><i class="fa fa-plus"></i> Import Staff</a>
                                <a href="{{ url('/import-approvals')}}" class="pull-right btn btn-default"><i class="fa fa-plus"></i> Import Approvals</a>
                            </div>
                        @endif
                    <div class="panel-body">
                        
                        <table class="table table-striped User-table">
                            <thead>
                                <th>#</th>
                                <th>Name</th>
                                <th>Job Title</th>
                                <th>Department</th>
                                <th class="hidden">Gender</th>
                                <th>Actions</th>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td style="vertically-align:center;">{{$loop->iteration}}</td>
                                        <td style="vertically-align:center;">{{$user->name}} ({{$user->username}})</td>
                                        <td style="vertically-align:center;">{{$user->designation}}</td>
                                        <td style="vertically-align:center;">{{$user->department}}</td>
                                        <td class="hidden" style="vertically-align:center;">{{$user->gender}}</td>
                                        <!-- Users Delete Button -->
                                        <td>
                                          @if(Auth::user()->hasRole('Admin') or Auth::user()->hasRole('HR'))
                                            <a href="/complete-your-profile?id={{$user->id}}"  class="d-inline btn-link">Profile</a>
                                            <a href="/leave-history?id={{$user->id}}"  class="d-inline btn-link">History</a>
                                            <a href="/apply-for?username={{$user->username}}"  class="d-inline btn-link">Apply For</a>
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
    </div>
@endsection
