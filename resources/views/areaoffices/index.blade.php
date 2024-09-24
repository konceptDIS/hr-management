@extends('layouts.app')
@section('title', 'Area Offices')
@section('content')
    <div class="container">
        <div class="rows">

            <!-- Current Area Offices -->
            @if (count($areaoffices) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Area Offices</h1>
                    </div>

                    <div class="panel-body">
                        <table class="table table-striped holiday-table">
                            <thead>
                                <th>Region</th>
                                <th>Name</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($areaoffices as $areaoffice)
                                    <tr>
                                        <td>{{$areaoffice->region}}</td>
                                        <td class="table-text"><div>{{ $areaoffice->name }}</div></td>

                                        <!-- Holiday Delete Button -->
                                        <td>
                                        @if(Auth::user()->hasRole("HR"))
                                            <form action="{{url('/areaoffices/' . $areaoffice->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button type="submit" id="delete-areaoffice-{{ $areaoffice->id }}" class="btn btn-danger">
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
    <a class="btn btn-primary btn-large" href="/areaoffices/new/?new=true">New Area Office</a>

    </div>
@endsection
