@extends('layouts.app')
@section('title', 'View Application')
@section('content')
<div class="container">
  <!--Pending Approval -->
   <div class="row">
     
      <div class="panel panel-default">

          <div class="panel-heading">
              <h1>{{$title}}</h1>
          </div>

          <div class="panel-body">
              <!--Pending Stand In -->
              @if(isset($flash_message))
                <div class="alert alert-info">
                <strong>Info!</strong> {{$flash_message}}
                </div>
                @endif
              @if (isset($leavereq) ==false)
                  <p>Nothing for your attention</p>
              @else
                    <div class="{{ $leavereq->hr_response == true ? 'success' : ''}} {{ $leavereq->days_left ==0 ? 'danger' : ''}}">
                        <!--Request Details -->
                          <div class="container"  id="request-details">
                            <div class="container">
                                        <div class="request-row">
                                          <div class="col-sm-12" style="background-color:whitesmoke; padding-top:10px;margin-bottom: 20px;border-bottom:solid 3px lightgray;">
                                                <span class="heading">{{ $leavereq->name}} | {{ $applicant->designation }} | {{ $applicant->department }}</span>
                                          </div>
                                        </div>
                                        
                                        <div class="request-row" >
                                          <div class="col-sm-2">
                                              <label>Leave Type</label>
                                              <div>{{ $leavereq->leave_type }}</div>
                                            </div>
                                        </div>
                                        <div class="request-row">
                                          <div class="col-sm-2">
                                              <label>Days Requested</label>
                                              <div>{{$leavereq->days_requested}}</div>
                                            </div>
                                        </div>
                                        <div class="request-row">
                                          <div class="col-sm-2">
                                              <label>Starting</label>
                                              <div >{{ Carbon\Carbon::parse($leavereq->start_date)->toFormattedDateString() }}</div>
                                            </div>
                                        </div>
                                        <div class="request-row">
                                          <div class="col-sm-2">
                                              <label>Ending</label>
                                              <div>{{ Carbon\Carbon::parse($leavereq->end_date)->toFormattedDateString() }}</div>
                                            </div>
                                        </div>
                                        <div class="request-row">
                                            <div class="col-sm-2">
                                              <label>Days Available</label>
                                              <div>{{ $leavereq->days_left}}</div>
                                            </div>
                                        </div>
                           </div>
                          <div class="clearfix"></div>
                          <div style="margin-top:20px;" class="container">
                                          <div >
                                            <div class="col-sm-3">
                                                <label>Stand In</label>
                                                <div>{{ $leavereq->stand_in_username }}</div>
                                              </div>
                                          </div>
                                          <div  >
                                            <div class="col-sm-3">
                                                <label>Stand In Response</label>
                                                <div>
                                                @if($leavereq->stand_in_response === 1) <span>Accepted on </span> {{ $leavereq->stand_in_response_date}} @endif
                                                @if($leavereq->stand_in_response === null) <span>No Response</span> @endif
                                                @if($leavereq->stand_in_response === 0) <span>Refused</span> {{ $leavereq->stand_in_response_reason}} @endif
                                                </div>
                                              </div>
                                          </div>
                                          <div >
                                            <div class="col-sm-3">
                                                <label>Supervisor</label>
                                                <div>{{ $leavereq->supervisor_username }}</div>
                                              </div>
                                          </div>
                                          <div >
                                            <div class="col-sm-3">
                                                <label>Supervisor Response</label>
                                                <div>
                                                @if($leavereq->supervisor_response === 1) <span>Accepted on</span> {{$leavereq->supervisor_response_date}}  @endif
                                                @if($leavereq->supervisor_response === null) <span>No Response</span> @endif 
                                                @if($leavereq->supervisor_response === 0) <span>Refused</span> {{ $leavereq->supervisor_response_reason}} @endif
                                                </div>
                                              </div>
                                          </div>
                                        </div>
                          <div class="clearfix"></div>
                            <div class="container" style="margin-top:20px;">
                              <div class="col-sm-6">
                                <div class="container" style="background-color:whitesmoke;padding-left:5px;padding-top:5px;padding-bottom:5px;">Reason:</div>
                                <div style="padding-left:5px; padding-top:5px;">{{$leavereq->reason}}</div>
                              </div>
                              <div class="col-sm-6">
                                <div class="container" style="background-color:whitesmoke; padding-left:5px;padding-top:5px;padding-bottom:5px;">Status</div>
                                <div style="padding-left:5px; padding-top:5px;">{{ $leavereq->getStatus(1)}}</div>
                              </div>
                            </div>
                          @if(sizeOf($documents)>0)
                          <div class="clearfix"></div>
                          <div class="container" style="margin-top:20px;">
                              <div class="col-sm-12">
                                <table class="table table-striped holiday-table">
                                    <thead>
                                        <th>Supporting documents</th>
                                        <th>&nbsp;</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($documents as $document)
                                            <tr>
                                                <td class="table-text"><div>{{ $document->description }}</div></td>
                                                <!-- Document Delete Button -->
                                                <td>
                                                    <a class="btn btn-primary btn-large pull-right" href="{{ url('/documents/download?id=' . $document->id) }}">Download</a>
                                                
                                                @if(Auth::user()->hasRole("CM"))
                                                    <form action="{{url('/documents/' . $document->id)}}" method="POST">
                                                        {{ csrf_field() }}
                                                        {{ method_field('DELETE') }}

                                                        <button type="submit" id="delete-vendor-{{ $document->id }}" class="btn btn-danger">
                                                            <i class="fa fa-btn fa-trash"></i>Delete
                                                        </button>
                                                    </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                              </div>
                          </div>
                          @endif
                          <div class="clearfix"></div>
                          <!--Request Action Buttons ---->
                          <div class="row" style="margin-bottom: 30px; margin-top:20px; padding-right:10px; padding-left:10px;">
                                          <!--Stand in Decline Request -->
                                          @if(trim(strtolower($leavereq->stand_in_username))==trim(strtolower(Auth::user()->username)) && $leavereq->stand_in_response===null)
                                            <button type="button" class="hidden btn btn-default pull-left" data-toggle="modal" data-target="#myModal-decline-stand-in-request-{{$leavereq->id}}">Decline</button>
                                            <button type="button" id="deny-btn-{{$leavereq->id}}" class="show-no-ui btn btn-default pull-left" data-toggle="-modal" data-target="#AmyModal-decline-stand-in-request-{{$leavereq->id}}">Decline</button>

                                            <div id="deny-ui-{{$leavereq->id}}" class="no-box container" style="min-height:100px; display:block;">
                                            <form class="d-inline" action="{{url('decline-stand-in-request/' . $leavereq->id)}}" method="POST">
                                              {{ csrf_field() }}
                                              <div>
                                                <h4 >Reason for declining</h4>
                                              </div>
                                              <div>
                                                <textarea style="width:100%; margin-bottom:10px;"
                                                placeholder="Please type your reason here" rows="5"
                                                 id="id-decline-stand-in-request-reason-{{$leavereq->id}}"
                                                 name="decline-stand-in-request-reason-{{$leavereq->id}}"></textarea>
                                              </div>
                                              <a class="pull-left close-no-ui" id="cancel-id-{{$leavereq->id}}" href="#">Cancel</a>
                                              <button type="submit" id="submit-{{ $leavereq->id }}"
                                                class="submit-btn btn btn-default pull-right"><i class="fa fa-btn fa-trash"></i>Submit</button>
                                            </form>
                                          </div>

                                          @endif

                                          <!-- Stand In Accept Request -->
                                          <form class="d-inline" action="{{url('accept-stand-in-request/' . $leavereq->id)}}" method="POST">{{ csrf_field() }}
                                              @if(trim(strtolower($leavereq->stand_in_username))==trim(strtolower(Auth::user()->username)) && $leavereq->stand_in_response===null)
                                                  <button type="submit" id="accept-btn-{{ $leavereq->id }}" class="accept-btn btn btn-success pull-right" >
                                                      <i class="fa fa-btn fa-check"></i>Accept
                                                  </button>
                                              @endif
                                          </form>

                                          <!-- Superviser Decline -->
                                          @if($leavereq->stand_in_response == true &&$leavereq->supervisor_response === null && trim(strtolower($leavereq->supervisor_username)) == trim(strtolower(Auth::user()->username)))
                                            <button type="button" class="hidden btn btn-default pull-left" data-toggle="modal" data-target="#myModal-supervisor-deny-{{$leavereq->id}}">Decline</button>
                                            <button type="button" id="deny-btn-{{$leavereq->id}}" class="show-no-ui btn btn-default pull-left">Decline</button>

                                            <!-- Modal -->
                                            <div class="clear-fix"></div>
                                            <div id="deny-ui-{{$leavereq->id}}" class="no-box container" style="min-height:100px; display:block;">
                                              <form class="d-inline" action="{{url('supervisor-deny-request/' . $leavereq->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                <div>
                                                  <h4 >Reason for declining</h4>
                                                </div>
                                                <div>
                                                  <textarea style="width:100%; margin-bottom:10px;"
                                                  placeholder="Please type your reason here" rows="5"
                                                   id="id-supervisor-deny-reason-{{$leavereq->id}}"
                                                   name="supervisor-deny-reason-{{$leavereq->id}}"></textarea>
                                                </div>
                                                <a class="pull-left close-no-ui" id="cancel-id-{{$leavereq->id}}" href="#">Cancel</a>
                                                <button type="submit" id="submit-{{ $leavereq->id }}"
                                                  class="submit-btn btn btn-default pull-right"><i class="fa fa-btn fa-trash"></i>Submit</button>
                                              </form>
                                          </div>
                                          @endif

                                          <!--Supervisor Approve -->
                                          <form class="d-inline" action="{{url('supervisor-approve-request/' . $leavereq->id)}}" method="POST">
                                                  {{ csrf_field() }}

                                                  @if($leavereq->stand_in_response==true &&$leavereq->supervisor_response === null && trim(strtolower($leavereq->supervisor_username)) == trim(strtolower(Auth::user()->username)))
                                                  <button type="submit" id="accept-btn-{{ $leavereq->id }}" class="accept-btn btn btn-success pull-right" >
                                                      <i class="fa fa-btn fa-check"></i>Approve
                                                  </button>
                                                  @endif
                                          </form>

                                      <!--HR Decline-->
                                          @if($leavereq->stand_in_response==true && $leavereq->supervisor_response == true &&
                                                  $leavereq->hr_response === null && Auth::user()->hasRole("HR"))
                                                  <button type="button" class="hidden btn btn-default pull-left" data-toggle="modal" data-target="#myModal-hr-deny-{{$leavereq->id}}">Decline</button>
                                                  <button type="button" id="deny-btn-{{$leavereq->id}}"  class="show-no-ui btn btn-default pull-left">Decline</button>

                                                  <!-- Modal -->
                                                  <div class="clear-fix"></div>
                                                  <div id="deny-ui-{{$leavereq->id}}" class="no-box container" style="min-height:100px; display:block;">
                                                    <form class="d-inline" action="{{url('hr-deny/' . $leavereq->id)}}" method="POST">
                                                      {{ csrf_field() }}
                                                      <div>
                                                        <h4 >Reason for denial</h4>
                                                      </div>
                                                      <div>
                                                        <textarea style="width:100%;" placeholder="Please type your reason here" rows="5" id="id-hr-deny-reason-{{$leavereq->id}}" name="hr-deny-reason-{{$leavereq->id}}"></textarea>
                                                      </div>
                                                      <a class="pull-left close-no-ui" id="cancel-btn-{{$leavereq->id}}" href="#">Cancel</a>
                                                      <button type="submit" id="submit-{{$leavereq->id}}"
                                                        class="submit-btn btn btn-default pull-right"><i class="submit-btn fa fa-btn fa-trash"></i>Submit</button>
                                                    </form>
                                                  </div>
                                          @endif

                                          <!---HR Approve-->
                                          <div class="d-inline">
                                              <form class="d-inline" class="" action="{{url('hr-approve/' . $leavereq->id)}}" method="POST">
                                                  {{ csrf_field() }}

                                                  @if( $leavereq->stand_in_response==true &&$leavereq->supervisor_response == true &&
                                                  $leavereq->hr_response === null && Entrust::hasRole("HR"))
                                                  <button type="submit" id="accept-btn-{{ $leavereq->id }}" class="accept-btn btn btn-success pull-right" >
                                                      <i class="fa fa-btn fa-check"></i>Approve
                                                      </button>
                                                  @endif
                                              </form>
                                          </div>
                          </div>
                      </div>
                    </div>
              @endif
          </div>
      </div>
  </div>
  @if(Entrust::hasRole("HR"))
  <a class="btn btn-danger" href="/deleteable/create?leave_request_id={{$leavereq->id}}&applicant_username={{$applicant->username}}">Refund</a>
  @endif
</div>
@endsection
