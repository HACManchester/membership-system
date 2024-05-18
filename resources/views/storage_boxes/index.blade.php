@extends('layouts.main')

@section('page-title')
    Member Storage 
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="well">
                @if (!$memberBoxes->isEmpty())
                    <h3>You have the following member storage location</h3>
                    <p>Please make sure an obvious "Do Not Hack" label is on your box (preferably front and back) or each piece of loose material</p>
                    <ul>
                        @foreach($memberBoxes as $box)
                            <h3 class="">
                                {!! Form::open(array('method'=>'DELETE', 'route' => ['storage_boxes_claim.destroy', $box->id], 'class'=>'js-return-box-form')) !!}
                                📦 Location {{ $box->location }}<small>(#{{ $box->id }})</small>
                                {!! Form::submit('Return Storage Location', array('class'=>'btn btn-default btn-link btn-sm')) !!}
                                {!! Form::close() !!}
                            </h3>
                        @endforeach
                    </ul>
                    
                    <p>
                        If your no longer using a space and want to return it, please remove your items,
                        return the box if it was found in the space, and then use the return link above.
                    </p>
                @else
                    <h3>You have not claimed a storage location</h3>
                    <p>To do so, find a storage location in the space that is vacant, note the location ID, and then claim it in the list below.</p>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            @can('create', BB\Entities\StorageBox::class)
                <div class="well">
                    <h3>Admin Actions</h3>
                    <ul>
                        <li><a href="{{ route('storage_boxes.create') }}">Create Storage Box</a></li>
                    </ul>
                </div>
            @endcan
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Location</th>
                <th>Member</th>
                <th>Message</th>
                @if (Auth::user()->hasRole('storage'))
                <th>Admin</th>
                @endif
            </tr>
        </thead>
        @foreach ($storageBoxes as $box)
            <tbody>
                <tr 
                    @if($box->user && !$box->user->active)class="warning"@elseif(!$box->user)class="success"@endif
                >
                    <td><a href="{{ route('storage_boxes.show', $box) }}">{{ $box->id }}</a></td>
                    <td>{{ $box->location }}</td>
                    <td>
                        @if($box->isClaimed())
                            <a href="{{ route('members.show', $box->user) }}">
                                {{ $box->user->name }}
                            </a>
                        @else
                            Available
                        @endif
                    </td>
                    <td>
                        @if($box->isClaimed() && !$box->user->active)
                            ⚠️ Member left
                        @elseif (!$box->isClaimed())
                            @if (Auth::user()->online_only)
                                ⛔ Not available to be claimed
                            @else
                                @can('claim', $box)
                                    {!! Form::open(array('method'=>'POST', 'route' => ['storage_boxes_claim.update', $box->id], 'class'=>'navbar-left')) !!}
                                    {!! Form::submit('Claim', array('class'=>'btn btn-default')) !!}
                                    {!! Form::close() !!}
                                @endcan
                            @endif
                        @endif
                    </td>
                    @if (Auth::user()->isAdmin() || Auth::user()->hasRole('storage'))
                        <td><a href="{{ route('storage_boxes.show', $box) }}">Admin</a></td>
                    @endif
                </tr>
            </tbody>
        @endforeach
    </table>
@stop
