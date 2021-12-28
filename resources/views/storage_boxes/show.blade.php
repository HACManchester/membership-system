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
}
</style>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading">
                    Storage Box Information
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6 print-border">
                            <h1>📦 Location {{ $box->location }}</h2>
                            <h2>#️⃣ ID {{ $box->id }}</h2>
                            <h2>
                                @if($box->user)
                                    @if($box->user->active)
                                        <span class="no-print">🟡</span> Claimed by `{{$box->user->name}}`
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
                            @if($box->user && $box->user->active)
                                <h4>
                                    `{{$box->user->name}}` can be found in the membership system under
                                    this username or ID {{ $box->user->id }}
                                </h4>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <span class="no-print">
                                <h4>Print this page to generate a label for this storage location</h4>
                            </span>
                            <h4>Scan this label to varify the latest information on this storage location</h4>
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
