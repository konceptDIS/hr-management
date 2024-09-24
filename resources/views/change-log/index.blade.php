@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="rows">

            <!-- Current Designations -->
            @if (count($changes) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Change Log</h1>
                    </div>

                    <div class="panel-body">
                        <table class="table table-striped holidays-table">
                            <thead>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Description</th>
                            </thead>
                            <tbody>
                                @foreach ($changes as $change)
                                    <tr>
                                      <td class="table-text" style="white-space: nowrap"><div>{{ $change->date }}</div></td>
                                      <td class="table-text" style="white-space: nowrap"><div>{{ $change->name }}</div></td>
                                      <td class="table-text" ><div>{{ $change->description }}</div></td>
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
