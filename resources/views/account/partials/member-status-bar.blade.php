<div class="row">
    <div class="col-xs-12 col-sm-8" style="background: rgba(255,255,255,0.3);border-color:white;border-width: 1px 0px;border-radius:3px;">
        <ul class="nav nav-pills">

            @if($user->online_only)
                <li>
                    <span class="label label-warning">Online Only</span>
                </li>
            @else
                <li>
                    <p class="navbar-text">{!! HTML::statusLabel($user->status) !!}</p>
                </li>
            @endif
            
            <li>
                <p class="navbar-text">{!! HTML::spaceAccessLabel($user->active) !!}</p>
            </li>

            @if ($user->keyFob())
            <li>
                <p class="navbar-text"><label class="label label-default">Key Fob ID: {{ $user->keyFob()->key_id }}</label></p>
            </li>
            @endif
        </ul>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="memberSubAmount clearfix">
            @if (!$user->online_only)
                <div class="navbar-text">
                    Balance: {{ $memberBalance }} <br />
                    {{ $user->present()->subscriptionDetailLine }}
                    @if ($user->canMemberChangeSubAmount())
                        <small><a href="#" class="js-show-alter-subscription-amount" title="Change Amount">Change</a></small>
                    @endif
                </div>
                @if ($user->canMemberChangeSubAmount())
                    <div class="hidden js-alter-subscription-amount-form clearfix">
                        {!! Form::open(array('method'=>'POST', 'class'=>'', 'style'=>'margin-bottom:20px;', 'route' => ['account.update-sub-payment', $user->id])) !!}
                        <div class="form-group">
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <div class="input-group-addon">&pound;</div>
                                    {!! Form::text('monthly_subscription', round($user->monthly_subscription), ['class'=>'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                {!! Form::submit('Update', array('class'=>'btn btn-default')) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}

                        @if (Auth::user()->isAdmin())

                            @if ($user->payment_method == 'gocardless-variable')
                                {!! Form::open(array('method'=>'POST', 'class'=>'', 'style'=>'margin-bottom:20px;', 'route' => ['account.update-sub-method', $user->id])) !!}
                                    {!! Form::hidden('payment_method', 'balance') !!}
                                    {!! Form::submit('Change to balance payment', array('class'=>'btn btn-default')) !!}
                                    <p>This will try and take their monthly subscription from the members balance</p>
                                {!! Form::close() !!}
                            @endif

                            @if ($user->payment_method == 'balance')
                                {!! Form::open(array('method'=>'POST', 'class'=>'', 'style'=>'margin-bottom:20px;', 'route' => ['account.update-sub-method', $user->id])) !!}
                                {!! Form::hidden('payment_method', 'gocardless-variable') !!}
                                {!! Form::submit('Change to DD payment', array('class'=>'btn btn-default')) !!}
                                <p>This switches back to a variable DD or resets the payment method if one doesn't exist</p>
                                {!! Form::close() !!}
                            @endif

                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
