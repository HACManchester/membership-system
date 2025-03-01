<div class="member-status-bar">
    @if($user->online_only)
        <div>
            <span class="label label-warning">Online Only</span>
        </div>
    @else
        <div>
            @include('partials.components.status-label', ['status' => $user->status])
        </div>
    @endif
    
    <div>
        @include('partials.components.space-access-label', ['active' => $user->active])
    </div>

    @if (!$user->online_only)
        @if($user->payment_method)
            <div>
                💳 Subscription: {{ $user->present()->subscriptionDetailLine }}
                @if ($user->canMemberChangeSubAmount())
                    <button type="button" class="btn btn-link btn-xs" data-toggle="modal" data-target="#changeSubscriptionModel">
                        Change
                    </button>
                @endif
            </div>
        @endif
    
        <div>
            @if ($user->keyFob())
                
            <a href="{{ route('keyfobs.index', $user->id) }}">
                    🔑 {{ $user->keyFobs()->count() }} access {{ \Illuminate\Support\Str::plural('method', $user->keyFobs()->count() )}}
                </a>
            @else
                <a href="{{ route('keyfobs.index', $user->id) }}">
                    🔑 Set up an access method
                </a>
            @endif
        </div>
    @endif
</div>

@if($user->gift)
    <div class="alert alert-success">
        <p>🎁 Free gift period. 
            Expires {!! $user->subscription_expires->toFormattedDateString() !!} - set up payment to keep access from this date.
        </p>
    </div>
@endif