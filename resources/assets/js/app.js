var React = require('react');
global.jQuery = global.$ = require('jquery');
require('bootstrap-datepicker');
require('bootstrap');

var SiteInteraction = require('./SiteInteraction');
new SiteInteraction();

//var PaymentForm = require('./PaymentForm');
//new PaymentForm();

var AdminForms = require('./AdminForms');
new AdminForms();

var Snackbar = require('./Snackbar');
new Snackbar();

if (jQuery('body').hasClass('payment-page')) {
    var FilterablePaymentTable = require('./components/FilterablePaymentTable');
    React.render(<FilterablePaymentTable />, document.getElementById('react-test'));
}

var PaymentModule = require('./components/PaymentModule');
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

    React.render(<PaymentModule csrfToken={csrfToken} description={displayReason} reason={reason} amount={amount} email={memberEmail} userId={userId} onSuccess={handleSuccess} buttonLabel={buttonLabel} methods={methods} reference={ref} />, jQuery(this)[0]);
});
