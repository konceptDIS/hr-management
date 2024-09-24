@extends('layouts.app')
@section('title', 'Delete Approvals')
@section('content')
    <div class="container">
        <div class="rows">
            <!--  -->
            @if (count($approvals) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Leave Delete Approvals</h1>
                    </div>

                    <div class="panel-body">
                        <table class="table table-striped holiday-table">
                            <thead>
                                <th>Date</th>
                                <th>Applicant</th>
                                <th>Approver</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($approvals as $approval)
                                    <tr>
                                        <td>{{Carbon\Carbon::parse($approval->create_at)->toDayDateTimeString()}}</td>
                                        <td class="table-text"><div>{{ $approval->applicant_username }}</div></td>
                                        <td class="table-text"><div>{{ $approval->created_by }}</div></td>
                                        <td></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
        @if(Auth::user()->hasRole("HR"))
          <a class="btn btn-primary btn-large" href="/holidays/new">New Holiday</a>
        @endif
    </div>
@endsection
