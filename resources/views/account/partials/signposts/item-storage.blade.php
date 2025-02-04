<li class="signposts-grid-item">
    <div class="panel {{ $user->storageBoxes()->count() ? 'panel-danger' : 'panel-warning' }}">
        <div class="panel-heading">
            <h3 class="panel-title">📦 Storage space</h3>
        </div>
        <div class="panel-body">
            @if ($user->storageBoxes()->count() > 0)
                <div class="alert alert-danger">
                    <strong>Storage is changing (action required)</strong>
                    <p>Please add a sticker to any items you've stored before 1st July 2025, or your items may be disposed of.</p>
                    <p>See signage in the Hackspace storage area for full details.</p>
                </div>
                <p><strong>Your storage space is: {{ $user->storageBoxes()->count() ? $user->storageBoxes()->first()->location : '(none claimed)' }}</strong></p>

                <p>To relinquish your claimed storage location, visit the Member&apos;s Storage page.</p>

                <div class="signpost-grid-item-buttons">
                    <a class="btn btn-primary btn-block" href="{{ route('storage_boxes.index') }}">
                        Manage Storage
                    </a>
                </div>
            @else
                <p>
                    As of January 2025, storage is managed physically with time-limited stickers.
                    See signage in the Hackspace storage area for full details.
                </p>
            @endif
        </div>
    </div>
</li>