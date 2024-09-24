@extends('mails.layout')
@section('content')

<div style="margin-top: 50px">
		<p style="margin-top: 10px;">
			Dear {{$to->name}},
		</p>

		<p style="margin-top: 10px;">
			This is to confirm that {{$approver->name}} just declined to approve a leave request by {{$applicant->name}}. Reason being "{{$leave_request->supervisor_response_reason}}".
		</p>

</div>
@endsection
