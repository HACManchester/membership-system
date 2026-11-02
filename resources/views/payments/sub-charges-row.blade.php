<tr>
    <td>{{ $charge->charge_date->toFormattedDateString() }}</td>
    <td>
        @if ($charge->user)
        <a href="{{ route('account.show', $charge->user->id) }}">{{ $charge->user->name }}</a>
        @else
        Unknown
        @endif
    </td>
    <td>{{ $charge->payment_date_label }}</td>
    <td>{{ $charge->status_label }}</td>
    <td>{{ $charge->amount_label }}</td>
    <td>
        @if ($charge->user)
        {{ $charge->user->payment_method_label }}
        @endif
    </td>
</tr>