<?php

namespace BB\Http\Controllers;

use BB\Entities\User;
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

        $user->update([
            'active' => false,
            'status' => 'left',
            'banned' => true,
            'banned_reason' => $request->get('reason'),
            'banned_date' => \Carbon\Carbon::now(),
        ]);

        return redirect()->back();
    }
    
    public function unban(User $user)
    {
        $this->authorize('unban', $user);

        $user->update([
            'banned' => false,
            'banned_reason' => null,
            'banned_date' => null,
        ]);

        return redirect()->back();
    }
}
