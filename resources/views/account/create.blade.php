@extends('layouts.main')

@section('meta-title')
Join Hackspace Manchester
@stop

@section('content')

<div class="register-container col-xs-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">

    <div class="row">
        <div class="col-xs-12">
            <div class="page-header">
                <h1>Join Hackspace Manchester</h1>
                <p>
                    Welcome! Hackspace Manchester is a fantastic space and community of like minded people.
                </p>
                <p>If you just want access to our online services such as the forum, please <a href="/online-only">sign up for online only access</a>.
            </div>
        </div>
    </div>

    @if($gift)
        <div class="row">
            <div class="col-xs-12">
                <div class="alert {!! $gift_valid ? 'alert-success' : 'alert-danger'!!}">
                    @if($gift_valid)
                        <h3>Gift Code Added!</h3>
                        <p>
                            Hey {{!! $gift_details['from'] !!}}, your gift from $NAME has been applied! Just register below and you'll
                            enjoy X months of membership for free and £Y of credit! 
                        </p>
                    @else
                        <h3>We couldn't find that gift code...</h3>
                        <p>
                            Hmmm, that code wasn't valid.<br/>
                            You can <a href="/gift">try again</a> or register below without the gift.
                        </p>
                    @endif
            </div>
            </div>
        </div>  
    @endif

    {!! Form::open(array('route' => 'account.store', 'class'=>'form-horizontal', 'files'=>true)) !!}

    {!! Form::hidden('online_only', '0') !!}

    @if($gift)
        {!! Form::hidden('gift_code', $gift_code) !!}
    @endif

    <div class="row">
        <div class="col-xs-12">
            <p>
                Please fill out the form below, on the next page you will be asked to setup a direct debit for the monthly payment.<br />
                <li>We need your real name and address, this is <a href="https://www.legislation.gov.uk/ukpga/2006/46/part/8/chapter/2/crossheading/general" target="_blank">required by UK law</a></li>
                <li>Your address will be kept private but your name will be listed publicly as being a member of our community</li>
            </p>
        </div>
    </div>

    @if (Notification::hasMessage())
    <div class="alert alert-{{ Notification::getLevel() }} alert-dismissable">
        {!! Notification::getMessage() !!}
    </div>
    @endif

    <h4>Basic Informaton</h4>
    <div class="form-group {{ Notification::hasErrorDetail('given_name', 'has-error has-feedback') }}">
        {!! Form::label('given_name', 'First Name', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('given_name', null, ['class'=>'form-control', 'autocomplete'=>'given-name', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('given_name') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('family_name', 'has-error has-feedback') }}">
        {!! Form::label('family_name', 'Surname', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('family_name', null, ['class'=>'form-control', 'autocomplete'=>'family-name', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('family_name') !!}
        </div>

    </div>
    <div class="form-group {{ Notification::hasErrorDetail('display_name', 'has-error has-feedback') }}">
        {!! Form::label('display_name', 'Username', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('display_name', null, ['class'=>'form-control', 'autocomplete'=>'display-name', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('display_name') !!}
        </div>
    </div>
    <div class="form-group {{ Notification::hasErrorDetail('announce_name', 'has-error has-feedback') }}">        
        {!! Form::label('announce_name', 'Announce Name', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('announce_name', null, ['class'=>'form-control', 'autocomplete'=>'announce-name', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('announce_name') !!}
        </div>
    </div>
    Note: announce name will be used to announce your entry into the Hackspace on our Hackscreen and Telegram Channel. <br>
    If you wish this to not be the case please set announce name to <i>anon</i>
    <br>
    <div class="form-group {{ Notification::hasErrorDetail('email', 'has-error has-feedback') }}">
        {!! Form::label('email', 'Email', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::input('email', 'email', null, ['class'=>'form-control', 'autocomplete'=>'email', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('email') !!}
        </div>
    </div>


    <div class="form-group {{ Notification::hasErrorDetail('password', 'has-error has-feedback') }}">
        {!! Form::label('password', 'Password', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::password('password', ['class'=>'form-control', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('password') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('monthly_subscription', 'has-error has-feedback') }}">
        {!! Form::label('monthly_subscription', 'Monthly Subscription Amount', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            <div class="input-group">
                <div class="input-group-addon">&pound;</div>
                {!! Form::input('number', 'monthly_subscription', 20, ['class'=>'form-control', 'placeholder'=>'20', 'min'=>'12', 'step'=>'1']) !!}
            </div>
            {!! Notification::getErrorDetail('monthly_subscription') !!}
            <span class="help-block"><button type="button" class="btn btn-link" data-toggle="modal" data-target="#howMuchShouldIPayModal">How much should I pay?</button></span>
        </div>
    </div>

    <h4>Contact Details</h4>
    <div class="form-group {{ Notification::hasErrorDetail('address.line_1', 'has-error has-feedback') }}">
        {!! Form::label('address[line_1]', 'Address Line 1', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('address[line_1]', null, ['class'=>'form-control', 'autocomplete'=>'address-line1', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('address.line_1') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('address.line_2', 'has-error has-feedback') }}">
        {!! Form::label('address[line_2]', 'Address Line 2', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('address[line_2]', null, ['class'=>'form-control', 'autocomplete'=>'address-line2']) !!}
            {!! Notification::getErrorDetail('address.line_2') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('address.line_3', 'has-error has-feedback') }}">
        {!! Form::label('address[line_3]', 'Address Line 3', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('address[line_3]', null, ['class'=>'form-control', 'autocomplete'=>'address-level2']) !!}
            {!! Notification::getErrorDetail('address.line_3') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('address.line_4', 'has-error has-feedback') }}">
        {!! Form::label('address[line_4]', 'Address Line 4', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('address[line_4]', null, ['class'=>'form-control', 'autocomplete'=>'address-level1']) !!}
            {!! Notification::getErrorDetail('address.line_4') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('address.postcode', 'has-error has-feedback') }}">
        {!! Form::label('address[postcode]', 'Post Code', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('address[postcode]', null, ['class'=>'form-control', 'autocomplete'=>'postal-code', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('address.postcode') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('phone', 'has-error has-feedback') }}">
        {!! Form::label('phone', 'Phone', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::input('tel', 'phone', null, ['class'=>'form-control', 'autocomplete'=>'tel', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('phone') !!}
        </div>
    </div>

    <div class="form-group {{ Notification::hasErrorDetail('emergency_contact', 'has-error has-feedback') }}">
        {!! Form::label('emergency_contact', 'Emergency Contact', ['class'=>'col-sm-3 control-label']) !!}
        <div class="col-sm-9 col-lg-7">
            {!! Form::text('emergency_contact', null, ['class'=>'form-control', 'required' => 'required']) !!}
            {!! Notification::getErrorDetail('emergency_contact') !!}
            <span class="help-block">Please give us the name and contact details of someone we can contact if needed.</span>
        </div>
    </div>


    <div class="form-group {{ Notification::hasErrorDetail('rules_agreed', 'has-error has-feedback') }}">
        <div class="col-xs-10 col-sm-8 well col-lg-8 col-xs-offset-1 col-sm-offset-3" style="background:rgba(255,255,0,0.2)">
            <h4>Getting your keyfob</h4>
            <b>How would you like to get your fob for 24/7 access?</b>
            <p>Your fob can be posted to you, or you can collect it from the space.<br/></p>
            <div class="radio">
                <label data-toggle="tooltip" title="Collect my fob from the space">
                    {!! Form::radio('postFob', false, true) !!}
                    Collect my fob from the space
                </label>
                <p style="color: darkblue;padding-left: 1.5em;">
                    You'll need to attend an open evening, or arrange with an existing member (on <a href="https://t.me/hacmanchester" target="_blank" >Telegram</a> or our <a href="https://list.hacman.org.uk" target="_blank">Forum</a>) to let you in so you can set up your fob.
                </p>
            </div>
            <div class="radio">
                <label data-toggle="tooltip" title="Have my fob posted to me">
                    {!! Form::radio('postFob', true, false) !!}
                    Have my fob posted to me
                </label>
                <p style="color: darkblue;padding-left: 1.5em;">
                    Your fob will be posted to the address above, after payment has been completed. It may take a few working days to arrive.
                </p>
            </div>
        </div>
    </div>


    <div class="form-group {{ Notification::hasErrorDetail('rules_agreed', 'has-error has-feedback') }}">
        <div class="col-xs-10 col-sm-8 well col-lg-8 col-xs-offset-1 col-sm-offset-3" style="background:rgba(255,0,0,0.05)">
            <h4>Rules</h4>
            <span class="help-block">Please read the <a href="https://members.hacman.org.uk/resources/policy/rules" target="_blank">rules</a> and click the checkbox to confirm you agree to them</span>
            {!! Form::checkbox('rules_agreed', true, null, ['class'=>'']) !!}
            {!! Form::label('rules_agreed', 'I agree to the Hackspace Manchester rules', ['class'=>'']) !!}
            {!! Notification::getErrorDetail('rules_agreed') !!}
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
            {!! Form::submit('Join Hackspace Manchester', array('class'=>'btn btn-primary')) !!}
        </div>
    </div>


    {!! Form::close() !!}

</div>

<div class="modal fade" id="howMuchShouldIPayModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Subscription Suggestions</h4>
            </div>
            <div class="modal-body">
                <p>If you're not sure how much to pay, here are some general guidelines to help you find a suitable subscription amount for your circumstances:</p>

                &pound;12.50 - &pound;15 a month:
                <ul>
                    <li>You are on a low income and unable to afford a higher amount.</li>
                </ul>

                &pound;15 - &pound;20 a month:
                <ul>
                    <li>You are planning to visit the makerspace regularly and are a professional / in full-time employment</li>
                </ul>

                &pound;25 a month and up:
                <ul>
                    <li>You are planning to visit the makerspace regularly and would like to provide a little extra support (thank you!)</li>
                </ul>

                <p>
                    If you feel that the makerspace is worth more to you then please do adjust your subscription accordingly.
                    You can also change your subscription amount at any time!
                </p>

                <p>
                    If you would like to pay less than &pound;12.50 a month please select an amount over £12.50 and complete
                    this form, on the next page you will be asked to setup a subscription payment.
                    Before you do this please send the board an email letting them know how much you would like to
                    pay, they will then override the amount so you can continue to setup a subscription.
                </p>
            </div>
        </div>
    </div>
</div>

@if ($confetti)
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    'use strict';

// If set to true, the user must press
// UP UP DOWN ODWN LEFT RIGHT LEFT RIGHT A B
// to trigger the confetti with a random color theme.
// Otherwise the confetti constantly falls.
var onlyOnKonami = false;

$(function() {
  // Globals
  var $window = $(window)
    , random = Math.random
    , cos = Math.cos
    , sin = Math.sin
    , PI = Math.PI
    , PI2 = PI * 2
    , timer = undefined
    , frame = undefined
    , confetti = [];
  
  var runFor = 3000
  var isRunning = true
  
  setTimeout(() => {
			isRunning = false
	}, runFor);

  // Settings
  var konami = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65]
    , pointer = 0;

  var particles = 550
    , spread = 20
    , sizeMin = 5
    , sizeMax = 12 - sizeMin
    , eccentricity = 10
    , deviation = 50
    , dxThetaMin = -.1
    , dxThetaMax = -dxThetaMin - dxThetaMin
    , dyMin = .10
    , dyMax = .15
    , dThetaMin = .2
    , dThetaMax = .5 - dThetaMin;

  var colorThemes = [
    function() {
      return color(200 * random()|0, 200 * random()|0, 200 * random()|0);
    }, function() {
      var black = 200 * random()|0; return color(200, black, black);
    }, function() {
      var black = 200 * random()|0; return color(black, 200, black);
    }, function() {
      var black = 200 * random()|0; return color(black, black, 200);
    }, function() {
      return color(200, 100, 200 * random()|0);
    }, function() {
      return color(200 * random()|0, 200, 200);
    }, function() {
      var black = 256 * random()|0; return color(black, black, black);
    }, function() {
      return colorThemes[random() < .5 ? 1 : 2]();
    }, function() {
      return colorThemes[random() < .5 ? 3 : 5]();
    }, function() {
      return colorThemes[random() < .5 ? 2 : 4]();
    }
  ];
  function color(r, g, b) {
    return 'rgb(' + r + ',' + g + ',' + b + ')';
  }

  // Cosine interpolation
  function interpolation(a, b, t) {
    return (1-cos(PI*t))/2 * (b-a) + a;
  }

  // Create a 1D Maximal Poisson Disc over [0, 1]
  var radius = 1/eccentricity, radius2 = radius+radius;
  function createPoisson() {
    // domain is the set of points which are still available to pick from
    // D = union{ [d_i, d_i+1] | i is even }
    var domain = [radius, 1-radius], measure = 1-radius2, spline = [0, 1];
    while (measure) {
      var dart = measure * random(), i, l, interval, a, b, c, d;

      // Find where dart lies
      for (i = 0, l = domain.length, measure = 0; i < l; i += 2) {
        a = domain[i], b = domain[i+1], interval = b-a;
        if (dart < measure+interval) {
          spline.push(dart += a-measure);
          break;
        }
        measure += interval;
      }
      c = dart-radius, d = dart+radius;

      // Update the domain
      for (i = domain.length-1; i > 0; i -= 2) {
        l = i-1, a = domain[l], b = domain[i];
        // c---d          c---d  Do nothing
        //   c-----d  c-----d    Move interior
        //   c--------------d    Delete interval
        //         c--d          Split interval
        //       a------b
        if (a >= c && a < d)
          if (b > d) domain[l] = d; // Move interior (Left case)
          else domain.splice(l, 2); // Delete interval
        else if (a < c && b > c)
          if (b <= d) domain[i] = c; // Move interior (Right case)
          else domain.splice(i, 0, c, d); // Split interval
      }

      // Re-measure the domain
      for (i = 0, l = domain.length, measure = 0; i < l; i += 2)
        measure += domain[i+1]-domain[i];
    }

    return spline.sort();
  }

  // Create the overarching container
  var container = document.createElement('div');
  container.style.position = 'fixed';
  container.style.top      = '0';
  container.style.left     = '0';
  container.style.width    = '100%';
  container.style.height   = '0';
  container.style.overflow = 'visible';
  container.style.zIndex   = '9999';

  // Confetto constructor
  function Confetto(theme) {
    this.frame = 0;
    this.outer = document.createElement('div');
    this.inner = document.createElement('div');
    this.outer.appendChild(this.inner);

    var outerStyle = this.outer.style, innerStyle = this.inner.style;
    outerStyle.position = 'absolute';
    outerStyle.width  = (sizeMin + sizeMax * random()) + 'px';
    outerStyle.height = (sizeMin + sizeMax * random()) + 'px';
    innerStyle.width  = '100%';
    innerStyle.height = '100%';
    innerStyle.backgroundColor = theme();

    outerStyle.perspective = '50px';
    outerStyle.transform = 'rotate(' + (360 * random()) + 'deg)';
    this.axis = 'rotate3D(' +
      cos(360 * random()) + ',' +
      cos(360 * random()) + ',0,';
    this.theta = 360 * random();
    this.dTheta = dThetaMin + dThetaMax * random();
    innerStyle.transform = this.axis + this.theta + 'deg)';

    this.x = $window.width() * random();
    this.y = -deviation;
    this.dx = sin(dxThetaMin + dxThetaMax * random());
    this.dy = dyMin + dyMax * random();
    outerStyle.left = this.x + 'px';
    outerStyle.top  = this.y + 'px';

    // Create the periodic spline
    this.splineX = createPoisson();
    this.splineY = [];
    for (var i = 1, l = this.splineX.length-1; i < l; ++i)
      this.splineY[i] = deviation * random();
    this.splineY[0] = this.splineY[l] = deviation * random();

    this.update = function(height, delta) {
      this.frame += delta;
      this.x += this.dx * delta;
      this.y += this.dy * delta;
      this.theta += this.dTheta * delta;

      // Compute spline and convert to polar
      var phi = this.frame % 7777 / 7777, i = 0, j = 1;
      while (phi >= this.splineX[j]) i = j++;
      var rho = interpolation(
        this.splineY[i],
        this.splineY[j],
        (phi-this.splineX[i]) / (this.splineX[j]-this.splineX[i])
      );
      phi *= PI2;

      outerStyle.left = this.x + rho * cos(phi) + 'px';
      outerStyle.top  = this.y + rho * sin(phi) + 'px';
      innerStyle.transform = this.axis + this.theta + 'deg)';
      return this.y > height+deviation;
    };
  }
     
    
  function poof() {
    if (!frame) {
      // Append the container
      document.body.appendChild(container);

      // Add confetti
      
      var theme = colorThemes[onlyOnKonami ? colorThemes.length * random()|0 : 0]
        , count = 0;
        
      (function addConfetto() {
  
        if (onlyOnKonami && ++count > particles)
          return timer = undefined;
        
        if (isRunning) {
          var confetto = new Confetto(theme);
          confetti.push(confetto);

          container.appendChild(confetto.outer);
          timer = setTimeout(addConfetto, spread * random());
         }
      })(0);
        

      // Start the loop
      var prev = undefined;
      requestAnimationFrame(function loop(timestamp) {
        var delta = prev ? timestamp - prev : 0;
        prev = timestamp;
        var height = $window.height();

        for (var i = confetti.length-1; i >= 0; --i) {
          if (confetti[i].update(height, delta)) {
            container.removeChild(confetti[i].outer);
            confetti.splice(i, 1);
          }
        }

        if (timer || confetti.length)
          return frame = requestAnimationFrame(loop);

        // Cleanup
        document.body.removeChild(container);
        frame = undefined;
      });
    }
  }
    
  $window.keydown(function(event) {
    pointer = konami[pointer] === event.which
      ? pointer+1
      : +(event.which === konami[0]);
    if (pointer === konami.length) {
      pointer = 0;
      poof();
    }
  });
  
  if (!onlyOnKonami) poof();
});

</script>
@endif
@stop
