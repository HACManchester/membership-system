@component('mail::message')
# Your membership has been suspended

Hi {{ $name }},

Your Hackspace Manchester membership has been **suspended** due to unresolved payment issues.

## What this means

- Your access has been deactivated - Key fobs, door codes, and system access are now disabled
- All membership benefits have ended - You're no longer able to use the space or equipment

## Why this happened

Your subscription payment failed and 2 weeks has passed from when the payment became due. This typically occurs when:
- Direct debit payments continue to fail
- Payment retry attempts were unsuccessful
- No alternative payment was made

## How to recover your membership

1. Log into your member dashboard
2. Set up a new subscription payment
3. Your membership will be automatically reactivated

@component('mail::button', ['url' => URL::route('home')])
Access Member Dashboard
@endcomponent

## If you're leaving

We'd love to understand your experience and how we can do better.

- Complete our [anonymous exit survey](https://forms.gle/5okny6T3yW3Cq8Zm6)
- Email us directly at [board@hacman.org.uk](mailto:board@hacman.org.uk)

Your insights help us create a better hackspace for future members.

## Questions or issues?

If you believe this was sent in error or have questions about your departure, please contact us at [board@hacman.org.uk](mailto:board@hacman.org.uk).

@endcomponent