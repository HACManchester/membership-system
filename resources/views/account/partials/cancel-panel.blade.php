<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Thinking of leaving?</h3>
    </div>
    <div class="panel-body">
        <p class="well">
            <b>Did something go wrong?</b><br/>
            If you're thinking of leaving, please send us a message and we will see what we can do. We would like to make it right if we can!<br />
            <a class="btn btn-success" href="mailto:board@hacman.org.uk">Email the Board</a>
        </p>
        @if ($user->payment_method == 'gocardless')

            <form method="POST" action="{{ route('account.subscription.destroy', [$user->id, 1]) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Cancel Your Monthly Direct Debit</button>
            </form>

        @elseif ($user->payment_method == 'gocardless-variable')

            <form method="POST" action="{{ route('account.subscription.destroy', [$user->id, 1]) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Cancel Your Direct Debit and Leave</button>
            </form>

        @else

            <form method="POST" action="{{ route('account.destroy', $user->id) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Leave Hackspace Manchester</button>
            </form>

        @endif
    </div>
</div>
