<tr>
    <td>{{ $payment->created_at->toFormattedDateString() }}</td>
    <td>
        @if ($payment->user)
        <a href="{{ route('account.show', $payment->user->id) }}">{{ $payment->user->name }}</a>
        @else
            Unknown
        @endif
    </td>
    <td>{{ $payment->reason_label }}</td>
    <td>{{ $payment->method_label }}</td>
    <td>£{{ number_format($payment->amount, 2) }}</td>
    <td>{{ $payment->reference }}</td>
    <td>{{ $payment->status_label }}</td>
    <td>
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                Action <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                @if (($payment->source == 'cash') && ($payment->reason == 'balance'))
                    <li>
                    <form method="POST" action="{{ route('payments.destroy', $payment->id) }}" class="navbar-form navbar-left">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-link">Delete</button>
                    </form>
                    </li>
                @endif
                @if (!$payment->user)
                    <li>
                        <form method="POST" action="{{ route('payments.update', $payment->id) }}" class="navbar-form navbar-left">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="change" value="assign-unknown-to-user">
                            <input type="number" name="user_id" placeholder="User ID" class="form-control">
                            <button type="submit" class="btn">Assign to user</button>
                        </form>
                    </li>
                @endif
                @if ($payment->source == 'gocardless-variable' && $payment->status === \BB\Support\PaymentStatus::Pending)
                    <li>
                        <form method="POST" action="{{ route('payment.gocardless.cancel', $payment) }}" class="navbar-form navbar-left">
                            @csrf
                            <input type="hidden" name="cancel" value="confirm-gocardless-variable">
                            <button type="submit" class="btn btn-link">Cancel payment</button>
                        </form>
                    </li>
                @endif
            </ul>
        </div>
    </td>
</tr>