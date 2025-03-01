<tr>
    <td class="profilePhotoCol">
        @include('partials.components.member-photo', ['profileData' => $user->profile, 'userHash' => $user->hash, 'size' => 100, 'class' => 'img-circle profilePhoto'])
    </td>
    <td>
        <a href="{{ route('account.show', $user->id) }}">{{ $user->name }}</a>
        @if ($user->hasRole('admin'))
        <span class="label label-danger">Admin</span>
        @endif
        <br />
        {{ $user->email }}
    </td>
    <td>
        @include('partials.components.status-label', ['status' => $user->status])
        @if ($user->online_only)
            <div class="label label-warning">Online Only</div>
        @endif
        @if ($user->profile->new_profile_photo)
            <br /><span class="label label-info">Photo to approve</span>
        @endif
    </td>
    <td class="hidden-xs">
        @if($user->key_holder)
            <i class="material-icons" title="Key Holder">vpn_key</i>
        @endif
        @if($user->keyFobs()->count() < 1) 
            💁‍♂️ Fob being collected
        @else
            🔑 ({{ $user->keyFobs()->count() }})
        @endif
    </td>
    <td class="hidden-xs">
        @if ($user->trusted)
            <i class="material-icons" title="Trusted">verified_user</i>
        @endif
    </td>
    <td class="hidden-xs">
        {{ $user->present()->paymentMethod }}<br />
        <span style="color:red;">Expires: {{ $user->present()->subscriptionExpiryDate }}</span><br />
        <span style="color:green">Created: {{ $user->created_at }}</span><br/>
        <span style="color:blue">Seen: {{ $user->seen_at }}</span>
    </td>
</tr>