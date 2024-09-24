@extends('mails.layout')
@section('content')

<div style="margin-top: 50px">
		<p style="margin-top: 10px;">
			Dear {{$applicant->getName()}},
		</p>

		<p style="margin-top: 10px;">
			Due to the declaration of {{Carbon\Carbon::parse($holiday->date)->toFormattedDateString()}} as a public holiday, your resumption date has been adjusted to {{Carbon\Carbon::parse($leave_request->end_date)->toFormattedDateString()}}.
		</p>

</div>
@endsection
