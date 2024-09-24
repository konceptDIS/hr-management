@extends('layouts.app')
@section('title', 'View Application')
@section('content')
<div class="container">
    <div class="row">
    <h1>Profile Incomplete</h1>
        <p>Dear {{ $person->name }},</p>
        <p>Your leave profile is incomplete, please get your office manager to update it. Here is a list of office managers for your convenience</p>
        @if($users)
            <table class="table table-stripped" style="background:white;">
                <tr>
                    <th>Name</th>
                    <th>Area Office</th>
                    <th>Region</th>
                    <th>Email</th>
                </tr>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->area_office !== "Select" ? $user->area_office : '' }}</td>
                    <td>{{ $user->region }}</td>
                    <td>{{ $user->username }}@abujaelectricity.com</td>
                </tr>
                @endforeach
            </table>
        @endif
    </div>
</div>
@endsection