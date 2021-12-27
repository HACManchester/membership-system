@extends('layouts.main')

@section('page-title')
    Member Storage - View Box
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading">
                    View Box Information
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h2>📦 Storage Location {{ $box->location }}</h2>
                            <small>(#{{ $box->id }})</small>


                            <h2>
                                @if($box->user)
                                    @if($box->user->active)
                                        🟡 Claimed
                                    @else
                                        ⚠️ Member left
                                    @endif
                                @else
                                    🟢 Available
                                @endif

                                @if($box->user && !$box->user->active)
                                ⚠️ Member left
                                @elseif($box->user && $box->user->active)
                                
                                @elseif (($volumeAvailable >= $box->size) && !$box->user)
                                    @if ($box->location == "Old Members Storage" || Auth::user()->online_only)
                                        ⛔ Not available to be claimed
                                    @else
                                        {!! Form::open(array('method'=>'PUT', 'route' => ['storage_boxes.update', $box->id], 'class'=>'navbar-left')) !!}
                                        {!! Form::hidden('user_id', Auth::user()->id) !!}
                                        {!! Form::submit('Claim', array('class'=>'btn btn-default')) !!}
                                        {!! Form::close() !!}
                                    @endif
                                @endif

                            </h2>
                        </div>
                        <div class="col-md-6">
                            <h4>QR code for storage location {{ $box->id }}</h4>
                        </div>
                    </div>
                    
                </div>
            </div>
            </div>
    </div>
    <div class="row">
        @if (Auth::user()->isAdmin() || Auth::user()->hasRole('storage'))
            <div class="col-md-6" style="background:repeating-linear-gradient( 45deg, #fafafa, #fafafa 40px, #fff 40px, #fff 80px )">
                <div class="row">
                    <div class="col-md-3">
                        @if($box->user)
                            {!! Form::open(array('method'=>'PUT', 'route' => ['storage_boxes.update', $box->id], 'class'=>'navbar-left')) !!}
                            {!! Form::hidden('user_id', '') !!}
                            {!! Form::submit('Reclaim', array('class'=>'btn btn-default btn-sm')) !!}
                            {!! Form::close() !!}                        
                        @endif
                    </div>
                    <div class="col-md-9">
                        {!! Form::open(array('method'=>'PUT', 'route' => ['storage_boxes.update', $box->id])) !!}
                        {!! Form::select('user_id', [''=>'Allocate member']+$memberList, null, ['class'=>'form-control js-advanced-dropdown']) !!}
                        {!! Form::submit('✔️', array('class'=>'btn btn-default btn-xs')) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        @endif
        
    </div>
@stop
