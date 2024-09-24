@extends('mails.layout')
@section('content')
<div style="margin-top: 50px">
		<p style="margin-top: 10px;">
			Dear {{$applicant->getName()}},
		</p>

		<p style="margin-top: 10px;">
			{{$approver->getName()}} declined standing in for you! Reason being "{{$leave_request->stand_in_response_reason}}".
			Did you discuss with @if($approver->gender == 'Female') <span>her</span> @else <span>him</span>@endif beforehand? 
			If yes, you both need to talk.
		</p>
</div>
@endsection
