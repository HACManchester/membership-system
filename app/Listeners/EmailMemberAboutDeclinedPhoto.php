<?php

namespace BB\Listeners;


use BB\Events\MemberPhotoWasDeclined;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailMemberAboutDeclinedPhoto
{
    protected $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle(MemberPhotoWasDeclined $event)
    {
        $user = $event->user;
        $this->mailer->send('emails.member-photo-declined', ['user' => $user], function ($m) use ($user) {
            $m->to($user->email, $user->name)->subject('Your profile photo was rejected');
        });
    }
}
