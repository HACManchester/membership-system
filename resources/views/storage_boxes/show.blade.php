@extends('layouts.main')

@section('page-title')
    <a href="{{ route('storage_boxes.index') }}">Member Storage</a> - Location {{ $box->location }}
@endsection

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

            .col-sm-6 {
                width: initial;
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
                        <div class="col-sm-6 print-border">
                            <h2>📦 {{ $box->location }} <small>(#{{ $box->id }})</small></h2>
                            <h2>
                                @if($box->isClaimed())
                                    @if($box->user->active)
                                        <span class="no-print">🟡</span> Claimed
                                    @else
                                        <span class="no-print">⚠️</span> Member left
                                    @endif
                                @else
                                    <span class="no-print">🟢</span> Available
                                @endif
                                    
                            </h2>
                            @if($box->isClaimed())
                                <a href="{{ route('members.show', $box->user) }}">
                                    <h2>🙂 {{$box->user->name}} <small>(#{{ $box->user->id }})</small> </h2>
                                </a>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <span class="no-print">
                                <h4>Print this page to generate a label for this storage location</h4>
                            </span>
                            <h4>Scan to verify</h4>
                            <img src="{{ $QRcodeURL }}" />
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
                        @if($box->isClaimed())
                            @can('update', $box)
                                <div class="col-md-12">
                                    <h4>Reclaim this space</h4>
                                    <form method="POST" action="{{ route('storage_boxes.update', $box->id) }}" class="navbar-left">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="user_id" value="">
                                        <button type="submit" class="btn btn-default btn-sm">Reclaim</button>
                                    </form>
                                </div>
                            @endcan
                        @else
                            @can('update', $box)
                                <div class="col-md-12">
                                    <h4>Allocate this space to a user</h4>
                                    <form method="POST" action="{{ route('storage_boxes.update', $box->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <select name="user_id" class="form-control js-advanced-dropdown">
                                            <option value="">Allocate member</option>
                                            @foreach($memberList as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-default btn-xs">✔️</button>
                                    </form>
                                </div>
                            @endcan
                        @endif
                    </div>
                </div>    
            </div>

            <div class="row">
                <div class="col-md-12 well">
                    <h3>Audit Logs</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>User</th>
                                <th>Date</th>
                                <th>Field changed</th>
                                <th>Old value</th>
                                <th>New value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($box->audits as $audit)
                                @foreach($audit->getModified() as $key => ['old' => $old, 'new' => $new])
                                    <tr>
                                        <td>{{ $audit->event }}</td>
                                        <td>{{ $audit->user->name }}</td>
                                        <td>{{ $audit->created_at }}</td>
                                        <td>{{ $key }}</td>
                                            @if($key == 'user_id')
                                                <td>
                                                    @if($old)
                                                        <a href="{{ route('account.show', $old) }}">
                                                            {{ $oldUser = BB\Entities\User::find($old)->name }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">(blank)</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($new)
                                                        <a href="{{ route('account.show', $new) }}">
                                                            {{ $newUser = BB\Entities\User::find($new)->name }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted">(blank)</span>
                                                    @endif
                                                </td>
                                            @else
                                                <td>{{ $old }}</td>
                                                <td>{{ $new }}</td>
                                            @endif
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>            
        </span>
    @endif
@stop
