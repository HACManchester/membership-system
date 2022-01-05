<?php namespace BB\Http\Controllers;

class AdminController extends Controller
{

    public function index()
    {
        return \View::make('admin');
    }


}
