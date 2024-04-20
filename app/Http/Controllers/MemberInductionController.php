<?php

namespace BB\Http\Controllers;

use BB\Repo\UserRepository;
use BB\Validators\InductionValidator;
use Illuminate\Http\Request;
use BB\Entities\Settings;

class MemberInductionController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var InductionValidator
     */
    private $inductionValidator;

    function __construct(UserRepository $userRepository, InductionValidator $inductionValidator)
    {
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
        $user = \Auth::user();
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
        $input = $request->only('induction_code');
        
        $this->inductionValidator->validate($input);

        $induction_code = Settings::get("general_induction_code");

        if(trim(strtolower($input['induction_code'])) != strtolower($induction_code)){
            throw new \BB\Exceptions\ValidationException("Invalid induction code.");
        }

        $user = \Auth::user();
        $this->userRepository->recordInductionCompleted($user->id);

        \FlashNotification::success('General Induction complete!');
        return redirect()->route('account.show', $user);
    }
}
