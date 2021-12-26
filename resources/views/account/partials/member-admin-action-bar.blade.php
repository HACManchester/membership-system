@if (Auth::user()->isAdmin())

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3 class="panel-title">Admin Actions (not visible to non-admins)</h3>
                </div>
                <div class="panel-body" style="repeating-linear-gradient( 45deg, #fee, #fee 20px, #fffafa 20px, #fffafa 60px )">
                    <div class="row"> <!-- Start Row -->

                        <!-- Panel -->
                        <div class="col-md-6" style="border-left: 3px solid green;">
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

                        <!-- Panel -->
                        <div class="col-md-6" style="border-left: 3px solid green;">
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

                        <!-- Panel -->
                        @if ($user->profile->new_profile_photo)
                            <div class="col-md-6" style="border-left: 3px solid green;">
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

                        <!-- Panel -->
                        <div class="col-md-6" style="border-left: 3px solid orange;">
                            <h4>Key Fob</h4>
                            <p>This is the ID number associated with their RFID tag. They don't need to be a key holder to get an RFID tag.</p>
                            @foreach ($user->keyFobs()->get() as $fob)
                            {!! Form::open(array('method'=>'DELETE', 'route' => ['keyfob.destroy', $fob->id], 'class'=>'form-horizontal')) !!}
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

                            @if ($user->keyFobs()->count() < 2)
                                {!! Form::open(array('method'=>'POST', 'route' => ['keyfob.store'], 'class'=>'form-horizontal')) !!}
                                <div class="form-group">
                                    <div class="col-sm-5">
                                        {!! Form::text('key_id', '', ['class'=>'form-control']) !!}
                                    </div>
                                    <div class="col-sm-3">
                                        {!! Form::submit('Add a new fob', array('class'=>'btn btn-default')) !!}
                                    </div>
                                </div>
                                {!! Form::hidden('user_id', $user->id) !!}
                                {!! Form::close() !!}
                            @endif

                        </div>
                    
                        <!-- Panel -->
                        <div class="col-md-6"  style="border-left: 3px solid blue;">
                            <h4>Balance - Top up</h4>
                            <p>Use this if the member has given you some cash to top up their balance.</p>

                            {!! Form::open(['method'=>'POST', 'route' => ['account.payment.cash.create', $user->id], 'class'=>'form-horizontal']) !!}

                            <div class="form-group">
                                <div class="col-sm-5">
                                    <div class="input-group">
                                        <div class="input-group-addon">&pound;</div>
                                        {!! Form::input('number', 'amount', '', ['class'=>'form-control', 'step'=>'0.01', 'required'=>'required']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    {!! Form::submit('Add Credit', array('class'=>'btn btn-primary')) !!}
                                </div>
                            </div>

                            {!! Form::hidden('reason', 'balance') !!}
                            {!! Form::hidden('source_id', 'user:' . \Auth::id()) !!}
                            {!! Form::hidden('return_path', 'account/'.$user->id) !!}
                            {!! Form::close() !!}
                        </div>

                        <!-- Panel -->
                        <div class="col-md-6" style="border-left: 3px solid red;">
                            <h4>Balance - Withdraw</h4>
                            <p>This will remove money from their balance, its used if your giving them cash.</p>

                            {!! Form::open(['method'=>'DELETE', 'route' => ['account.payment.cash.destroy', $user->id], 'class'=>'form-horizontal']) !!}

                            <div class="form-group">
                                <div class="col-sm-3">
                                    <div class="input-group">
                                        <div class="input-group-addon">&pound;</div>
                                        {!! Form::input('number', 'amount', '', ['class'=>'form-control', 'step'=>'0.01', 'required'=>'required']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    {!! Form::select('ref', ['cash'=>'Cash', 'bank-transfer'=>'Bank Transfer'], null, ['class'=>'form-control']) !!}
                                </div>
                                <div class="col-sm-3">
                                    {!! Form::submit('Remove Credit', array('class'=>'btn btn-primary')) !!}
                                </div>
                            </div>

                            {!! Form::hidden('return_path', 'account/'.$user->id) !!}
                            {!! Form::close() !!}

                        </div>
                
                        <!-- Panel -->
                        @if ($newAddress)
                            <div class="col-md-6" style="border-left: 3px solid green;">
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

                        <!-- Panel -->
                        @if ($user->payment_method == 'cash')
                            <div class="col-md-6"  style="border-left: 3px solid green;">
                                <h4>Cash Membership</h4>
                                {!! Form::open(array('method'=>'POST', 'class'=>'form-horizontal', 'route' => ['account.payment.store', $user->id])) !!}

                                <div class="form-group">
                                    <div class="col-sm-5"></div>
                                    <div class="col-sm-3">
                                        {!! Form::submit('Record A &pound;'.round($user->monthly_subscription).' Cash Subscription Payment', array('class'=>'btn btn-default')) !!}
                                    </div>
                                </div>

                                {!! Form::hidden('reason', 'subscription') !!}
                                {!! Form::hidden('source', 'cash') !!}
                                {!! Form::close() !!}
                                
                            </div>
                        @endif

                        <!-- Panel -->
                        @if (in_array($user->status, ['setting-up', 'left', 'leaving']))
                            <div class="col-md-6" style="border-left: 3px solid red;">
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

                        <!-- Panel -->
                        @if ($user->status == 'setting-up' || $user->online_only)
                            <div class="col-md-6 alert-danger" style="border-left: 3px solid red;">
                                <h4>Delete</h4>
                                <p>Is this an old record? No sign of {{ $user->name }}?</p>
                                {!! Form::open(array('method'=>'DELETE', 'class'=>'form-horizontal', 'route' => ['account.destroy', $user->id])) !!}
                                <div class="form-group">
                                    <div class="col-sm-5">
                                        {!! Form::submit('Delete this member', array('class'=>'btn btn-default')) !!}
                                    </div>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        @endif

                        
                        <!-- Panel -->
                        <div class="col-xs-12" style="border-left: 3px solid black;">
                            <h4>Member subscription and DD info</h4>
                            <p>
                                <strong>Method</strong>:
                                @if ($user->payment_method == 'gocardless')
                                    🕰️ Fixed Direct Debit - controlled via GoCardless
                                @elseif ($user->payment_method == 'gocardless-variable')
                                    💳 Flexible Direct Debit - controlled via BBMS
                                @elseif ($user->payment_method == 'balance')
                                    💵 Payments taken from the users balance. Backup: {{ $user->secondary_payment_method }}
                                @elseif ($user->payment_method == 'paypal')
                                    🏧 PayPal subscription - managed entirely through PayPal
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
                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                {!! Form::submit('Setup monthly payment', array('class'=>'btn btn-default')) !!}
                                            </div>
                                        </div>
                                    {!! Form::close() !!}
                                @endif
                            @endif
                        </div>
                        
                    </div> <!-- End Row -->
                </div>
            </div>

        </div>
    </div>
</div>

@endif
