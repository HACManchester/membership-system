<?php namespace BB\Http\Controllers;

use BB\Entities\KeyFob;

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
    public function index()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        if(\Auth::user()->online_only){
            throw new \BB\Exceptions\AuthenticationException();
        }

        $input = \Input::only('user_id', 'key_id');

        //If the fob befins with ff it's a request for an access cod
        //Bin off any extra characters

        if(substr( $input['key_id'], 0, 2 ) === "ff"){
            $input['key_id']="ff".rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
        }

        $this->keyFobForm->validate($input);

        KeyFob::create($input);

        \Notification::success("Key fob/Access code has been activated");
        return \Redirect::route('account.show', $input['user_id']);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $fob = KeyFob::findOrFail($id);
        $fob->markLost();
        \Notification::success("Key Fob marked as lost/broken");
        return \Redirect::route('account.show',$fob->user_id);
    }


}
