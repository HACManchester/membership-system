<?php

namespace BB\Http\Controllers;

use BB\Entities\KeyFob;
use BB\Entities\User;
use BB\Http\Requests\StoreKeyFobRequest;

class KeyFobController extends Controller
{
    public function index(User $user)
    {
        $this->authorize('view', [KeyFob::class, $user]);

        return \View::make('keyfobs.index')->with('user', $user);
    }


    public function store(User $user, StoreKeyFobRequest $request)
    {
        $this->authorize('create', [KeyFob::class, $user]);

        if ($user->online_only) {
            \FlashNotification::error("Online only accounts cannot have access methods configured.");
            return \Redirect::route('keyfobs.index', $user->id);
        }
        
        if (!$user->induction_completed) {
            \FlashNotification::error("General induction must be completed before adding access methods.");
            return \Redirect::route('keyfobs.index', $user->id);
        }

        
        //If the fob begins with ff it's a request for an access code
        //Bin off any extra characters
        if ($request->input('type') == 'access_code') {
            $keyId = "ff" . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
        } else {
            $keyId = $request->input('key_id');
        }

        KeyFob::create([
            'user_id' => $user->id,
            'key_id' => $keyId
        ]);

        \FlashNotification::success("Key fob/Access code has been activated");
        return \Redirect::route('keyfobs.index', $user->id);
    }

    public function destroy(User $user, KeyFob $fob)
    {
        $this->authorize('delete', $fob);

        $fob->markLost();

        \FlashNotification::success("Key Fob marked as lost/broken");
        return \Redirect::route('keyfobs.index', $user->id);
    }
}
