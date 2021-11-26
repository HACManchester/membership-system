<?php namespace BB\Http\Controllers;

class GiftController extends Controller
{


    public function index()
    {
        \View::share('body_class', 'home');
        return \View::make('home');
    }



}
