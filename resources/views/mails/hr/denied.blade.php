@extends('mails.layout')
@section('content')

<div style="margin-top: 50px">
		<p style="margin-top: 10px;">
			Dear {{$applicant->getName()}},
		</p>

		<p style="margin-top: 10px;">
			I'm sorry to inform you that HR denied your leave application.
			Reason being: "{{$leave_request->stand_in_response_reason}}". This is very unusual, I would suggest you meet them to resolve this.
		</p>
</div>
@endsection
