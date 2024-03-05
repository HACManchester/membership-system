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

            {!! Form::open(array('method'=>'DELETE', 'route' => ['account.subscription.destroy', $user->id, 1])) !!}
            {!! Form::submit('Cancel Your Monthly Direct Debit', array('class'=>'btn btn-danger')) !!}
            {!! Form::close() !!}

        @elseif ($user->payment_method == 'gocardless-variable')

            {!! Form::open(array('method'=>'DELETE', 'route' => ['account.subscription.destroy', $user->id, 1])) !!}
            {!! Form::submit('Cancel Your Direct Debit and Leave', array('class'=>'btn btn-danger')) !!}
            {!! Form::close() !!}

        @else

            {!! Form::open(array('method'=>'DELETE', 'route' => ['account.destroy', $user->id])) !!}
            {!! Form::submit('Leave Hackspace Manchester :(', array('class'=>'btn btn-danger')) !!}
            {!! Form::close() !!}

        @endif
    </div>
</div>
