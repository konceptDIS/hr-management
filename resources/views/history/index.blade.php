@extends('layouts.app')
@section('title', 'History')
@section('content')
<div class="container">
 @if($title == null)
	$title = Auth::user()->name . " Leave History";
 @endif
    <!-- My Requests-->
    <div class="row">
            <!-- My Leave Requests -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>{{$title}}</h1>
                    </div>
                    <div class="panel-body">
						@foreach($entries as $key => $years)
							<h1 class="text-center" style="border-bottom:solid 1px black; margin-top:20px; margin-bottom:20px; padding-bottom:10px;">{{$key}} Leave</h1>
							<div style="padding-left:10px">
								@if (count($years) > 0)
									@foreach($years as $year => $transactions)
										<h2 style="padding-top:10px; padding-bottom:10px;">{{$year}} {{$key}} Leave Transactions</h2>
											@if (count($transactions) > 0)
												<table style="margin-left:20px; margin-right:20px; max-width:99%" class="table table-striped table-responsive">
													<thead>
														<th>#</th>
														<th>Date</th>
														<th>Type</th>
														<th style="text-align:right;">Before</th>
														<th style="text-align:right;">Days</th>
														<th style="text-align:right;">After</th>
														<th style="white-space:nowrap;">&nbsp;</th>
														<th style="text-align:left;">Remarks</th>
													</thead>
													<tbody>
														@foreach ($transactions as $line)
															<tr>
																<td class="table-text" style="width:20px;min-width:20px;max-width:20px;">{{ $loop->iteration }}
																</td>
																<td class="table-text" style="width:200px;max-width:200px;">{{ $line->date }}
																</td>
																<td class="table-text" style="width:100px;max-width:100px;">{{ $line->type }}
																</td>
																<td class="table-text" style="width:70px; max-width:70px; text-align:right;">{{$line->before}}
																</td>
																<td class="table-text" style="width:70px; max-width:70px; text-align:right;">{{$line->amount}}
																</td>
																<td class="table-text" style="width:70px; max-width:70px; text-align:right;">{{$line->balance}}
																</td>																
																<td style="white-space: nowrap;">
																	@if($line->application_id)
																	<a href="{{ url('view?applicationId=' . $line->application_id)}}" class="lnk lnk-default pull-righto">View</a>
																	@endif
																</td>
																<td class="table-text" style="width:440px; max-width:440px; text-align:left;">{{ $line->remarks }}
																</td>
															</tr>
														@endforeach
													</tbody>
												</table>
											@else
												<p>None</p>
											@endif
									@endforeach
								@else
									<p>No leave activity</p>
								@endif
							</div>
						@endforeach
                    </div>
                </div>
    </div>
</div>
@endsection
