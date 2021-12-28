@extends('layouts.main')

@section('page-title')
    Member Storage - View Box
@stop

@section('content')
<style>
@media print
{    
    .no-print, .no-print *
    {
        display: none !important;
    }

    .mainSidenav, nav, header {
        display: none !important;
    }

    #bodyWrap {
        padding: 0;
    }

    .print-border {
        border: 5px solid black;
        border-radius: 10px;
    }

    .panel, .panel-heading{
        border: none;
    }

    h2 {
        font-size: 1.5em;
    }

    img {
        width: 50%;
    }
}
</style>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading no-print">
                    Storage Box Information
                </div>
                <div class="panel-body print-border">
                    <div class="row">
                        <div class="col-xs-6 print-border">
                            <h2>📦 {{ $box->location }} <small>(#{{ $box->id }})</small></h2>
                            <h2>
                                @if($box->user)
                                    @if($box->user->active)
                                        <span class="no-print">🟡</span> Claimed
                                    @else
                                        <span class="no-print">⚠️</span> Member left
                                        @endif
                                @else
                                @if ($box->location == "Old Members Storage")
                                        <span class="no-print">⛔</span> Not available to be claimed
                                    @else
                                    <span class="no-print">🟢</span> Available
                                    @endif
                                    @endif
                                    
                            </h2>
                            <h2>🙂 {{$box->user->name}} <small>(#{{ $box->user->id }})</small> </h2>
                        </div>
                        <div class="col-xs-6">
                            <span class="no-print">
                                <h4>Print this page to generate a label for this storage location</h4>
                            </span>
                            <h4>Scan to verify</h4>
                            <img src="{{ $QRcodeURL }}">
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    @if (Auth::user()->isAdmin() || Auth::user()->hasRole('storage'))
    <span class="no-print">
    <h3>Admin</h3>
    <div class="row">
            <div class="col-md-12 well" style="background:repeating-linear-gradient( 45deg, #fafafa, #fafafa 40px, #fff 40px, #fff 80px )">
                <div class="row">
                    <div class="col-md-6">
                        @if($box->user)
                            {!! Form::open(array('method'=>'PUT', 'route' => ['storage_boxes.update', $box->id], 'class'=>'navbar-left')) !!}
                            {!! Form::hidden('user_id', '') !!}
                            {!! Form::submit('Reclaim', array('class'=>'btn btn-default btn-sm')) !!}
                            {!! Form::close() !!}                        
                        @endif
                    </div>
                    <div class="col-md-6">
                        {!! Form::open(array('method'=>'PUT', 'route' => ['storage_boxes.update', $box->id])) !!}
                        {!! Form::select('user_id', [''=>'Allocate member']+$memberList, null, ['class'=>'form-control js-advanced-dropdown']) !!}
                        {!! Form::submit('✔️', array('class'=>'btn btn-default btn-xs')) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>    
        </div>
    </span>
    @endif
@stop
