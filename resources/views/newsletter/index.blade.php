@extends('layouts.main')

@section('meta-title')
Newsletter Recipients
@stop

@section('page-title')
Newsletter Recipients
@stop

@section('content')

<div>
    <strong>Select separator:</strong><br>
    <label><input type="radio" name="separator" value=" " checked> Space</label>
    <label><input type="radio" name="separator" value="\\n"> New Line</label>
    <label><input type="radio" name="separator" value=","> Comma</label>
</div>

<section>
    <h2>Newsletter recipients ({{$newsletterRecipients->count()}} members)</h2>

    <p>These e-mail address represent members who:</p>

    <ul>
        <li>Have not opted out from newsletter emails</li>
        <li>Are currently active members, or members who have lapsed within the last 6 months</li>
    </ul>

    <p>This list is intended to send regular news &amp; announcements relating to the space.</p>

    <pre><code id="newsletter-recipients" data-emails="{{ implode('|', $newsletterRecipients->pluck('email')->toArray()) }}">Loading...</code></pre>

    <button class="btn btn-primary" onclick="copyToClipboard(event, 'newsletter-recipients')">Copy to clipboard</button>
</section>

<section>
    <h2>Active members / legitimate interest purposes ({{$activeMembers->count()}} members)</h2>

    <p>These e-mail address represent currently active members, and should be emailed for urgent matters relating to membership of the space.</p>

    <pre><code id="active-members" data-emails="{{ implode('|', $activeMembers->pluck('email')->toArray()) }}">Loading...</code></pre>

    <button class="btn btn-primary" onclick="copyToClipboard(event, 'active-members')">Copy to clipboard</button>
</section>

<script>
    const copyTimers = {};

    function copyToClipboard(e, contentId) {
        e.preventDefault();

        const data = document.querySelector('#' + contentId)?.textContent;
        if (!data) {
            return;
        }

        const btn = e.currentTarget;
        btn.textContent = "Copied!";

        clearTimeout(copyTimers[contentId]);
        copyTimers[contentId] = setTimeout(function() {
            btn.textContent = "Copy to clipboard";
        }, 2000)

        navigator.clipboard.writeText(data);
    }
    function getSelectedSeparator() {
        const selected = document.querySelector('input[name="separator"]:checked');
        return selected?.value === '\\n' ? '\n' : selected?.value || ' ';
    }

    function updateEmails(contentId) {
        const el = document.getElementById(contentId);
        if (!el) return;
        const emails = el.dataset.emails?.split('|') || [];
        el.textContent = emails.join(getSelectedSeparator());
    }

    function updateAllEmails() {
        updateEmails('newsletter-recipients');
        updateEmails('active-members');
    }

    document.addEventListener('DOMContentLoaded', updateAllEmails);
</script>
@stop