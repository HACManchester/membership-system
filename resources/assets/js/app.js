import React from 'react';
import ReactDOM from 'react-dom';
import SiteInteraction from './SiteInteraction';
import AdminForms from './AdminForms';
import Snackbar from './Snackbar';
import PaymentModule from './components/PaymentModule';

global.jQuery = global.$ = require('jquery');
require('bootstrap');

new SiteInteraction();
new AdminForms();
new Snackbar();

jQuery('.paymentModule').each(function () {
    var reason = jQuery(this).data('reason');
    var displayReason = jQuery(this).data('displayReason');
    var buttonLabel = jQuery(this).data('buttonLabel');
    var methods = jQuery(this).data('methods');
    var amount = jQuery(this).data('amount');
    var ref = jQuery(this).data('ref');
    var memberEmail = document.getElementById('memberEmail').value;
    var userId = document.getElementById('userId').value;
    var csrfToken = document.getElementById('csrfToken').value;

    var handleSuccess = () => { document.location.reload(true) };

    ReactDOM.render(
        React.createElement(PaymentModule, {
            csrfToken: csrfToken,
            description: displayReason,
            reason: reason,
            amount: amount,
            email: memberEmail,
            userId: userId,
            onSuccess: handleSuccess,
            buttonLabel: buttonLabel,
            methods: methods,
            reference: ref
        }),
        jQuery(this)[0]
    );
});
