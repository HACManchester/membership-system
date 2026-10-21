<?php

namespace BB\Http\Controllers;

use BB\Support\CurrentUser;
use Illuminate\Http\Request;

class LinksController extends Controller
{
    public function forum(Request $request)
    {
        $user = CurrentUser::get();

        if (!$user->visited_forum) {
            $user->visited_forum = true;
            $user->save();
        }

        return redirect()->to(config('links.forum'));
    }
}
