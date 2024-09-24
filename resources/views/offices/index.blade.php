@extends('layouts.app')
@section('title', 'Offices')
@section('content')
    <div class="container">
        <div class="rows">

            <!-- Current offices -->
            @if (count($offices) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Departments & Units</h1>
                    </div>

                    <div class="panel-body">
                        <table class="table table-striped office-table">
                            <thead>
                                <th>Id</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Is Under</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($offices as $office)
                                    <tr>
                                      <td class="table-text">{{$office->id}}</td>
                                      <td class="table-text">{{$office->name}}</td>
                                        <td class="table-text"><div>{{ $office->type }}</div></td>
                                        <td class="table-text"><div>{{ $office->parent }}</div></td>

                                        <!-- office Delete Button -->
                                        <td>
                                        @if(Auth::user()->hasRole("HR"))
                                            <form action="{{url('/offices/' . $office->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button type="submit" id="delete-office-{{ $office->id }}" class="btn btn-danger">
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
    <a class="btn btn-primary btn-large" href="/offices/new?new=true">New office</a>

    </div>
@endsection
