import React from "react";

var Select = require("./form/Select");
var Loader = require("halogen/PulseLoader");

class PaymentModule extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      amount: this.props.amount || '0.00',
      method: "gocardless",
      errorMessage: null,
      csrfToken: this.props.csrfToken,
      requestInProgress: false,
      desiredPaymentMethods: this.props.methods.split(","),
    };

    this.handleSubmit = this.handleSubmit.bind(this);
    this.handleAmountChange = this.handleAmountChange.bind(this);
    this.handleMethodChange = this.handleMethodChange.bind(this);

    this.availableMethods = [
      { key: "gocardless", value: "Direct Debit" },
      { key: "balance", value: "Balance" },
      { key: "cash", value: "Cash" },
    ];

    this.availablePaymentMethods = this.getPaymentMethodArray();
  }

  componentDidMount() {
    //Set the default payment method to be the first item in the array, the one the user sees
    this.setState({ method: this.availablePaymentMethods[0].key });
  }

  handleAmountChange(event) {
    //this doesn't allow decimal places to be entered by hand
    var amount = event.target.value;

    this.setState({ amount });
  }

  handleMethodChange(event) {
    var method = event.target.value;

    this.setState({ method });
  }

  handleSubmit() {
    var $ = require("jquery");

    var parsedAmount = parseFloat(this.state.amount);
    console.log(parsedAmount, this.state);

    if (parsedAmount < 0) {
      this.setState({ errorMessage: 'Amount cannot be negative'})
      return;
    }

    if (isNaN(parsedAmount)) {
      this.setState({ 
        errorMessage: 'Invalid amount. Please re-enter'
      })
      return;
    }

    this.setState({ errorMessage: null})
    this.setState({ requestInProgress: true });

    // loading indicator
    // https://madscript.com/halogen/

    $.ajax({
      url: this.getTargetUrl(this.props.userId, this.state.method),
      dataType: "json",
      contentType: "application/json",
      type: "POST",
      data: this.prepareRequestData(),
      success: function (responseData) {
        //Reset the state
        this.setState({
          requestInProgress: false,
          amount: 0
        });

        BB.SnackBar.displayMessage("Your payment has been processed");

        //run the passed in success function
        this.props.onSuccess();
      }.bind(this),
      error: function (xhr, status, err) {
        var responseData = JSON.parse(xhr.responseText);

        this.setState({ requestInProgress: false });

        if (xhr.status == 303) {
          document.location.href = responseData.url;
        }

        this.setState({ errorMessage: responseData.error})
      }.bind(this),
    });
  }

  /**
   * Generate data data for the ajax request
   * @returns string
   */
  prepareRequestData() {
    return JSON.stringify({
      amount: this.state.amount * 100 + "",
      reason: this.props.reason,
      ref: this.props.reference,
      _token: this.state.csrfToken,
    });
  }

  /**
   * Where will the request be sent?
   *
   * @param userId
   * @param method
   * @returns {string}
   */
  getTargetUrl(userId, method) {
    return "/account/" + userId + "/payment/" + method;
  }

  /**
   * Get an array of payment methods for the dropdown, this is controlled by the data being passed in
   *
   * @returns {Array}
   */
  getPaymentMethodArray() {
    var methods = [];
    for (var i in this.availableMethods) {
      if (
        this.state.desiredPaymentMethods.indexOf(
          this.availableMethods[i]["key"]
        ) !== -1
      ) {
        methods.push(this.availableMethods[i]);
      }
    }
    return methods;
  }

  render() {
    var amountField = null;

    if (!this.props.amount) {
      amountField = (
        <div className="form-group">
          <div className="input-group">
            <div className="input-group-addon">£</div>
            <input
              style={{ width: 70 }}
              className="form-control"
              step="0.01"
              required="required"
              type="number"
              value={this.state.amount}
              onChange={this.handleAmountChange}
            />
          </div>
        </div>
      );
    }

    return (
      <div className="form-inline multi-payment-form">
        {amountField}

        <Select
          value={this.state.method}
          onChange={this.handleMethodChange}
          options={this.availablePaymentMethods}
          style={{ width: 150 }}
        />

        <button
          className="btn btn-primary"
          disabled={this.state.requestInProgress}
          onClick={(x) => this.handleSubmit(x)}
        >
          {this.props.buttonLabel}
        </button>

        <div
          className={
            this.state.requestInProgress ? "has-feedback has-success" : "hidden"
          }
        >
          <p className="help-block">Please wait, processing...</p>
        </div>

        <div className="has-feedback has-error">
          <p
            className={
              this.state.errorMessage !== null ? "help-block" : "hidden"
            }
          >
            {this.state.errorMessage}
          </p>
        </div>
      </div>
    );
  }
}

PaymentModule.defaultProps = {
  name: "Hackspace Manchester",
  email: null,
  userId: null,
  amount: null,
  buttonLabel: "Pay Now",
  onSuccess: function () {},
  methods: "gocardless,cash,balance",
  reference: null,
  reason: null,
  description: null,
};

export default PaymentModule;
