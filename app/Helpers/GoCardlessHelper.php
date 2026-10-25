<?php

namespace BB\Helpers;

use Illuminate\Support\Facades\Log;

class GoCardlessHelper
{

    /**
     * @var \GoCardlessPro\Client
     */
    private $client;

    public function __construct()
    {
        $this->setup();
    }

    public function setup()
    {
        $this->client = new \GoCardlessPro\Client([
            'access_token' => config('services.gocardless.access_token'),
            'environment' => (config('services.gocardless.environment') == 'LIVE') ? \GoCardlessPro\Environment::LIVE : \GoCardlessPro\Environment::SANDBOX,
        ]);
    }

    public function getPayment($paymentId)
    {
        return $this->client->payments()->get($this->requireId($paymentId, 'payment'));
    }

    public function cancelPayment($paymentId)
    {
        return $this->client->payments()->cancel($this->requireId($paymentId, 'payment'));
    }

    public function newPreAuthUrl($user, $paymentDetails)
    {
        $redirectFlow = $this->client->redirectFlows()->create([
            "params" => $paymentDetails
        ]);


        $user->gocardless_setup_id = $redirectFlow->id;
        $user->save();

        return $redirectFlow->redirect_url;
    }

    public function confirmResource($user, $confirm_params)
    {
        return $this->client->redirectFlows()->complete(
            $this->requireId($user->gocardless_setup_id, 'redirect flow'),

            ["params" => ["session_token" => 'user-token-' . $user->id]]
        );
    }


    public function createSubscription($mandate, $amount, $dayOfMonth, $subscriptionNumber)
    {
        $subscription = $this->client->subscriptions()->create([
            "params" => [
                "amount"        => $amount, // GBP in pence
                "currency"      => "GBP",
                "interval_unit" => "monthly",
                "day_of_month"  => $dayOfMonth,
                "links"         => [
                    "mandate" => $mandate
                ],
                "metadata"      => [
                    "subscription_number" => $subscriptionNumber
                ]
            ],
        ]);

        return $subscription;
    }
    public function cancelSubscription($id)
    {
        return $this->client->subscriptions()->cancel($this->requireId($id, 'subscription'));
    }

    /**
     * An empty ID would otherwise reach the SDK, which turns it into a request
     * for the wrong URL (4.x) or its own URL-parameter exception (8.12+).
     * Failing here gives one clear error, whichever SDK is installed, without
     * contacting GoCardless.
     */
    private function requireId(?string $id, string $resource): string
    {
        if ($id === null || $id === '') {
            throw new \InvalidArgumentException("No GoCardless {$resource} ID given");
        }

        return $id;
    }

    /**
     * Create a new payment against a preauth
     * @param             $mandateId
     * @param             $amount
     * @param null|string $name
     * @param null|string $description
     * @param string|null $idempotencyKey identifies the collection, so a repeat
     *        returns the payment GoCardless already created instead of taking
     *        the money again. Without one the SDK sends a random key, which only
     *        protects its own retries of this one call.
     * @return \GoCardlessPro\Resources\Payment
     */
    public function newBill($mandateId, $amount, $name = null, $description = null, ?string $idempotencyKey = null)
    {
        try {
            return $this->client->payments()->create([
                "params" => [
                    "amount" => $amount, // amount in pence
                    "currency" => "GBP",
                    "links" => [
                        "mandate" => $mandateId
                    ],
                    "metadata" => [
                        "description" => $name
                    ]
                ],
                "headers" => $idempotencyKey === null ? [] : ["Idempotency-Key" => $idempotencyKey],
            ]);
        } catch (\Exception $e) {
            Log::error($e);

            // Log these to Sentry too, to increase visibility of them.
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }

            throw $e;
        }
    }

    public function cancelPreAuth($preauthId)
    {
        if (empty($preauthId)) {
            return true;
        }
        try {
            $mandate = $this->client->mandates()->cancel($preauthId);

            if ($mandate->status == 'cancelled') {
                return true;
            }

            Log::error('Canceling pre auth failed: ' . json_encode($mandate));
        } catch (\Exception $e) {
            Log::error($e);
        }
        return false;
    }

    /**
     * @param string $reason
     * @return null|string
     */
    public function getNameFromReason($reason)
    {
        switch ($reason) {
            case 'subscription':
                return 'Monthly subscription';
            case 'balance':
                return 'Balance top up';
            case 'equipment-fee':
                return 'Equipment access fee';
            case 'induction':
                return 'Equipment induction';
            case 'door-key':
                return 'Door key';
        }

        return $reason;
    }
}
