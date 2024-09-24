@extends('mails.layout')
@section('content')

<div style="margin-top: 50px">
		<p style="margin-top: 10px;">
			Dear {{$approver->getName()}},
		</p>

		<p style="margin-top: 10px;">
			This is to confirm that you just approved a leave request for {{$applicant->getName()}}!
		</p>

</div>
@endsection
