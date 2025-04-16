<?php

namespace BB\Http\Controllers;

use BB\Repo\UserRepository;
use Inertia\Inertia;

class NewsletterController extends Controller
{
    private $userRepository;

    function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        $activeMembers = $this->userRepository->getActive();
        $newsletterRecipients = $this->userRepository->getWantNewsletter();

        return Inertia::render('Newsletter/Index', [
            'activeMemberEmails' => $activeMembers->pluck('email'),
            'newsletterRecipientEmails' => $newsletterRecipients->pluck('email')
        ]);
    }
}
