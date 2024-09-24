@extends('mails.layout')
@section('content')

<div style="margin-top: 50px">
	<p style="margin-top: 10px;">
		Dear {{$applicant->getName()}},
	</p>

	<p style="margin-top: 10px;">
		You have deleted your leave request #{{$leave_request->id}}!
	</p>
	<p>Here are the details of the deleted request</p>
	<div class="{{ $leave_request->hr_response == true ? 'success' : ''}} {{ $leave_request->days_left ==0 ? 'danger' : ''}}">
		<!--Request Details -->
		<div class="container"  id="request-details">
			<div class="container">
				<div class="request-row">
					<div class="col-sm-12" style="background-color:whitesmoke; padding-top:10px;margin-bottom: 20px;border-bottom:solid 3px lightgray;">
						<span class="heading">{{ $leave_request->name}} | {{ $applicant->designation }} | {{ $applicant->department }}</span>
					</div>
				</div>
				
				<div class="request-row" >
					<div class="col-sm-2">
						<label>Leave Type</label>
						<div>{{ $leave_request->leave_type }}</div>
					</div>
				</div>
				<div class="request-row">
					<div class="col-sm-2">
						<label>Days Requested</label>
						<div>{{$leave_request->days_requested}}</div>
					</div>
				</div>
				<div class="request-row">
					<div class="col-sm-2">
						<label>Starting</label>
						<div >{{ Carbon\Carbon::parse($leave_request->start_date)->toFormattedDateString() }}</div>
					</div>
				</div>
				<div class="request-row">
					<div class="col-sm-2">
						<label>Ending</label>
						<div>{{ Carbon\Carbon::parse($leave_request->end_date)->toFormattedDateString() }}</div>
					</div>
				</div>
				<div class="request-row">
					<div class="col-sm-2">
						<label>Days Available</label>
						<div>{{ $leave_request->days_left}}</div>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
			<div style="margin-top:20px;" class="container">
				<div >
				<div class="col-sm-3">
					<label>Stand In</label>
					<div>{{ $leave_request->stand_in_username }}</div>
					</div>
				</div>
				<div  >
				<div class="col-sm-3">
					<label>Stand In Response</label>
					<div>
					@if($leave_request->stand_in_response === 1) <span>Accepted on </span> {{ $leave_request->stand_in_response_date}} @endif
					@if($leave_request->stand_in_response === null) <span>No Response</span> @endif
					@if($leave_request->stand_in_response === 0) <span>Refused</span> {{ $leave_request->stand_in_response_reason}} @endif
					</div>
					</div>
				</div>
				<div >
				<div class="col-sm-3">
					<label>Supervisor</label>
					<div>{{ $leave_request->supervisor_username }}</div>
					</div>
				</div>
				<div >
				<div class="col-sm-3">
					<label>Supervisor Response</label>
					<div>
					@if($leave_request->supervisor_response === 1) <span>Accepted on</span> {{$leave_request->supervisor_response_date}}  @endif
					@if($leave_request->supervisor_response === null) <span>No Response</span> @endif 
					@if($leave_request->supervisor_response === 0) <span>Refused</span> {{ $leave_request->supervisor_response_reason}} @endif
					</div>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="container" style="margin-top:20px;">
				<div class="col-sm-6">
				<div class="container" style="background-color:whitesmoke;padding-left:5px;padding-top:5px;padding-bottom:5px;">Reason:</div>
				<div style="padding-left:5px; padding-top:5px;">{{$leave_request->reason}}</div>
				</div>
				<div class="col-sm-6">
				<div class="container" style="background-color:whitesmoke; padding-left:5px;padding-top:5px;padding-bottom:5px;">Status</div>
				<div style="padding-left:5px; padding-top:5px;">{{ $leave_request->getStatus(1)}}</div>
				</div>
			</div>
			
			<div class="clearfix"></div>
		</div>
	</div>
</div>
@endsection
