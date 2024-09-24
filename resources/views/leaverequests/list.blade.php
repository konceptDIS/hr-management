@extends('layouts.app')
@section('title', 'Leave Applications')
@section('content')
    <div class="container">
        <div class="rows">

            <!-- Current Offline Leave applications -->
            @if (count($applications) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Leave applications</h1>
                    </div>

                    <div class="panel-body">
                    <div class="row">
                        <form action="/applications/search" method="GET">
                            {{ csrf_field() }}
                            <div class="input-group">
                                <input type="text" class="form-control" name="filter" placeholder="{{$filter ? $filter : 'Search applications'}}"> 
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-default">
                                        <span class="glyphicon glyphicon-search"></span>
                                    </button>
                                </span>
                            </div>
                        </form>
                    </div>
                    {{ $applications->appends(Request::only('filter'))->links() }}
                        <table class="table table-striped holiday-table">
                            <thead>
                              <th>#</th>
                              <th>Date</th>
                              <th>Name</th>
                              <th>Type</th>
                              <th>Days Asked</th>
                              <th>Days Available</th>
                              <th>Stand In</th>
                              <th>Supervisor</th>
                              <th>Standin Response</th>
                              <th>Supervisor Response</th>
                              <th ></th>
                            </thead>
                            <tbody>
                                @foreach ($applications as $approval)
                                    <tr>
                                      <td>{{$approval->sn}}</td>
                                      <td >{{$approval->date_created}}</td> 
                                      <td >{{$approval->name}} ({{$approval->created_by}})</td>
                                      <td>{{$approval->leave_type}}</td>
                                      <td class="table-text"><div>{{ $approval->days_requested }}</div></td>
                                      <td class="table-text"><div>{{ $approval->days_left }}</div></td>
                                      <td>{{$approval->stand_in_username}}</td>
                                      <td>{{$approval->supervisor_username}}</td>
                                      <td>{{ $approval->standInResponseText() }}</td>
                                      <td>{{ $approval->supervisorResponseText() }}</td>
                                      <!-- Holiday Delete Button -->
                                      <td >
                                        <a class="btn btn-primary btn-large pull-right" href="{{url('/view?applicationId=' . $approval->id)}}">View</a>                                        
                                        @if(Auth::user()->hasRole("HRA"))
                                        <a class="btn btn-primary btn-large pull-right" href="{{url('/reverse?applicationId=' . $approval->id)}}">Undo Last Action</a>                                        
                                        @endif
                                      </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    {{ $applications->appends(Request::only('filter'))->links() }}
                        
                    </div>
                </div>
            @endif
        </div>

    </div>
@endsection
