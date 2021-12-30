<?php namespace BB\Http\Controllers;

use BB\Exceptions\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client as HttpClient;

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
        return \View::make('session.create')
            ->with('sso', false);
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

            if(\Input::get('sso')){
                return redirect(
                    "sso/login" . 
                    "?sso=" . \Input::get('sso') . 
                    "&sig=" . \Input::get('sig')
                );
            }else{
                return redirect()->intended('account/' . \Auth::id());
            }
        }

        \Notification::error("Invalid login details");
        return redirect()->back()->withInput();
	}

    public function sso_login()
	{
        $input = \Input::only('sso', 'sig');

        if(empty($input['sso']) || empty($input['sig'])){
            return \View::make('session.error')
                ->with('code', '0');
        }

        // Decode SSO object from discourse and convert URL string to variables
        parse_str(base64_decode($input['sso']), $decoded);
        $nonce = $decoded['nonce'];
        $return_sso_url = $decoded['return_sso_url'];

         /**
         * The sso input is signed with sig
         * So to verify the signature, we hash with our shared key
         * and check it matches.
         */
        $calculatedHash = hash_hmac('sha256', $input['sso'], env('DISCOURSE_SSO_SECRET'), false);

        if( $calculatedHash != $input['sig'] ){
            \Log::error("HMAC: $calculatedHash  !!!!Provided: " . $input['sig']);

            return \View::make('session.error')
                ->with('code', '1');
        } 


        if ( ! Auth::guest()) {

            $user = Auth::user();

            if(!$user->email_verified){  
                return \View::make('session.error')
                    ->with('code', '2');
            }
                
            $userData = base64_encode(http_build_query([
                'nonce'         => $nonce,
                'name'          => $user->given_name . " " . $user->family_name,
                'email'         => $user->email,
                'external_id'   => $user->id,
                'username'      => $user->name
            ]));

            return \View::make('session.confirm')
                ->with('sso', $userData)
                ->with('sig', hash_hmac('sha256', $userData, env('DISCOURSE_SSO_SECRET'), false))
                ->with('user', $user)
                ->with('return_sso_url', $return_sso_url);
        }

        return \View::make('session.create')
            ->with('sso', $input['sso'])
            ->with('sig', $input['sig']);
	}

    /**
     * Say a user has a different email on the forum
     * Sync will tell forum that user `forumUsername` belong to `membershipSysUsername`
     * It does this by adding external_id to the forum user's account
     * This external ID is the ID in the membership system
     * That way, they can log in with different emails in the future.
     */
    public function sso_sync($memberID, $forumUsername){

        // Create an array of SSO parameters.
        $sso_params = array(
            'external_id' => $memberID,
            'username' => $forumUsername,
        );

        // Convert the SSO parameters into the SSO payload and generate the SSO signature.
        $sso_payload = base64_encode( http_build_query( $sso_params ) );
        $sig = hash_hmac( 'sha256', $sso_payload, env('DISCOURSE_SSO_SECRET') );

        $url = 'http://list.hacman.org.uk/admin/users/sync_sso';
        $post_fields = array(
            'sso' => $sso_payload,
            'sig' => $sig,
        );

        $client = new HttpClient;
        $res = $client->request('POST', $url, [
            'query' => $post_fields,
            'headers' => [
                'Api-Key' => env('DISCOURSE_API_KEY'),
                'Api-Username' => env('DISCOURSE_API_USERNAME'),
            ]
        ]);
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
