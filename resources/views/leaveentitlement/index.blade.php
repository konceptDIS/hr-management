@extends('layouts.app')
@section('title', 'Leave Entitlements')
@section('content')
    <div class="container">
        <div class="rows">

            <!-- Current Leave Entitlements -->
            @if (count($leaveentitlements) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Leave Entitlements</h1>
                    </div>

                    <div class="panel-body">
                        <table class="table table-striped holiday-table">
                            <thead>
                              <th>Salary Grade</th>
                              <th>Leave Type</th>
                              <th>Days Since Resumption</th>
                              <th>Days Allocated</th>
                              <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($leaveentitlements as $leaveentitlement)
                                    <tr>
                                      <td>{{$leaveentitlement->salary_grade}}</td>
                                      <td>{{$leaveentitlement->leave_type}}</td>
                                      <td class="table-text"><div>{{ $leaveentitlement->days_since_resumption }}</div></td>
                                      <td>{{$leaveentitlement->days_allowed}}</td>
                                      <!-- Holiday Delete Button -->
                                      <td>
                                        @if(Auth::user()->hasRole("HR"))
                                            <form action="{{url('/leaveentitlements/' . $leaveentitlement->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button type="submit" id="delete-leaveentitlement-{{ $leaveentitlement->id }}" class="btn btn-danger">
                                                    <i class="fa fa-btn fa-trash"></i>Delete
                                                </button>
                                            </form>
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
    <a class="btn btn-primary btn-large" href="/leaveentitlements/new/?new=true">New Leave Entitlement</a>

    </div>
@endsection
