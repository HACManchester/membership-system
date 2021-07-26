<?php namespace BB\Http\Controllers;

use BB\Exceptions\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{

    protected $loginForm;

    function __construct(\BB\Validators\Login $loginForm)
    {
        $this->loginForm = $loginForm;
        \View::share('body_class', 'register_login');
    }


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
        if ( ! Auth::guest()) {
            return redirect()->to('account/' . \Auth::id());
        }
        return \View::make('session.create');
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function store()
	{
        $input = \Input::only('email', 'password');

        $this->loginForm->validate($input);

        if (Auth::attempt($input, true)) {
            return redirect()->intended('account/' . \Auth::id());
        }

        \Notification::error("Invalid login details");
        return redirect()->back()->withInput();
	}

    /**
	 * SSO validate login
	 *
	 * @return Response
	 */
	public function sso()
	{
        $input = \Input::only('sso', 'sig');

        if(empty($input['sso']) || empty($input['sig'])){
            \Log::error("SSO - 'sso' or 'sig' params not set");
        }

        /**
         * The sso input is signed with sig
         * So to verify the signature, we hash with our shared key
         * and check it matches.
         */
        $calculatedHash = hash_hmac('sha256', $input['sso'], env('SSO_KEY'), false);

        if( $calculatedHash != $input['sig'] ){
            \Log::error(
                'SSO - Signature did not match. Calculated: ' . 
                $calculatedHash . ' but received: ' .
                $input['sig'] 
            );
            return \Response::json([
                'success'=>'false', 
                'message' => 'Invalid signature'
            ], 403);
        } else {
            /**
             * The sso input is a string, base64 encoded.
             * e.g. "email=test@test.com&password=password"
             * So we need to decode it, and put it into a variable,
             * called $parsedInput.
             */
            parse_str(base64_decode(urldecode($input['sso'])), $parsedInput);
            
            $this->loginForm->validate($parsedInput);

            if (Auth::attempt([
                'email'     => $parsedInput['email'], 
                'password'  => $parsedInput['password']
            ], false)) {
                /**
                 * Get the user that is returned with those credentials
                 */
                $user = \Auth::user();
                
                /**
                 * This is what's required back by SSO
                 */
                $userData = base64_encode(http_build_query([
                    'name'      => $user->given_name . " " . $user->family_name,
                    'email'     => $user->email,
                    'id'        => $user->id,
                    'username'  => $user->name
                    ]));
                    
                /**
                 * We need to sign what we return so SSO
                 * can validate what it receives.
                 */
                return \Response::json([
                    'success'   => 'true', 
                    'response'  => $userData,
                    'sig'       => hash_hmac('sha256', $userData, env('SSO_KEY'), false)
                ], 200);
            }
        }

        return \Response::json([
            'success'=>'false', 
            'message' => 'Incorrect login details'
        ], 401);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function destroy($id = null)
	{
        Auth::logout();

        \Notification::success('Logged Out');

        return redirect()->home();
	}

    /**
     * @param Request $request
     * @return string
     * @throws AuthenticationException
     */
    public function pusherAuth(Request $request)
    {
        //Verify the user has permission to connect to the chosen channel
        if ($request->get('channel_name') !== 'private-' . Auth::id()) {
            throw new AuthenticationException();
        }

        $pusher = new \Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id')
        );

        return $pusher->socket_auth($request->get('channel_name'), $request->get('socket_id'));
    }


}
