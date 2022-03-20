<?php namespace BB\Http\Controllers;

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
        $users = $this->userRepository->getWantNewsletter();

        return \Response::json([
            'count'     => count($users),
            'emails'    => $users
        ]);
    }



}
