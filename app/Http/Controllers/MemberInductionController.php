<?php

namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Repo\PolicyRepository;
use BB\Repo\UserRepository;
use BB\Validators\InductionValidator;
use Illuminate\Http\Request;
use BB\Http\Requests;
use Michelf\Markdown;
use BB\Entities\Settings;

class MemberInductionController extends Controller
{

    /**
     * @var PolicyRepository
     */
    private $policyRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var InductionValidator
     */
    private $inductionValidator;

    function __construct(PolicyRepository $policyRepository, UserRepository $userRepository, InductionValidator $inductionValidator)
    {
        $this->policyRepository = $policyRepository;
        $this->userRepository = $userRepository;
        $this->inductionValidator = $inductionValidator;
    }

    /**
     * Show the peer induction page
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    { 
        $user = false;
        if(!\Auth::guest()){
            $user = \Auth::user();
        }
        $induction_code = Settings::get("general_induction_code");
        $prefill_code = $request->has('code') ? $request->input('code') : '';

        return view('general-induction.show')
            ->with('user', $user)
            ->with('general_induction_code', $induction_code)
            ->with('prefill_induction_code', $prefill_code);
    }

    /**
     * Set a peer induction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $input = $request->only('rules_agreed', 'inductee_email', 'induction_code');
        
        $this->inductionValidator->validate($input);

        $induction_code = Settings::get("general_induction_code");

        if(trim(strtolower($input['induction_code'])) != strtolower($induction_code)){
            throw new \BB\Exceptions\ValidationException("Invalid induction code.");
        }

        $user = User::where('email', '=', $input['inductee_email'])->first();
        if(!$user){
            throw new \BB\Exceptions\ValidationException("Cannot find the inductee - check the email is the one used for signing up.");
        }

        $this->userRepository->recordInductionCompleted($user->id);

        \Notification::success('Member marked as inducted! They can now set up entry methods on their account.');
        return \Redirect::route('general-induction.show');
    }
}
