@extends('mails.layout')
@section('content')
<div style="margin-top: 50px">
		<p style="margin-top: 10px;">
			Dear Sir/Madam,
		</p>

		<p style="margin-top: 10px;">
		This is to inform you that {{$applicant->name}} is going on leave.
		</p>

		<p style="margin-top: 10px;">
			The leave will last
			{{$leave_request->days_requested}} day(s), starting from {{Carbon\Carbon::parse($leave_request->start_date)->toFormattedDateString()}}
			to {{Carbon\Carbon::parse($leave_request->end_date)->subDay()->toFormattedDateString()}} to resume on {{Carbon\Carbon::parse($leave_request->end_date)->toFormattedDateString()}}
		</p>

</div>
@endsection
