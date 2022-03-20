<?php namespace BB\Http\Controllers;

class NewsletterController extends Controller
{


    public function index()
    {
        $guest = \Auth::guest();
        $user = \Auth::user();

        \View::share('body_class', 'home');
        return \View::make('home')->with('guest', $guest)->with('user', $user);
    }



}
