@extends('layouts.app')
@section('title', 'View Application')
@section('content')
<div class="container">
    <div class="row">
    <h1>Pending Applications</h1>
        <p>Dear {{ $user->name }},</p>
        <p>You have pending leave requests, this is what you can do:</p>
        <p>1. If this is a new request, that you intend to go for, please get your stand in and supervisor to approve.</p>
        <p>2. If this is a past request, that you went for, please get your stand in and supervisor to approve.</p>
        <p>3. If this is a past request, that you <u>did not go for</u>, please get your stand in to decline. If you stand in already approved, get your supervisor to <b>DENY</b>.</p>
        <p>4. If this is a duplicate of an approved request, please get your stand in to decline. If you stand in already approved, get your supervisor to <b>DENY</b>.</p>
        <p>5. If you no longer have access to your standin or supervisor for whatever reason, you can substitute with someone else.</p>
        @if($leaverequests)
        <div class="panel panel-default">
                    <div class="panel-body">
	                    @if ($leaverequests && $leaverequests->isEmpty() == false)
	                        <table class="table table-striped task-table">
	                            <thead>
	                                <th>#</th>
	                                <th>Date</th>
	                                <th>Type</th>
	                                <th>Starting</th>
	                                <th>Ending</th>
                                    <th class="text-center">Before</th>
	                                <th class="text-center">Request</th>
	                                <th class="text-center">After</th>
	                                <th>Status</th>
	                                <th style="white-space:nowrap;">&nbsp;</th>
	                            </thead>
	                            <tbody>
	                                @foreach ($leaverequests as $leavereq)
	                                    <tr class="{{ $leavereq->hr_response == true ? 'success' : ''}}">
                                            <td class="table-text">
	                                    		<span>{{ $leavereq->id }}</span>
	                                    	</td>
                                            <td class="table-text">
	                                        	<span>
	                                        		{{ Carbon\Carbon::parse($leavereq->created_at)->toDateTimeString() }}
	                                        	</span>
	                                      </td>
                                          	<td class="table-text">
	                                    		<span>{{ $leavereq->leave_type }}</span>
	                                    	</td>
	                                      <td class="table-text">
	                                        	<span>
	                                        		{{ Carbon\Carbon::parse($leavereq->start_date)->toFormattedDateString() }}
	                                        	</span>
	                                      </td>

	                                      <td class="table-text">
	                                        	<span>
	                                        		{{ Carbon\Carbon::parse($leavereq->end_date)->toFormattedDateString() }}
	                                        	</span>
	                                      </td>

	                                    	<td class="text-center">
	                                        	<span class="{{ $leavereq->days_left ==0 ? 'danger' : ''}}">
	                                        		{{ $leavereq->days_left}} {{$leavereq->days_left >1 ? 'days' : 'day' }}
                                                    @if($leavereq->increment)
                                                        (+{{ $leavereq->increment}})
                                                    @endif
	                                        	</span>
	                                      </td>
                                          <td class="text-center">
                                                <span>
                                                    {{$leavereq->days_requested}} {{$leavereq->days_requested >1 ? 'days' : 'day' }}
                                                </span>
                                            </td>
                                          <td class="text-center">
	                                        	<span class="{{ $leavereq->days_left ==0 ? 'danger' : ''}}">
	                                        		{{ $leavereq->days_left-$leavereq->days_requested}} {{$leavereq->days_left-$leavereq->days_requested >1 ? 'days' : 'day' }}
	                                        	</span>
	                                      </td>
	                                      <td class="table-text">
                                                <span>
                                                    {{$leavereq->getStatus()}}
                                                </span>
	                                        </td>

	                                        <!-- Leave Request Delete Button -->
	                                        <td style="white-space: nowrap;">

	                                            <form action="{{url('requests/' . $leavereq->id)}}" method="POST">
	                                                {{ csrf_field() }}
	                                                {{ method_field('DELETE') }}
                                                <a href="{{ url('view?applicationId=' . $leavereq->id)}}" class="btn btn-default">View</a>
                                                @if($leavereq->recallable())
                                                <a href="{{ url('refunds/new?id=' . $leavereq->id)}}" class="btn btn-default">Refund</a>
                                                @endif
                                                @if($leavereq->allowEdit())
                                                <a href="{{ url('edit?id=' . $leavereq->id)}}" class="btn btn-default">Edit</a>
                                                @endif

	                                                @if($leavereq->allowDelete())
	                                                <button type="submit" name="delete-leave-request-{{ $leavereq->id }}" id="delete-leave-request-{{ $leavereq->id }}" class="btn btn-danger">
	                                                    <i class="fa fa-btn fa-trash"></i>Delete
	                                                </button>
	                                                @endif
	                                            </form>
	                                        </td>
	                                    </tr>
	                                @endforeach
	                            </tbody>
	                        </table>
	                    @else
	                        <p>You have no leave applications</p>
	                    @endif
                    </div>
                </div>
        @endif
    </div>
</div>
@endsection
