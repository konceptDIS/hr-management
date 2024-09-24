@extends('mails.layout')
@section('content')

<div style="margin-top: 50px">
		<p style="margin-top: 10px;">
			Dear {{$approver->getName()}},
		</p>

		<p style="margin-top: 10px;">
			{{$applicant->getName()}} has applied for leave and needs your approval. The leave will last {{$leave_request->days_requested}} day(s),
			beginning on {{Carbon\Carbon::parse($leave_request->start_date)->toFormattedDateString()}}
			and ending on {{Carbon\Carbon::parse($leave_request->end_date)->subDay()->toFormattedDateString()}}. {{ $applicant->gender =='Male' ? 'He' : 'She' }} will resume on {{Carbon\Carbon::parse($leave_request->end_date)->toFormattedDateString()}}.
		</p>

		<p style="margin-top: 10px;">
			Please <a href="{{ url('/view?applicationId=' . $leave_request->id)}}">click here</a> to approve or decline the request.
		</p>
	</div>
@endsection
