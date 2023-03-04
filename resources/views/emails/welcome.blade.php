<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
</head>
<body>
<h1>Welcome to Hackspace Manchester 🎉</h1>
<p>Hi {{ $user['given_name'] }},<br /> Thanks for joining Manchester Hackspace, your local community makerspace. </p>
<hr/>
<h2>Next steps...</h2>
<h2>Confirm your email address</h2>
<p>Please click the link below to confirm your email address and ensure we have your accurate details.<br /><br /> <a href="{!! URL::route('account.confirm-email', [$user['id'], $user['hash']]) !!}">{!! URL::route('account.confirm-email', [$user['id'], $user['hash']]) !!}</a></p>

<h2>Get your fob for 24/7 access</h2>
<p>Once your first payment has cleared, you will be able to set up a keyfob so you can enjoy 24/7 access. To collect your fob, you need to visit the space for a tour/general induction. Check our website for information about visiting: <a href="https://www.hacman.org.uk/visit-us/">hacman.org.uk/visit-us/</a>
<br><br/>
If you can't make Wednesday evenings you can arrange with an existing member another time to attend for a tour. You will need to do this via our telegram group (<a href="https://t.me/hacmanchester">t.me/hacmanchester</a>) or forum <a href="https://list.hacman.org.uk/">(list.hacman.org.uk/)</a>.
<br/><br/>
<hr/>

<h2>💳 Membership Payment</h2>
<p>Please note that payments will be taken monthly with reference Manchester Makers Ltd which is the legal name of Hackspace Manchester.</p>
<h2>🤸 Manage your Membership</h2>
<p>You can manage your membership on our Members Portal - which you can access using your email address and password you set when you joined. This can be accessed at <a href="https://members.hacman.org.uk"> members.hacman.org.uk</a> and allows you to amend your direct debit and top up your Hacman Balance for paying for any fees related to equipment induction or usage. <br></br>
You can also use this system if you need to claim short term project storage shelf/cube.</p>

<h2>👋 Chat with us</h2>
We have two main channels of communication online, our forum and Telegram.<br></br>
<b>Our Forum</b> <br>
This is a great place for discussions, finding ways to help out, asking big questions, showing off what you've been making, etc. It works with your existing login. <br>
<a href="https://list.hacman.org.uk">list.hacman.org.uk</a><br></br>
<b>Telegram Group Chat</b><br>
Fast paced and good for time critical questions or needing a quick answer. Download the app for notifications.<br>
<a href="https://t.me/hacmanchester">t.me/hacmanchester</a><br></br>
<b>In-person Events</b><br>
We have various meetings throughout the year - you'll find these announced on the forum and telegram. The three main ones are:<br>
<b>Hack-the-Space Day</b> - a day where we come together to improve the space and eat pizza.<br>
<b>Members Meetings</b> - Usually a weekday evening to discuss some of the important topics going on in the forum.<br>
<b>AGM</b> - Usually around September each year. Overview of our finances, progress over the past year and voting in new board members.

<h2>🛠️ Equipment use</h2>
<p>Certain equipment requires an induction so you don't hurt yourself or damage the machines for example: the Laser Cutter, 3D printers, sewing machines, lathes, etc. Please check signage around the machine. A good general rule to go by is that if it's particularly dangerous or complex, it's probably induction protected.<br></br>

The Tools & Equipment (<a href="https://members.hacman.org.uk/equipment">members.hacman.org.uk/equipment)</a> page on the Members Portal also notes which pieces of equipment require inductions.</p>
<p><strong>Inductions are run by volunteer trainers, and may not be available immediately,</strong> sometimes it may take a couple of months to arrange. You can signup for an induction via the equipment pages on the Members Portal.<br></br>

Look out for any instructions in the “Induction Next Steps” section on equipment pages. Some teams ask for you to contact them either via telegram or the forum to arrange a suitable date/time for training, whereas others have regular training slots each week.
</p>
<b>We're always on the hunt for more members to run training sessions, if you're interested please let the team know.</b>
<p>You're welcome to get going with all our other tools immediately. Please note, tools and equipment should not be borrowed to be taken out of the space.</p>
<h3>What if something breaks or I get injured?</h3>
<p>Hackspace gets a heck of a lot of use, even if it seems quiet when you visit. <strong>Things do break, and that's ok!</strong> We have the budget to fix/replace things, the most important thing is to let us know.<br></br>
There's QR codes all around the space for reporting broken things via a <a href="https://forms.gle/CqLzjv4svNqDARwW8">Google Form.</a> Unless there's a sign on something saying it's broken, it's likely no one has reported it. Please put a sign on the equipment you've reported.

The same goes for reporting injuries and near misses. First aid kits are located by the entrance.
</p>
<h3>What if something runs out?</h3>
<p>We keep a small supply of regularly used things in the space for members to use on a fair-use basis. (the full list is here: <a href="https://docs.hacman.org.uk/Operations/Consumables/">docs.hacman.org.uk/Operations/Consumables/</a>), if you think something is low on stock or has run out:<br>
<ul>
    <li>Ask the community if we have any of the item either in person or online.</li>
    <li>Still no joy finding it? check it's on the consumables list and that general members can purchase it, then either buy some and <a href="https://forms.gle/QdBMwtkExtk8JZuU9">expense the space</a>, or report it via the <a href="https://forms.gle/CqLzjv4svNqDARwW8">report form.</a>.</li>
</ul>
</p>
    
<h3>The space should buy an X/Y/Z</h3>
<p>We are often looking at what new tools and equipment we can replace/buy. If you have a good idea, speak to one of the teams around the space and they can explain how our purchasing process works and how you can lead the setup of a new tool.</p>



<h2>How can I help out around the space?</h2>
<p>The space has zero staff. Everything is done by members and we encourage everyone to help out how they can. We have teams that run each area, and lots of smaller ongoing projects that are always looking for people to take them on. Head to the forum if you'd like to get involved with these, or email <a href="mailto:info@hacman.org.uk">info@hacman.org.uk</a> and we can put you in touch with the teams you’re interested in.</p>

<p>If you have any questions, please do not hesitate to ask the community on Telegram or the forum. If you have any membership issues you can contact the board via <a href="mailto:board@hacman.org.uk">board@hacman.org.uk</a> </p>

Thanks for reading all that, and we look forward to seeing you soon!
<br></br>
<b>Manchester Hackspace Board of Directors a.k.a The Board</b><br></br>
<hr></hr>
<a href="https://hacman.org.uk">hacman.org.uk</a>
<p>Manchester Hackspace is a not for profit community run maker space on the edge of Ancoats, Central Manchester. Anyone is welcome to become a member and make use of the space and its wide variety of tools.<br>
Woodwork. Metalwork. Arts. Crafts. 3D Printing. Laser Cutting. Electronics.
<br></br>

Wellington House, Pollard St E, Manchester M40 7FS<br>
<i><small>Hackspace Manchester and HacMan are trading names of Manchester Makers Ltd. Company No. 08012547</small></i>
</p>

