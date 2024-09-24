@extends('mails.layout')
@section('content')

<div style="margin-top: 50px">
		<p style="margin-top: 10px;">
			Dear {{$applicant->getName()}},
		</p>

		<p style="margin-top: 10px;">
			I'm sorry I have sad news. Your supervisor, {{$approver->getName()}} denied your leave request.
			 Reason being "{{$leave_request->supervisor_response_reason}}". Did you discuss with
			@if($approver->gender == 'Female') <span>her</span>
			@else <span>him</span>
			@endif
			beforehand? If yes, you both need to talk.
		</p>

</div>
@endsection
