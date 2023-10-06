<li class="signposts-grid-item">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">💰 Payments & Credit</h3>
        </div>
        <div class="panel-body">
            <p><strong>Your account balance is {{$memberBalance}}</strong></p>

            <p>Visit the “Balance” page to credit your account or pay online for materials or equipment.</p>

            <div class="signpost-grid-item-buttons">
                <a class="btn btn-primary btn-block" href="{{ route('account.balance.index', [$user->id]) }}">
                    Manage Balance
                </a>
            </div>
        </div>
    </div>
</li>