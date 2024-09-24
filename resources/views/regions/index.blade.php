@extends('layouts.app')
@section('title', 'Regions')
@section('content')
    <div class="container">
        <div class="rows">

            <!-- Current Regions -->
            @if (count($regions) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Regions</h1>
                    </div>

                    <div class="panel-body">
                        <table class="table table-striped region-table">
                            <thead>
                                <th>Region</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($regions as $region)
                                    <tr>
                                        <td class="table-text"><div>{{ $region->name }}</div></td>

                                        <!-- Region Delete Button -->
                                        <td>
                                        @if(Auth::user()->hasRole("HR"))
                                            <form action="{{url('/regions/' . $region->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button type="submit" id="delete-region-{{ $region->id }}" class="btn btn-danger">
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
    <a class="btn btn-primary btn-large" href="/regions/new/">New Region</a>

    </div>
@endsection
