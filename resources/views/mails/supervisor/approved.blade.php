@extends('mails.layout')
@section('content')

<div style="margin-top: 50px">
		<p style="margin-top: 10px;">
			Dear {{$applicant->getName()}},
		</p>

		<p style="margin-top: 10px;">
			I have good news! Your supervisor {{$approver->getName()}} approved your leave request!
		</p>

</div>
@endsection
