@extends('mails.layout')
@section('content')

<div style="margin-top: 50px">
		<p style="margin-top: 10px;">
			Dear {{$to->name}},
		</p>

		<p style="margin-top: 10px;">
			This is to inform you that {{$approver->name}} just declined to stand in for {{$applicant->name}}. Reason being "{{$leave_request->stand_in_response_reason}}".
		</p>

</div>
@endsection
