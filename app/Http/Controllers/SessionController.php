<?php

namespace BB\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SessionController extends Controller
{

    function __construct()
    {
        \View::share('body_class', 'register_login');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (!Auth::guest()) {
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
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'     => 'required|email',
            'password'  => 'required',
        ]);

        if (Auth::attempt($credentials, true)) {

            if (\Input::get('sso')) {
                return redirect(
                    "sso/login" .
                        "?sso=" . \Input::get('sso') .
                        "&sig=" . \Input::get('sig')
                );
            } else {
                return redirect()->intended('account/' . \Auth::id());
            }
        }

        \FlashNotification::error("Invalid login details");
        return redirect()->back()->withInput();
    }

    public function sso_login()
    {
        $input = \Input::only('sso', 'sig');

        if (empty($input['sso']) || empty($input['sig'])) {
            return \View::make('session.error')
                ->with('code', '0');
        }

        // Decode SSO object from discourse and convert URL string to variables
        parse_str(base64_decode($input['sso']), $decoded);


        if (empty($decoded['nonce']) || empty($decoded['return_sso_url'])) {
            return \View::make('session.error')
                ->with('code', '3');
        }

        $nonce = $decoded['nonce'];
        $return_sso_url = $decoded['return_sso_url'];

        /**
         * The sso input is signed with sig
         * So to verify the signature, we hash with our shared key
         * and check it matches.
         */
        $calculatedHash = hash_hmac('sha256', $input['sso'], env('DISCOURSE_SSO_SECRET'), false);

        if ($calculatedHash != $input['sig']) {
            Log::error("HMAC: $calculatedHash  !!!!Provided: " . $input['sig']);

            return \View::make('session.error')
                ->with('code', '1');
        }


        if (!Auth::guest()) {

            $user = Auth::user();

            if (!$user->email_verified) {
                return \View::make('session.error')
                    ->with('code', '2');
            }

            $name = $user->suppress_real_name ? $user->name : ($user->given_name . " " . $user->family_name);

            $userData = base64_encode(http_build_query([
                'nonce'         => $nonce,
                'name'          => $name,
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id = null)
    {
        Auth::logout();

        \FlashNotification::success('Logged Out');

        return redirect()->home();
    }
}
