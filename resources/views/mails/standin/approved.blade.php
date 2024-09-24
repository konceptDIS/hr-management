@extends('mails.layout')
@section('content')
<div style="margin-top: 50px">
		<p style="margin-top: 10px;">
			Dear {{$applicant->getName()}},
		</p>

		<p style="margin-top: 10px;">
			{{$approver->getName()}} has accepted to stand in for you! Your request is now awaiting your supervisor's approval.
			A gentle reminder at a good time could help.
		</p>

</div>
@endsection
