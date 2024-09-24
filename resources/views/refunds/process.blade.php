@extends('layouts.app')
@section('title', 'Process Leave Refund')
@section('content')
<div class="container">
   <div class="row">
     @if(isset($flash_message))
     <div class="alert alert-info">
       <strong>Info!</strong> {{$flash_message}}
     </div>
     @endif
      <div class="panel panel-default">

          <div class="panel-heading">
              <h1>{{$title}}</h1>
          </div>

          <div class="panel-body">
              <!--Pending Stand In -->
              @if (isset($recall) ==false)
                  <p>Nothing for your attention</p>
              @else
                    <div class="{{ $recall->supervisor_response == true ? 'success' : ''}} {{ $recall->days_credited ==0 ? 'danger' : ''}}">
                        <!--Request Details -->
                          <div class="container"  id="request-details">
                            <div class="container">
                                        <div class="request-row">
                                          <div class="col-sm-12" style="background-color:whitesmoke; padding-top:10px;margin-bottom: 20px;border-bottom:solid 3px lightgray;">
                                                <span class="heading">{{ $recall->name}}</span>
                                          </div>
                                        </div>
                                        <div class="request-row" >
                                          <div class="col-sm-2">
                                              <label>Leave Type</label>
                                              <div>{{ $recall->leave_type }}</div>
                                            </div>
                                        </div>
                                        <div class="request-row">
                                          <div class="col-sm-1">
                                              <label>Duration</label>
                                              <div>{{$recall->approval->days}}</div>
                                            </div>
                                        </div>
                                        <div class="request-row">
                                          <div class="col-sm-2">
                                              <label>Starting</label>
                                              <div >{{ Carbon\Carbon::parse($recall->application->start_date)->toFormattedDateString() }}</div>
                                            </div>
                                        </div>
                                        <div class="request-row">
                                          <div class="col-sm-2">
                                              <label>Ending</label>
                                              <div>{{ Carbon\Carbon::parse($recall->application->end_date)->toFormattedDateString() }}</div>
                                            </div>
                                        </div>
                                        <div class="request-row">
                                          <div class="col-sm-2">
                                              <label>Date Recalled</label>
                                              <div>{{ Carbon\Carbon::parse($recall->date)->toFormattedDateString() }}</div>
                                            </div>
                                        </div>
                                        <div class="request-row">
                                            <div class="col-sm-2">
                                              <label>Days to be credited</label>
                                              <div>{{ $recall->days_credited}}</div>
                                            </div>
                                        </div>
                           </div>
                          <div class="clearfix"></div>
                            <div class="container" style="margin-top:20px;">
                              <div class="col-sm-6">
                                <div class="container" style="background-color:whitesmoke;padding-left:5px;padding-top:5px;padding-bottom:5px;">Reason:</div>
                                <div style="padding-left:5px; padding-top:5px;">{{$recall->reason}}</div>
                              </div>
                              <div class="col-sm-6">
                                <div class="container" style="background-color:whitesmoke; padding-left:5px;padding-top:5px;padding-bottom:5px;">Status</div>
                                <div style="padding-left:5px; padding-top:5px;">{{ $recall->getStatus()}}{{ $recall->supervisor_response_reason}}</div>
                              </div>
                            </div>
                          <div class="clearfix"></div>
                          <!--Request Action Buttons ---->
                          <div class="row" style="margin-bottom: 30px; margin-top:20px; padding-right:10px; padding-left:10px;">
                                         

                                          <!-- Superviser Decline -->
                                          @if($recall->supervisor_response === null && strtolower($recall->supervisor_username) == strtolower(Auth::user()->username))
                                            <button type="button" id="deny-btn-{{$recall->id}}" class="show-no-ui btn btn-default pull-left">Decline</button>

                                            <!-- Modal -->
                                            <div class="clear-fix"></div>
                                            <div id="deny-ui-{{$recall->id}}" class="no-box container" style="min-height:100px; display:block;">
                                              <form class="d-inline" action="{{url('refunds/dismiss/' . $recall->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                <div>
                                                  <h4 >Reason for declining</h4>
                                                </div>
                                                <div>
                                                  <textarea style="width:100%; margin-bottom:10px;"
                                                  placeholder="Please type your reason here" rows="5"
                                                   id="id-supervisor-deny-reason-{{$recall->id}}"
                                                   name="reason"></textarea>
                                                </div>
                                                <a class="pull-left close-no-ui" id="cancel-id-{{$recall->id}}" href="#">Cancel</a>
                                                <button type="submit" id="submit-{{ $recall->id }}"
                                                  class="submit-btn btn btn-default pull-right"><i class="fa fa-btn fa-trash"></i>Submit</button>
                                              </form>
                                          </div>
                                          @endif

                                          <!--Supervisor Approve -->
                                          <form class="d-inline" action="{{url('refunds/confirm/' . $recall->id)}}" method="POST">
                                                  {{ csrf_field() }}

                                                  @if($recall->supervisor_response === null && $recall->supervisor_username==Auth::user()->username)
                                                  <button type="submit" id="accept-btn-{{ $recall->id }}" class="accept-btn btn btn-success pull-right" >
                                                      <i class="fa fa-btn fa-check"></i>Confirm
                                                  </button>
                                                  @endif
                                          </form>
                          </div>
                      </div>
                    </div>
              @endif
          </div>
      </div>
  </div>

</div>
@endsection
