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
                            <b>Real name privacy:</b> {{ $user->suppress_real_name ? "Keep private" : "Share with others" }}
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
                        <form method="POST" action="{{ route('account.admin-update', $user->id) }}" class="form-horizontal">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <div class="col-sm-5">
                                @if ($user->trusted)
                                    <input type="hidden" name="trusted" value="0">
                                    <button type="submit" class="btn btn-default">Remove Trusted Status</button>
                                @else
                                    <input type="hidden" name="trusted" value="1">
                                    <button type="submit" class="btn btn-default">Make a trusted member</button>
                                @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="infobox__grid-item infobox__grid-item--main">
                        <h4>Physical Override Key</h4>
                        <p>
                            Does this user have a physical override key? <br/>
                            <br/>
                        </p>
                        <form method="POST" action="{{ route('account.admin-update', $user->id) }}" class="form-horizontal js-quick-update">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <div class="col-sm-8">
                                    <select name="key_holder" class="form-control">
                                        <option value="0" {{ $user->key_holder == 0 ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ $user->key_holder == 1 ? 'selected' : '' }}>Yes</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if ($user->profile->new_profile_photo)
                        <div class="infobox__grid-item infobox__grid-item--main">
                            <h4>New photo to approve</h4>
                            <p>If rejected they will be emailed explaining the photo wasn't suitable.</p>
                            <form method="POST" action="{{ route('account.admin-update', $user->id) }}" class="form-horizontal">
                                @csrf
                                @method('PUT')

                                <div class="form-group">
                                    <div class="col-sm-5">
                                        <img src="{{ \BB\Helpers\UserImage::newThumbnailUrl($user->hash) }}" width="100" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-5">
                                        <select name="photo_approved" class="form-control">
                                            <option value="0">Rejected</option>
                                            <option value="1" selected>Approved</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-3">
                                        <button type="submit" class="btn btn-default">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif

                    <div class="infobox__grid-item infobox__grid-item--main">
                        <h4>Key Fob</h4>
                        <p>This is the ID number associated with their RFID tag. They don't need to be a key holder to get an RFID tag.</p>
                        @foreach ($user->keyFobs()->get() as $fob)
                            <form method="POST" action="{{ route('keyfobs.destroy', [$user->id, $fob->id]) }}" class="form-horizontal">
                                @csrf
                                @method('DELETE')
                                <div class="form-group">
                                    <div class="col-sm-5">
                                        <p class="form-control-static">{{ $fob->key_id }} <small>(added {{ $fob->created_at->toFormattedDateString() }})</small></p>
                                    </div>
                                    <div class="col-sm-3">
                                        <button type="submit" class="btn btn-default">Mark Lost</button>
                                    </div>
                                </div>
                            </form>
                        @endforeach
                    </div>
                    
                    <div class="infobox__grid-item infobox__grid-item--main">
                        <h4>Balance - Top up</h4>
                        <p>Use this if the member has given you some cash to top up their balance.</p>

                        <form method="POST" action="{{ route('account.payment.cash.create', $user->id) }}" class="form-inline">
                            @csrf

                            <div class="form-group {{ $errors->credit->has('amount') ? 'has-error' : '' }}">
                                <label class="sr-only" for="amount">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-addon">&pound;</div>
                                    <input type="number" name="amount" value="" class="form-control" step="0.01" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Add Credit</button>

                            <div class="help-block">
                                <ul>
                                    @foreach ($errors->credit->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            <input type="hidden" name="reason" value="balance">
                            <input type="hidden" name="source_id" value="user:{{ \Auth::id() }}">
                            <input type="hidden" name="return_path" value="account/{{ $user->id }}">
                        </form>
                    </div>

                    <div class="infobox__grid-item infobox__grid-item--main">
                        <h4>Balance - Withdraw</h4>
                        <p>This will remove money from their balance, its used if your giving them cash.</p>

                        <form method="POST" action="{{ route('account.payment.cash.destroy', $user->id) }}" class="form-inline">
                            @csrf
                            @method('DELETE')

                            <div class="form-group {{ $errors->withdrawal->has('amount') ? 'has-error' : '' }}">
                                <label class="sr-only" for="amount">Amount</label>
                                <div class="input-group">
                                    <div class="input-group-addon">&pound;</div>
                                    <input type="number" name="amount" value="" class="form-control" step="0.01" required>
                                </div>
                            </div>

                            <div class="form-group {{ $errors->withdrawal->has('ref') ? 'has-error' : '' }}">
                                <label class="sr-only" for="ref">Reimbursemed via</label>
                                <select name="ref" class="form-control">
                                    <option value="cash">Cash</option>
                                    <option value="bank-transfer">Bank Transfer</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Remove Credit</button>

                            <div class="help-block">
                                <ul>
                                    @foreach ($errors->withdrawal->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            <input type="hidden" name="return_path" value="account/{{ $user->id }}">
                        </form>

                    </div>
                
                    @if ($newAddress)
                        <div class="infobox__grid-item infobox__grid-item--main">
                            <h4>Address Change</h4>
                            <p>Does this look like a real address?</p>
                            <form method="POST" action="{{ route('account.admin-update', $user->id) }}" class="form-horizontal">
                                @csrf
                                @method('PUT')

                                <div class="form-group">
                                    <div class="col-sm-5">
                                        {{ $newAddress->line_1 }}<br />
                                        {{ $newAddress->line_2 }}<br />
                                        {{ $newAddress->line_3 }}<br />
                                        {{ $newAddress->line_4 }}<br />
                                        {{ $newAddress->postcode }}
                                    </div>
                                    <div class="col-sm-3">
                                        <button type="submit" class="btn btn-default" name="approve_new_address">Approve</button>
                                        <button type="submit" class="btn btn-default" name="approve_new_address">Decline</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif

                    @if ($user->payment_method == 'cash')
                        <div class="infobox__grid-item infobox__grid-item--main">
                            <h4>Cash Membership</h4>
                            <form method="POST" action="{{ route('account.payment.store', $user->id) }}" class="form-horizontal">
                                @csrf
                                <button type="submit" class="btn btn-default">Record A &pound;{{ round($user->monthly_subscription) }} Cash Subscription Payment</button>
                                <input type="hidden" name="reason" value="subscription">
                                <input type="hidden" name="source" value="cash">
                            </form>
                            
                        </div>
                    @endif

                    @if ($user->status == 'setting-up' || $user->online_only)
                        <div class="infobox__grid-item infobox__grid-item--main alert-danger">
                            <h4>Delete</h4>
                            <p>Is this an old record? No sign of {{ $user->name }}?</p>
                            <form method="POST" action="{{ route('account.destroy', $user->id) }}" class="form-horizontal">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-default">Delete this member</button>
                            </form>
                        </div>
                    @endif

                    <div class="infobox__grid-item infobox__grid-item--main alert-danger">
                        <h4>Disciplinary actions</h4>
                        @if ($user->isBanned())
                            <p>User was banned on {{ $user->banned_date }} for the reason:</p>
                            <p style="padding-left: 2em">{{ nl2br($user->banned_reason) }}</p>
                            
                            <form method="POST" action="{{ route('disciplinary.unban', $user->id) }}" class="form-horizontal">
                                @csrf
                                <button type="submit" class="btn btn-default">Unban member</button>
                            </form>
                        @else
                            <div>
                                <h3>Ban member</h3>
                                <p>By banning a member, we will:</p>
                                <ul>
                                    <li>Immediately mark them as left on the system</li>
                                    <li>Stop them being able to access the members system</li>
                                </li>

                                <p>We will not send any automated emails to the member, you should do this yourself from the board email address.</p>

                                <form method="POST" action="{{ route('disciplinary.ban', $user->id) }}" class="form-horizontal">
                                    @csrf
                                    <label for="reason" class="control-label">Reason for the ban (255 characters)</label>
                                    <input type="text" name="reason" class="form-control">
                                    <button type="submit" class="btn btn-default">Ban member</button>
                                </form>
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
                                <form method="POST" action="{{ route('account.admin-update', $user->id) }}" class="form-horizontal">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="cancel_experimental_dd_subscription" value="true">
                                    <div class="form-group">
                                        <div class="col-sm-3">
                                            <button type="submit" class="btn btn-default">Cancel monthly payment</button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <form method="POST" action="{{ route('account.admin-update', $user->id) }}" class="form-horizontal">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="experimental_dd_subscription" value="true">
                                    <button type="submit" class="btn btn-default">Setup monthly payment</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endif
