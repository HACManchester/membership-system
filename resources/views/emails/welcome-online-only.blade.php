<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
</head>
<body>
<h1>Welcome to Hackspace Manchester Online Only 🎉</h1>
<p>Hi {{ $user['given_name'] }},<br /> Welcome to the online Hackspace Manchester!</p>
<hr/>
<h2>Next steps...</h2>
<h2>Confirm your email address</h2>
<p>Please click the link below to confirm your email address and ensure we have your accurate details.<br /><br /> <a href="{!! URL::route('account.confirm-email', [$user['id'], $user['hash']]) !!}">{!! URL::route('account.confirm-email', [$user['id'], $user['hash']]) !!}</a></p>

<hr/>

<h2>🤸 Manage your Membership</h2>
<p>You can manage your membership on our Management System where you signed up - which you can access using your email address and password you set when you joined. This can be accessed at <a href="https://members.hacman.org.uk">https://members.hacman.org.uk</a> and allows you to set up a direct debit if you'd like to become a full time member, top up your Hacman Balance for paying for things including the laser, printing and any fees related to equipment induction or usage. You can also use this system to claim your members storage shelf/cube.</p>
<h2>👋 Chat with us</h2>
We have two main channels of communication online, our forum and Telegram.
<h4>Our forum</h4>
<p>This is a good place for threads, asking questions, keeping to topic<br/>
<a href="https://list.hacman.org.uk/">https://list.hacman.org.uk</a></p>
<h4>Telegram group chat</h4>
<p>Fast paced and good for time critical questions or needing a quick answer. Download the app for notifications.<br/>
<a href="https://t.me/hacmanchester">https://t.me/hacmanchester</a></p>


<p>If you have any questions, please do not hesitate to ask the community on Telegram or the forum.</p>
<p>Many Thanks</p>
<p>Hackspace Manchester Board</p>
</body>
</html>
