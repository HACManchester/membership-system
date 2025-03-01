<form method="POST" href="" class="form-inline js-multiPaymentForm">
    @csrf
    <input type="hidden" name="reason" value="{{ $reason }}">
    <input type="hidden" name="display_reason" value="{{ $displayReason }}" class="js-paymentDescription">
    <input type="hidden" name="return_path" value="{{ $returnPath }}">
    <input type="hidden" name="ref" value="{{ isset($ref) ? $ref : null }}">

    @if ($amount != null)
        <input type="hidden" name="amount" value="{{ $amount }}" class="js-amount">
    @else
        <div class="form-group">
            <div class="input-group">
                <div class="input-group-addon">&pound;</div>
                <input type="number" name="amount" value="10.00" class="form-control js-amount" step="0.01" required>
            </div>
        </div>
    @endif
    <div class="form-group">
        <select name="source" class="form-control">
            @if(empty($methods) || in_array('gocardless', $methods))
                <option value="gocardless">Direct Debit</option>
            @endif
        </select>
    </div>
    <button type="submit" class="btn btn-primary">{{ $buttonLabel }}</button>
    <div class="has-feedback has-error">
        <span class="help-block"></span>
    </div>
</form>