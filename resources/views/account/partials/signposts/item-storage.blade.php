<li class="signposts-grid-item">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">📦 Storage space</h3>
        </div>
        <div class="panel-body">
            <p><strong>Your storage space is: {{ $user->storageBoxes()->count() ? $user->storageBoxes()->first()->location : '(none claimed)' }}</strong></p>

            <p>To claim a storage space, or return your space for somebody else to use, visit the Member&apos;s Storage page.</p>

            <div class="signpost-grid-item-buttons">
                <a class="btn btn-primary btn-block" href="{{ route('storage_boxes.index') }}">
                    Manage Storage
                </a>
            </div>
        </div>
    </div>
</li>