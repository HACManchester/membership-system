<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
</head>
<body>
<h1>Welcome to Hackspace Manchester</h1>
<p>Hi {{ $user['given_name'] }},<br /> Thanks for joining the Hackspace Manchester maker space.</p>
<h2>Covid-19 Process Changes</h2>
<p>Since reopening after Lockdown we have implemented a number of changes to the space to make it CovID Secure please make sure you check the covid alert level on the following link below and make sure that you book equipment usage if required.</p>
<p>Please also note facemasks are mandatory in the space.&nbsp;</p>
<p>As we are currently not running opening evenings or social events getting access first time is a little bit more trickly than normal. Please&nbsp;use our forum at&nbsp;<a href="https://list.hacman.org.uk/">https://list.hacman.org.uk</a>&nbsp;or telegram group at&nbsp;<a href="https://t.me/hacmanchester">https://t.me/hacmanchester</a>&nbsp;to arrange to meet someone at the space and obtain and register a fob.&nbsp;</p>
<h2>Confirm your email address</h2>
<p>Finally, please click the link below to confirm your email address and ensure we have your accurate details.<br /><br /> <a href="{!! URL::route('account.confirm-email', [$user['id'], $user['hash']]) !!}">{!! URL::route('account.confirm-email', [$user['id'], $user['hash']]) !!}</a></p>
<h2>Membership Payment</h2>
<p>You have successfully signed up and have successfully create a direct debit via Gocardless - our payment processor for direct debit. Please note that payments will be to Manchester Makers Ltd which is the legal name of Hackspace Manchester.</p>
<h2>Manage your Membership</h2>
<p>We have recently introduced a new Membership Management System which you can access using your email address and password you set when you joined. This can be accessed at <a href="https://members.hacman.org.uk">https://members.hacman.org.uk</a> and allows you to amend your direct debit, top up your Hacman Balance for paying for things including the laser, snackspace, printing and any fees related to equipment induction or usage. You can also use this system to claim your members storage shelf/cube.</p>
<h2>Hackspace Helpdesk</h2>
<p>If you wish to contact the board or any of the teams that manage different areas of the space please use our Hackspace Helpdesk at <a href="https://help.hacman.org.uk">https://help.hacman.org.uk</a> alternatively you can email <a href="mailto:helpdesk@hacman.org.uk">helpdesk@hacman.org.uk</a></p>
<h2>Chat with us</h2>
<p>Communities work best when we talk to each other and we're a talkative bunch. We have an extremely active Telegram Group ( https://hacman.org.uk/telegram ) and forum ( list.hacman.org.uk ). Telegram is a mobile/desktop app that allows easy real-time text chat for groups which we use for social chatter while our forum is more focused on discussion of projects, group orders and announcements regarding the space itself.</p>
<p>Everyone is also welcome at the Members Meeting, these take place at 19:30 on the 1st Monday of every 2nd Month (Dates can be found at <a href="https://members.hacman.org.uk/resources">https://members.hacman.org.uk/resources</a> where most of the decisions regarding the running of the space are discussed/agreed upon.</p>
<h2>Access</h2>
<p>If possible we like to organise for a member to meet you when you first come to the space, give you a quick tour and help you set up RFID access and storage space (if you need it). You can do this before your first payment arrives if you'd like, but your RFID tag will only become active once the payment is confirmed.</p>
<h2>Equipment use</h2>
<p>Certain equipment requires an induction so you don't hurt yourself or damage the machines for example: the Laser Cutter, 3D printers, sewing machines, lathe, and any equipment in the workshop with a "This is Bloody Dangerous" tag. Our volunteer inductors organise these to fit around their schedules, some are ad-hoc and some are at set times and dates. If you'd like to use one of these machines, ask on telegram or the forum and someone will point you in the right direction.</p>
<p>You can signup for an induction for most of the items via the Members Portal at <a href="https://members.hacman.org.uk">https://members.hacman.org.uk</a> once you have done this you just need to contact the relevant team either via the helpdesk, telegram or email to arrange a suitable date/time. <strong>Please Note that inductions are done by volunteers in their free time so please be patient</strong>.</p>
<h2>Consumables</h2>
<p>We keep a small supply of regularly used things in the space (the full list is here: http://wiki.hacman.org.uk/Consumables), if you cant find something do the following:</p>
<ol>
<li>ask someone in the space if they know where it is - if the answer is "we've run out" GOTO 3</li>
<li>ask on telegram (https://hacman.org.uk/telegram)</li>
<li>still no joy finding it? Contact the procurement team via the helpdesk at <a href="https://help.hacman.org.uk">https://help.hacman.org.uk</a> or if you need it urgently (and it's on the list) go buy some then email the receipt to procurement@hacman.org.uk</li>
</ol>
<p>If you have any questions, please do not hesitate to contact us via the helpdesk, email or ask everyone via Telegram or the forum.</p>
<p>Many Thanks</p>
<p>Hackspace Manchester Board</p>
</body>
</html>
