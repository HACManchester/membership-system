
<div id="js-message-holder"></div>

@if (FlashNotification::hasMessage())
    <input type="hidden" id="snackbarMessage" value="{{ FlashNotification::getMessage() }}" />
    <input type="hidden" id="snackbarLevel" value="{{ FlashNotification::getLevel() }}" />
    @if (FlashNotification::hasDetails())
        <input type="hidden" id="snackbarMessages" value="{!! htmlentities(json_encode(FlashNotification::getDetails()->all())) !!}" />
    @endif
@endif