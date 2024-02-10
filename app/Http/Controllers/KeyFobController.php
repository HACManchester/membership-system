<?php

namespace BB\Http\Controllers;

use BB\Entities\KeyFob;
use BB\Entities\User;

class KeyFobController extends Controller
{
    /**
     * @var \BB\Validators\KeyFob
     */
    private $keyFobForm;

    public function __construct(\BB\Validators\KeyFob $keyFobForm)
    {

        $this->keyFobForm = $keyFobForm;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(User $user)
    {
        $this->authorize('view', [KeyFob::class, $user]);

        return \View::make('keyfobs.index')->with('user', $user);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(User $user)
    {
        $this->authorize('create', [KeyFob::class, $user]);

        if ($user->online_only || !$user->induction_completed) {
            throw new \BB\Exceptions\AuthenticationException();
        }

        $input = \Input::only('key_id');

        //If the fob begins with ff it's a request for an access code
        //Bin off any extra characters
        if (substr($input['key_id'], 0, 2) === "ff") {

            // generate random access code, if there's a collision, it'll fail due to db constraints
            $input['key_id'] = "ff" . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
        }

        $this->keyFobForm->validate($input);

        KeyFob::create([
            'user_id' => $user->id,
            'key_id' => $input['key_id']
        ]);

        \FlashNotification::success("Key fob/Access code has been activated");
        return \Redirect::route('keyfobs.index', $user->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(User $user, KeyFob $fob)
    {
        $this->authorize('delete', $fob);

        $fob->markLost();

        \FlashNotification::success("Key Fob marked as lost/broken");
        return \Redirect::route('keyfobs.index', $user->id);
    }
}
