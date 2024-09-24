@extends('layouts.app')
@section('title', 'Holidays')
@section('content')
    <div class="container">
        <div class="rows">
        @if(Auth::user()->hasRole("HR"))
          <a class="btn btn-primary btn-large pull-right" href="/holidays/new">New Holiday</a>
        @endif
            <!-- Current Holidays -->
            @if (count($holidays) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Current Holidays</h1>
                    </div>

                    <div class="panel-body">
                        <table class="table table-striped holiday-table">
                            <thead>
                                <th>Date</th>
                                <th>Holiday</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($holidays as $holiday)
                                    <tr>
                                        <td>{{Carbon\Carbon::parse($holiday->date)->toDayDateTimeString()}}</td>
                                        <td class="table-text"><div>{{ $holiday->name }}</div></td>

                                        <!-- Holiday Delete Button -->
                                        <td>
                                          @if(Auth::user()->hasRole("HR"))
                                            <form action="{{url('/holidays/' . $holiday->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button type="submit" id="delete-holiday-{{ $holiday->id }}" class="btn btn-danger">
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
        @if(Auth::user()->hasRole("HR"))
          <a class="btn btn-primary btn-large" href="/holidays/new">New Holiday</a>
        @endif
    </div>
@endsection
