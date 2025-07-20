@component('mail::message')
# You're leaving Hackspace Manchester

Hi {{ $name }},

We've received your request to leave Hackspace Manchester.

## What this means

- Your membership will end at the conclusion of your current payment cycle
- You'll retain access until your membership expires
- Your subscription payments will stop after the current period

## Help us improve

We'd love to understand your experience and how we can do better.

- Complete our [anonymous exit survey](https://forms.gle/5okny6T3yW3Cq8Zm6)
- Email us directly at [board@hacman.org.uk](mailto:board@hacman.org.uk)

Your insights help us create a better hackspace for future members.

## Changed your mind?

If you change your mind, just:

1. Log into your member dashboard
2. Set up a new subscription payment
3. Your membership will be automatically reactivated

@component('mail::button', ['url' => URL::route('home')])
Access Member Dashboard
@endcomponent

## Questions or issues?

If you believe this was sent in error or have questions about your departure, please contact us at [board@hacman.org.uk](mailto:board@hacman.org.uk).


@endcomponent