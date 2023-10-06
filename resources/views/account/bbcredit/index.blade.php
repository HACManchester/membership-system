@extends('layouts.main')

@section('meta-title')
Hackspace Manchester Balance {{ $user->name }}
@stop

@section('page-title')
Hackspace Manchester Balance
@stop

@section('content')

<div class="row">
    <div class="col-md-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><strong>Your Balance</strong></h3>
            </div>
            <div class="panel-body">
                <p>Your Current Balance is:</p>
                <div>        
                    <span class="credit-figure" style="{{ $userBalanceSign == 'positive' ? 'color:#219724' : 'color:#D9534F' }}">{{ $userBalance }}</span>
                </div> 
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-body">

                <h4>
                    Manage your Hackspace Balance, you can use it for paying for laser time, paying for materials that you use in the space, or paying for replacement access fobs.
                </h4>
                <br>
                <p>
                    You can top up either by direct debit, or by putting cash in the pot in the space.
                </p>
            </div>
        </div>
    </div>
</div>

<h3>💰 Add Credit</h3>
<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Top up with card or cash</h3>
            </div>
            <div class="panel-body">
                <p>Top Up Using Cash Topup or Direct Debit - select which you'd like from the dropdown.</p> <br>
                <p>Note: if you don't have a direct debit set up you should pay with cash</p>
                <div class="paymentModule" data-reason="balance" data-display-reason="Balance Payment" data-button-label="Add Credit" data-methods="gocardless,cash2"></div>
                <br>
                <p>
                    
                </p>
            </div>
        </div>
    </div>
</div>

<h3>💸 Log Expenditure</h3>
<div class="row">
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Materials</h3>
            </div>
            <div class="panel-body">
                <p>Log purchases of materials such as laser materials, 3D printing filament, etc. </p>
                <hr/><p>Laser materials</p>
                <div class="paymentModule" data-reason="Laser Materials" data-display-reason="Materials" data-button-label="Buy Now" data-methods="balance" data-ref="laser-materials"></div>
                <hr/><p>3D printer filament</p>
                <div class="paymentModule" data-reason="3D Printer Filament" data-display-reason="Materials" data-button-label="Buy Now" data-methods="balance" data-ref="3d-printer-filament"></div>
                <hr/><p>Heat Press Items</p>
                <div class="paymentModule" data-reason="Heat Press Items" data-display-reason="Materials" data-button-label="Buy Now" data-methods="balance" data-ref="heat-press-items"></div>
                <hr/><p>Other material purchases</p>
                <div class="paymentModule" data-reason="Misc Materials" data-display-reason="Materials" data-button-label="Buy Now" data-methods="balance" data-ref="misc-materials"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Fob Purchase</h3>
            </div>
            <div class="panel-body">
                <p>Only do this if you need another fob - the first one is on the house</p>
                <p>Suggested donation is £2</p>
                <div class="paymentModule" data-reason="Fob" data-display-reason="Usage Fee" data-button-label="Buy Now" data-methods="balance" data-ref="fob"></div>
            </div>
        </div>
    </div>
</div>

<h3>📒 Balance History</h3>
<div class="row">
    <div class="col-xs-12">
    <div class="panel panel-default">
    <div class="panel-body">
                <h3 class="panel-title">Balance Payment History</h3>
            </div>
            <table class="table">
                <thead>
                <tr>
                <th class="not_mapped_style" style="text-align:left">Reason</th>
                    <th class="not_mapped_style" style="text-align:left">Method</th>
                    <th class="not_mapped_style" style="text-align:left">Date</th>
                    <th class="not_mapped_style" style="text-align:left">Amount</th>
                    <th class="not_mapped_style" style="text-align:left">Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($payments as $payment)
                <tr class="{{ $payment->present()->balanceRowClass }}">
                    <td >{{ $payment->present()->reason }}</td>
                    <td>{{ $payment->present()->method }}</td>
                    <td>{{ $payment->present()->date }}</td>
                    <td>{{ $payment->present()->balanceAmount }}</td>
                    <td>{{ $payment->present()->status }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            <div class="panel-footer">
            {!! $payments->render() !!}
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