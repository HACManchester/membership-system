<?php namespace BB\Http\Controllers;

class HomeController extends Controller
{


    public function index()
    {
        $guest = \Auth::guest();

        \View::share('body_class', 'home');
        return \View::make('home')->with('guest', $guest);
    }



}
