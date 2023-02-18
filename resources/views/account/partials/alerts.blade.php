@if (($user->status != 'setting-up' || $user->online_only) && count($user->getAlerts()) > 0)
    <div class="alert alert-warning" role="alert">
        <ul>
            @foreach ($user->getAlerts() as $alert)
                @if ($alert == 'email-not-verified')
                    <li><strong>Your email isn't verified</strong>, please check your inbox for the welcome email and click the link. You won't be able to sign into online services with this login until you do this. <br/>Didn't get the email? <a href="{{ route('account.send-confirmation-email') }}">Click here to re-send it.</a></li>
                @endif
                @if ($alert == 'missing-profile-photo')
                    <li><strong>Missing profile photo</strong>, Please upload a profile picture - <a href="{{ route('account.profile.edit', [$user->id]) }}" class="alert-link">upload a photo</a></li>
                @endif
                @if ($alert == 'missing-phone')
                    <li><strong>No phone number</strong>, please enter a phone number - we need this in case we have to get in contact with you - <a href="{{ route('account.edit', [$user->id]) }}" class="alert-link">edit your profile</a></li>
                @endif
            @endforeach
        </ul>
    </div>
@endif