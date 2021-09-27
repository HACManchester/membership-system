<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
</head>
<body>
<h1>Welcome to Hackspace Manchester 🎉</h1>
<p>Hi {{ $user['given_name'] }},<br /> Thanks for joining the Hackspace Manchester!</p>
<hr/>
<h2>Next steps...</h2>
<h2>Confirm your email address</h2>
<p>Please click the link below to confirm your email address and ensure we have your accurate details.<br /><br /> <a href="{!! URL::route('account.confirm-email', [$user['id'], $user['hash']]) !!}">{!! URL::route('account.confirm-email', [$user['id'], $user['hash']]) !!}</a></p>

<h2>Get your fob for 24/7 access</h2>
<p>Now you've set up a payment, it's time for you to get your fob so you can enjoy 24/7 access. When you've set your fob up, and your first payment has cleared, you'll have 24/7 access.<br/>
<b>If you selected to collect your fob:</b>
Visit the space - either show up to an open evening (no need to book!) or arrange to be let in by an existing member. Once there, you'll find the registration desk by the entrance, with fobs and a getting started guide that explains what you need to do.
<br/><br/>
<b>If you selected to have your fob posted:</b>
We will shortly post you a fob to the address you registered with. You'll receive a welcome leaflet with some guidance on how to set up your fob. 
<br/>
<hr/>

<h2>🦠 Covid-19 Process Changes</h2>
<p>Since reopening after Lockdown we have implemented a number of changes to the space to make it CovID Secure please make sure you check the covid alert level on the following link <a href="https://www.hacman.org.uk/covid-19-information/">https://www.hacman.org.uk/covid-19-information/</a></p>
<p>Please also note face masks are mandatory in the space.&nbsp;</p>
<p>If open evenings aren't happening, you may want to connect with the community online. More on that below!</p>
<h2>💳 Membership Payment</h2>
<p>You have successfully signed up and have successfully create a direct debit via Gocardless - our payment processor for direct debit. Please note that payments will be to Manchester Makers Ltd which is the legal name of Hackspace Manchester.</p>
<h2>🤸 Manage your Membership</h2>
<p>You can manage your membership on our Management System where you signed up - which you can access using your email address and password you set when you joined. This can be accessed at <a href="https://members.hacman.org.uk">https://members.hacman.org.uk</a> and allows you to amend your direct debit, top up your Hacman Balance for paying for things including the laser, snackspace, printing and any fees related to equipment induction or usage. You can also use this system to claim your members storage shelf/cube.</p>
<h2>👋 Chat with us</h2>
We have two main channels of communication online, our forum and Telegram.
<h4>Our forum</h4>
<p>This is a good place for threads, asking questions, keeping to topic<br/>
<a href="https://list.hacman.org.uk/">https://list.hacman.org.uk</a></p>
<h4>Telegram group chat</h4>
<p>Fast paced and good for time critical questions or needing a quick answer. Download the app for notifications.<br/>
<a href="https://t.me/hacmanchester">https://t.me/hacmanchester</a></p>
<h4>Member Meetings</h4>
<p>We have various meetings called as and when needed by members - but the most regular is the Members Meeting, called every month to two months, usually the first Monday of the month at 19:30. You'll find announcements of this <a href="https://list.hacman.org.uk/c/announcements/23">in the announcements section of the forum</a></p>
<h2>🛠️ Equipment use</h2>
<p>Certain equipment requires an induction so you don't hurt yourself or damage the machines for example: the Laser Cutter, 3D printers, sewing machines, lathes, and any equipment in the workshop with a "This is Bloody Dangerous" tag. Our volunteer inductors organise these to fit around their schedules, some are ad-hoc and some are at set times and dates. If you'd like to use one of these machines, ask on telegram or the forum and someone will point you in the right direction.</p>
<p>You can signup for an induction for most of the items via the Members Portal at <a href="https://members.hacman.org.uk">https://members.hacman.org.uk</a> once you have done this you just need to contact the relevant team either via the helpdesk, telegram or email to arrange a suitable date/time. <strong>Please Note that inductions are done by volunteers in their free time so please be patient</strong>.</p>
<h2>Consumables</h2>
<p>We keep a small supply of regularly used things in the space (the full list is here: http://wiki.hacman.org.uk/Consumables), if you cant find something do the following:</p>
<ol>
<li>ask someone in the space if they know where it is - if the answer is "we've run out" GOTO 3</li>
<li>ask on telegram (https://hacman.org.uk/telegram)</li>
<li>still no joy finding it? Contact the procurement team via the helpdesk at <a href="https://help.hacman.org.uk">https://help.hacman.org.uk</a> or if you need it urgently (and it's on the list) go buy some then email the receipt to procurement@hacman.org.uk</li>
</ol>
<p>If you have any questions, please do not hesitate to ask the community on Telegram or the forum.</p>
<p>Many Thanks</p>
<p>Hackspace Manchester Board</p>
</body>
</html>
