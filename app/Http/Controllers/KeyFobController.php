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
        if(\Auth::user()->online_only || !\Auth::user()->induction_completed){
            throw new \BB\Exceptions\AuthenticationException();
        }

        $input = \Input::only('key_id');

        //If the fob begins with ff it's a request for an access code
        //Bin off any extra characters
        if(substr( $input['key_id'], 0, 2 ) === "ff"){

            // generate random access code, if there's a collision, it'll fail due to db constraints
            $input['key_id']="ff".rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
        }

        $this->keyFobForm->validate($input);

        KeyFob::create([
            'user_id' => \Auth::user()->id, 
            'key_id' => $input['key_id']
        ]);

        \FlashNotification::success("Key fob/Access code has been activated");
        return \Redirect::route('account.show', \Auth::user()->id);
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
        $fobQuery = KeyFob::where('id', $id);

        if(!\Auth::user()->isAdmin()){
            $fobQuery->where('user_id', \Auth::user()->id);
        }
        $fob = $fobQuery->firstOrFail();
        $fob->markLost();
        \FlashNotification::success("Key Fob marked as lost/broken");
        return \Redirect::route('account.show',$fob->user_id);
    }


}
