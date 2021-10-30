<div class="row">
    <div class="col-xs-12 col-md-10">
        <div class="panel panel-default">
            <div class="panel-body">
                <h4>🚀 Want to power up your direct debit or change your subscription amount?</h4>
                <p>
                    If you want, you can move over to the new direct debit process where you authorise
                    Hackspace Manchester once and it applies to all the other payments.<br />
                    You can still cancel at any point and the same protections as before will apply.
                </p>
                <ul>
                    <li>Click the button below and fill in Direct Debit form</li>
                    <li>Return to the Membership System</li>
                    <li>Use the "change" link which will be next to your subscription amount (top right)</li>
                </ul>
                {!! Form::open(array('method'=>'POST', 'route' => ['account.payment.gocardless-migrate'])) !!}
                {!! Form::submit('Setup a variable Direct Debit', array('class'=>'btn btn-primary')) !!}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>