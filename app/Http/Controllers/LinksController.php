<?php

namespace BB\Http\Controllers;

use Illuminate\Http\Request;

class LinksController extends Controller
{
    public function forum(Request $request)
    {
        $user = $request->user();

        if (!$user->visited_forum) {
            $user->visited_forum = true;
            $user->save();
        }

        return redirect()->to(config('links.forum'));
    }
}
