@component('mail::message')
# Your membership payment has failed

Hi {{ $name }},

We've detected an issue with your latest subscription payment to Hackspace Manchester.

## What this means

- Your membership is still active for now
- You have approximately **10 days** to resolve the payment issue
- You currently retain access to the space and systems

## What might have happened

- Your direct debit payment failed (insufficient funds, cancelled mandate, etc.)
- There was a temporary banking issue

## What you need to do

### If you want to continue your membership:

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