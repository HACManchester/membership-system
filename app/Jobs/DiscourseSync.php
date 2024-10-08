<?php

namespace BB\Jobs;

use BB\Entities\User;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DiscourseSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(Client $client)
    {
        $params = $this->sso_params();
        ['sso' => $sso, 'sig' => $sig] = $this->sign_payload($params);

        try {
            $client->post('admin/users/sync_sso', [
                'base_uri' => config('discourse.url'),
                'query' => compact('sso', 'sig'),
                'headers' => [
                    'Api-Key' => config('discourse.api_key'),
                    'Api-Username' => config('discourse.api_username'),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to sync user {$this->user->id} to Discourse: {$e->getMessage()}");

            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        }
    }

    protected function sso_params()
    {
        return [
            'external_id' => $this->user->id,

            // Make sure UserObserver@saved triggers MemberDiscourseParamsChanged for any field that are used here
            // TODO: Flip dependency here to pull these from one centralised list on user? Or some intermediary class?
            'email' => $this->user->email,
            'username' => $this->user->name,
            'name' => $this->user->suppress_real_name ? $this->user->name : ($this->user->given_name . " " . $this->user->family_name),

            'add_groups' => $this->user->isActive() ? 'active_members' : 'previous_members',
            'remove_groups' => $this->user->isActive() ? 'previous_members' : 'active_members',
        ];
    }

    protected function sign_payload($params)
    {
        $sso = base64_encode(http_build_query($params));
        $sig = hash_hmac('sha256', $sso, env('DISCOURSE_SSO_SECRET'), false);
        return compact('sso', 'sig');
    }
}
