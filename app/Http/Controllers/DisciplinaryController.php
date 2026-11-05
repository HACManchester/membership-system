<?php

namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Support\MemberStatus;
use Illuminate\Http\Request;

class DisciplinaryController extends Controller
{
    public function ban(User $user, Request $request)
    {
        $this->authorize('ban', $user);

        // validation
        $this->validate($request, [
            'reason' => 'required|string|max:255',
        ]);

        $user->active = false;
        $user->status = MemberStatus::Left;
        $user->banned = true;
        $user->banned_reason = $request->get('reason');
        $user->banned_date = \Carbon\Carbon::now();
        $user->save();

        return redirect()->back();
    }
    
    public function unban(User $user)
    {
        $this->authorize('unban', $user);

        $user->banned = false;
        $user->banned_reason = null;
        $user->banned_date = null;
        $user->save();

        return redirect()->back();
    }
}
