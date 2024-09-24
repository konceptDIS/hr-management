@extends('mails.layout')
@section('content')
<div style="margin-top: 50px">
		<p style="margin-top: 10px;">
			Dear {{$applicant->getName()}},
		</p>

		<p style="margin-top: 10px;">
		Congratulations!!! Your leave request is approved.
		</p>

		<p style="margin-top: 10px;">
			Your leave will last
			{{$leave_request->days_requested}} day(s), starting from {{Carbon\Carbon::parse($leave_request->start_date)->toFormattedDateString()}}
			and ending on {{Carbon\Carbon::parse($leave_request->end_date)->subDay()->toFormattedDateString()}}. You are to resume on {{Carbon\Carbon::parse($leave_request->end_date)->toFormattedDateString()}}
		</p>

		<p style="margin-top:10px; font-style:italics;">
			Enjoy your holiday!
		</p>
</div>
@endsection
