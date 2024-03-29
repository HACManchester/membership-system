@if (Auth::user()->isAdmin())

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-danger">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a role="button" data-toggle="collapse" href="#collapseMemberAdminActions" aria-expanded="true" aria-controls="collapseMemberAdminActions">
                        Admin Actions (not visible to non-admins)
                    </a>
                </h3>
            </div>
            <div id="collapseMemberAdminActions" role="tabpanel">
                <div class="infobox infobox__grid">
                    <div class="infobox__grid-item infobox__grid-item--header">
                        <h4>Particulars</h4>
                        <li>
                            <b>Name:</b> {{ $user->given_name }} {{ $user->family_name }}
                        </li>
                        <li>
                            <b>Email:</b> {{ $user->email }} 
                            (
                                @if($user->email_verified)
                                    Email verified
                                @else
                                    Email NOT verified
                                @endif
                            )
                        </li>
                        <li><b>Pronouns:</b> {{ $user->pronouns ?: '(Not set)'}}</li>
                        <li>
                            <b>General induction done?:</b> {{ $user->induction_completed ? "Yes" : "No" }} 
                        </li>
                        <li>
                            <b>Last Seen:</b> {{ $user->seen_at }}
                        </li>
                    </div>
                
                    
                    <div class="infobox__grid-item infobox__grid-item--main">
                        <h4>Trusted Member</h4>
                        <p>The member will be automatically emailed about being made trusted but not if they are loosing trusted status.</p>
                        {!! Form::open(array('method'=>'PUT', 'route' => ['account.admin-update', $user->id], 'class'=>'form-horizontal')) !!}
                        <div class="form-group">
                            <div class="col-sm-5">
                            @if ($user->trusted)
                                {!! Form::hidden('trusted', 0) !!}
                                {!! Form::submit('Remove Trusted Status', array('class'=>'btn btn-default')) !!}
                            @else
                                {!! Form::hidden('trusted', 1) !!}
                                {!! Form::submit('Make a trusted member', array('class'=>'btn btn-default')) !!}
                            @endif
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>

                    <div class="infobox__grid-item infobox__grid-item--main">
                        <h4>Physical Override Key</h4>
                        <p>
                            Does this user have a physical override key? <br/>
                            <br/>
                        </p>
                        {!! Form::open(array('method'=>'PUT', 'route' => ['account.admin-update', $user->id], 'class'=>'form-horizontal js-quick-update')) !!}
                        <div class="form-group">
                            <div class="col-sm-8">
                                {!! Form::select('key_holder', ['0'=>'No', '1'=>'Yes'], $user->key_holder, ['class'=>'form-control']) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>

                    @if ($user->profile->new_profile_photo)
                        <div class="infobox__grid-item infobox__grid-item--main">
                            <h4>New photo to approve</h4>
                            <p>If rejected they will be emailed explaining the photo wasn't suitable.</p>
                            {!! Form::open(array('method'=>'PUT', 'route' => ['account.admin-update', $user->id], 'class'=>'form-horizontal')) !!}

                            <div class="form-group">
                                <div class="col-sm-5">
                                    <img src="{{ \BB\Helpers\UserImage::newThumbnailUrl($user->hash) }}" width="100" />
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-5">
                                    {!! Form::select('photo_approved', ['0'=>'Rejected', '1'=>'Approved'], 1, ['class'=>'form-control']) !!}
                                </div>
                                <div class="col-sm-3">
                                    {!! Form::submit('Update', array('class'=>'btn btn-default')) !!}
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    @endif

                    <div class="infobox__grid-item infobox__grid-item--main">
                        <h4>Key Fob</h4>
                        <p>This is the ID number associated with their RFID tag. They don't need to be a key holder to get an RFID tag.</p>
                        @foreach ($user->keyFobs()->get() as $fob)
                        {!! Form::open(array('method'=>'DELETE', 'route' => ['keyfobs.destroy', $user->id, $fob->id], 'class'=>'form-horizontal')) !!}
                            <div class="form-group">
                                <div class="col-sm-5">
                                    <p class="form-control-static">{{ $fob->key_id }} <small>(added {{ $fob->created_at->toFormattedDateString() }})</small></p>
                                </div>
                                <div class="col-sm-3">
                                    {!! Form::submit('Mark Lost', array('class'=>'btn btn-default')) !!}
                                </div>
                            </div>
                        {!! Form::hidden('user_id', $user->id) !!}
                        {!! Form::close() !!}
                        @endforeach
                    </div>
                    
                    <div class="infobox__grid-item infobox__grid-item--main">
                        <h4>Balance - Top up</h4>
                        <p>Use this if the member has given you some cash to top up their balance.</p>

                        {!! Form::open(['method'=>'POST', 'route' => ['account.payment.cash.create', $user->id], 'class'=>'form-inline']) !!}

                            <div class="form-group {{ $errors->credit->has('amount') ? 'has-error' : '' }}">
                                <label class="sr-only" for="amount">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-addon">&pound;</div>
                                    {!! Form::input('number', 'amount', '', ['class'=>'form-control', 'step'=>'0.01', 'required'=>'required']) !!}
                                </div>
                            </div>

                            {!! Form::submit('Add Credit', array('class'=>'btn btn-primary')) !!}

                            <div class="help-block">
                                <ul>
                                    @foreach ($errors->credit->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            {!! Form::hidden('reason', 'balance') !!}
                            {!! Form::hidden('source_id', 'user:' . \Auth::id()) !!}
                            {!! Form::hidden('return_path', 'account/'.$user->id) !!}
                        {!! Form::close() !!}
                    </div>

                    <div class="infobox__grid-item infobox__grid-item--main">
                        <h4>Balance - Withdraw</h4>
                        <p>This will remove money from their balance, its used if your giving them cash.</p>

                        {!! Form::open(['method'=>'DELETE', 'route' => ['account.payment.cash.destroy', $user->id], 'class'=>'form-inline']) !!}

                            <div class="form-group {{ $errors->withdrawal->has('amount') ? 'has-error' : '' }}">
                                <label class="sr-only" for="amount">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-addon">&pound;</div>
                                    {!! Form::input('number', 'amount', '', ['class'=>'form-control', 'step'=>'0.01', 'required'=>'required']) !!}
                                </div>
                            </div>

                            <div class="form-group {{ $errors->withdrawal->has('ref') ? 'has-error' : '' }}">
                                <label class="sr-only" for="ref">Reimbursemed via</label>
                                {!! Form::select('ref', ['cash'=>'Cash', 'bank-transfer'=>'Bank Transfer'], null, ['class'=>'form-control']) !!}
                            </div>
                            
                            {!! Form::submit('Remove Credit', array('class'=>'btn btn-primary')) !!}

                            <div class="help-block">
                                <ul>
                                    @foreach ($errors->withdrawal->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>

                        {!! Form::hidden('return_path', 'account/'.$user->id) !!}
                        {!! Form::close() !!}

                    </div>
                
                    @if ($newAddress)
                        <div class="infobox__grid-item infobox__grid-item--main">
                            <h4>Address Change</h4>
                            <p>Does this look like a real address?</p>
                            {!! Form::open(array('method'=>'PUT', 'route' => ['account.admin-update', $user->id], 'class'=>'form-horizontal')) !!}

                            <div class="form-group">
                                <div class="col-sm-5">
                                    {{ $newAddress->line_1 }}<br />
                                    {{ $newAddress->line_2 }}<br />
                                    {{ $newAddress->line_3 }}<br />
                                    {{ $newAddress->line_4 }}<br />
                                    {{ $newAddress->postcode }}
                                </div>
                                <div class="col-sm-3">
                                    {!! Form::submit('Approve', array('class'=>'btn btn-default', 'name'=>'approve_new_address')) !!}
                                    {!! Form::submit('Decline', array('class'=>'btn btn-default', 'name'=>'approve_new_address')) !!}
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    @endif

                    @if ($user->payment_method == 'cash')
                        <div class="infobox__grid-item infobox__grid-item--main">
                            <h4>Cash Membership</h4>
                            {!! Form::open(array('method'=>'POST', 'class'=>'form-horizontal', 'route' => ['account.payment.store', $user->id])) !!}
                            {!! Form::submit('Record A &pound;'.round($user->monthly_subscription).' Cash Subscription Payment', array('class'=>'btn btn-default')) !!}
                            {!! Form::hidden('reason', 'subscription') !!}
                            {!! Form::hidden('source', 'cash') !!}
                            {!! Form::close() !!}
                            
                        </div>
                    @endif

                    @if (in_array($user->status, ['setting-up', 'left', 'leaving']))
                        <div class="infobox__grid-item infobox__grid-item--main">
                            <h4>Setup</h4>
                            <p>Activate this members subscription but have them pay using their balance</p>
                            {!! Form::open(array('method'=>'POST', 'class'=>'form-horizontal', 'route' => ['account.update-sub-method', $user->id])) !!}
                            <div class="form-group">
                                <div class="col-sm-5">
                                    @if ($user->cash_balance > ($user->monthly_subscription * 100))
                                    {!! Form::hidden('payment_method', 'balance') !!}
                                    {!! Form::submit('Activate & pay by balance', array('class'=>'btn btn-default')) !!}
                                    @else
                                        <p>The user doesn't have enough money in their balance</p>
                                    @endif
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    @endif

                    @if ($user->status == 'setting-up' || $user->online_only)
                        <div class="infobox__grid-item infobox__grid-item--main alert-danger">
                            <h4>Delete</h4>
                            <p>Is this an old record? No sign of {{ $user->name }}?</p>
                            {!! Form::open(array('method'=>'DELETE', 'class'=>'form-horizontal', 'route' => ['account.destroy', $user->id])) !!}
                            {!! Form::submit('Delete this member', array('class'=>'btn btn-default')) !!}
                            {!! Form::close() !!}
                        </div>
                    @endif

                    <div class="infobox__grid-item infobox__grid-item--main alert-danger">
                        <h4>Disciplinary actions</h4>
                        @if ($user->isBanned())
                            <p>User was banned on {{ $user->banned_date }} for the reason:</p>
                            <p style="padding-left: 2em">{{ nl2br($user->banned_reason) }}</p>
                            
                            {!! Form::open(array('method'=>'POST', 'class'=>'form-horizontal', 'route' => ['disciplinary.unban', $user->id])) !!}
                                {!! Form::submit('Unban member', array('class'=>'btn btn-default')) !!}
                            {!! Form::close() !!}
                        @else
                            <div>
                                <h3>Ban member</h3>
                                <p>By banning a member, we will:</p>
                                <ul>
                                    <li>Immediately mark them as left on the system</li>
                                    <li>Cancel their GoCardless subscription (if they have one set up)</li>
                                    <li>Stop them being able to access the members system</li>
                                </li>
                                <p>We will not send any automated emails to the member, you should do this yourself from the board email address.</p>

                                {!! Form::open(array('method'=>'POST', 'class'=>'form-horizontal', 'route' => ['disciplinary.ban', $user->id])) !!}
                                    {!! Form::label('reason', 'Reason for the ban (255 characters)', array('class'=>'control-label')) !!}
                                    {!! Form::text('reason', null, array('class'=>'form-control')) !!}
                                    {!! Form::submit('Ban member', array('class'=>'btn btn-default')) !!}
                                {!! Form::close() !!}
                            </div>
                        @endif
                    </div>

                        
                    <div class="infobox__grid-item infobox__grid-item--footer">
                        <h4>Member subscription and DD info</h4>
                        <p>
                            <strong>Method</strong>:
                            @if ($user->payment_method == 'gocardless')
                                🕰️ Fixed Direct Debit - controlled via GoCardless
                            @elseif ($user->payment_method == 'gocardless-variable')
                                💳 Flexible Direct Debit - controlled via BBMS
                            @elseif ($user->payment_method == 'balance')
                                💵 Payments taken from the users balance. Backup: {{ $user->secondary_payment_method }}
                            @else
                                🏦 Other: {{ $user->payment_method }}
                            @endif
                        </p>
                        @if ($user->subscription_id)
                        <p>
                            <strong>GoCardless subscription ID:</strong> {{ $user->subscription_id }}
                        </p>
                        @endif
                        
                        @if ($user->mandate_id)
                            <p>
                                <strong>GoCardless mandate ID:</strong> {{ $user->mandate_id }}
                            </p>
                            <p>
                                <strong>Experimental DD subscription</strong><br>
                                This is a fixed DD subscription based on the users exiting mandate. It does not replace the normal monthly payment, it is for testing only.
                            </p>
                            @if ($user->subscription_id)
                                {!! Form::open(array('method'=>'PUT', 'route' => ['account.admin-update', $user->id], 'class'=>'form-horizontal')) !!}
                                    {!! Form::hidden('cancel_experimental_dd_subscription', true) !!}
                                    <div class="form-group">
                                        <div class="col-sm-3">
                                            {!! Form::submit('Cancel monthly payment', array('class'=>'btn btn-default')) !!}
                                        </div>
                                    </div>
                                {!! Form::close() !!}
                            @else
                                {!! Form::open(array('method'=>'PUT', 'route' => ['account.admin-update', $user->id], 'class'=>'form-horizontal')) !!}
                                    {!! Form::hidden('experimental_dd_subscription', true) !!}
                                    {!! Form::submit('Setup monthly payment', array('class'=>'btn btn-default')) !!}
                                {!! Form::close() !!}
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endif
