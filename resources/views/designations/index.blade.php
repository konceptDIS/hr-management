@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="rows">

            <!-- Current Designations -->
            @if (count($designations) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Designations</h1>
                    </div>

                    <div class="panel-body">
                        <table class="table table-striped holidays-table">
                            <thead>
                                <th>Designation</th>
                                <th>Leave Days</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($designations as $designation)
                                    <tr>
                                      <td class="table-text"><div>{{ $designation->name }}</div></td>
                                      <td class="table-text"><div>{{ $designation->leave_days }}</div></td>

                                        <!-- Designation Delete Button -->
                                        <td>
                                        @if(Auth::user()->hasRole("HR"))
                                            <form action="{{url('/designations/' . $designation->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button type="submit" id="delete-designation-{{ $designation->id }}" class="btn btn-danger">
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
    <a class="btn btn-primary btn-large" href="/designations/new/">New Designation</a>
    </div>
@endsection
