@extends('mails.layout')
@section('content')

<div style="margin-top: 50px">
		<p style="margin-top: 10px;">
			Dear {{$approver->getName()}},
		</p>

		<p style="margin-top: 10px;">
			This is to confirm that you just declined a leave request by {{$applicant->getName()}} because "{{$leave_request->stand_in_response_reason}}".
		</p>

</div>
@endsection
