@extends('layouts.app')
@section('title', 'Delete reversals')
@section('content')
    <div class="container">
        <div class="rows">
            <!--  -->
            @if (count($reversals) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Leave Action Reversals</h1>
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
                                @foreach ($reversals as $reversal)
                                    <tr>
                                        <td>{{Carbon\Carbon::parse($reversal->create_at)->toDayDateTimeString()}}</td>
                                        <td class="table-text"><div>{{ $reversal->applicant_username }}</div></td>
                                        <td class="table-text"><div>{{ $reversal->created_by }}</div></td>
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
