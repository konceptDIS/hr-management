@extends('mails.layout')
@section('content')
<div style="min-height:50px; text-align:center; background-color:light-gray">
<h1>For your information</h1>
</div>
<div style="margin-top: 50px">
		<p style="margin-top: 10px;">
			Dear Sir/Madam,
		</p>

		<p style="margin-top: 10px;">
			{{$applicant->getName()}} has applied for leave and wants {{$approver->getName()}} to stand in. The leave will last
			{{$leave_request->days_requested}} day(s), beginning on {{Carbon\Carbon::parse($leave_request->start_date)->toFormattedDateString()}}
			 and ending on {{Carbon\Carbon::parse($leave_request->end_date)->subDay()->toFormattedDateString()}}. {{ $applicant->gender =='Male' ? 'He' : 'She' }} will resume on {{Carbon\Carbon::parse($leave_request->end_date)->toFormattedDateString()}}.
		</p>

		<p style="margin-top: 10px;">
			Please <a href="{{ url('/view?applicationId=' . $leave_request->id)}}">click here</a> to view the request.
		</p>
</div>
@endsection
