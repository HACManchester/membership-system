@extends('layouts.main')

@section('page-title')
    Member Storage 
@stop

@section('content')

    <div class="well">
        <h1>Storage is changing: Add stickers to your items by 1st July 2025</h1>
        <ul>
            <li>Storage will no longer be managed online</li>
            <li>Items being stored must have a storage sticker on them, allowing them to be stored for a period of time.</li>
            <li>New stickers will be released every 3 months</li>
            <li>Items left with old stickers on them may be disposed of, as per our storage guidelines.</li>
        </ul>
        <p>
            The new storage system went into effect in January 2025, with a
            grace period until 1st July 2025.
        </p>
        <strong>Items left without stickers after 1st July 2025 will be disposed of.</strong>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="well">
                @if (!$memberBoxes->isEmpty())
                    <h3>You have the following member storage location</h3>
                    <p>Please make sure an obvious "Do Not Hack" label is on your box (preferably front and back) or each piece of loose material</p>
                    <ul>
                        @foreach($memberBoxes as $box)
                            <h3>
                                <form method="POST" action="{{ route('storage_boxes_claim.destroy', $box->id) }}" class="js-return-box-form">
                                    @csrf
                                    @method('DELETE')
                                    📦 Location {{ $box->location }}<small>(#{{ $box->id }})</small>
                                    <button type="submit" class="btn btn-default btn-link btn-sm">Return Storage Location</button>
                                </form>
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

    @can('canViewOld', BB\Entities\StorageBox::class)
        <div class="well">
            <h1>Previous storage claims</h1>
            <p>These will no longer be in effect after the July 2025 grace period deadline.</p>
            <p>This box and below is only visible to admins and storage managers.</p>
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
                                        <form method="POST" action="{{ route('storage_boxes_claim.update', $box->id) }}" class="navbar-left">
                                            @csrf
                                            <button type="submit" class="btn btn-default">Claim</button>
                                        </form>
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
    @endcan
@stop
