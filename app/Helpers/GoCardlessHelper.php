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
            'access_token' => env('GOCARDLESS_ACCESS_TOKEN', ''),
            'environment' => (env('NEW_GOCARDLESS_ENV', 'LIVE') == 'LIVE') ? \GoCardlessPro\Environment::LIVE : \GoCardlessPro\Environment::SANDBOX,
        ]);
    }

    public function getPayment($paymentId)
    {
        return $this->client->payments()->get($paymentId);
    }

    public function cancelPayment($paymentId)
    {
        return $this->client->payments()->cancel($paymentId);
    }

    public function newPreAuthUrl($user, $paymentDetails)
    {
        // @phpstan-ignore-next-line
        $redirectFlow = $this->client->redirectFlows()->create([
            "params" => $paymentDetails
        ]);


        // @phpstan-ignore-next-line
        $user->gocardless_setup_id = $redirectFlow->id;
        $user->save();

        // @phpstan-ignore-next-line
        return $redirectFlow->redirect_url;
    }

    public function confirmResource($user, $confirm_params)
    {
        return $this->client->redirectFlows()->complete(
            $user->gocardless_setup_id,

            // @phpstan-ignore-next-line
            ["params" => ["session_token" => 'user-token-' . $user->id]]
        );
    }


    public function createSubscription($mandate, $amount, $dayOfMonth, $subscriptionNumber)
    {
        // @phpstan-ignore-next-line
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
        return $this->client->subscriptions()->cancel($id);
    }

    /**
     * Create a new payment against a preauth
     * @param             $mandateId
     * @param             $amount
     * @param null|string $name
     * @param null|string $description
     * @return \GoCardlessPro\Resources\Payment
     */
    public function newBill($mandateId, $amount, $name = null, $description = null)
    {
        try {
            // @phpstan-ignore-next-line
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
                "headers" => [
                    //"Idempotency-Key" => $preauthId . ':' . time()
                ]
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

            // @phpstan-ignore-next-line
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
