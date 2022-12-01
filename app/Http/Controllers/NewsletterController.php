<?php

namespace BB\Http\Controllers;

use BB\Repo\UserRepository;

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

        return view('newsletter.index', compact('activeMembers', 'newsletterRecipients'));
    }
}
