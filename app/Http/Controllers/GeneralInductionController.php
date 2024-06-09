<?php

namespace BB\Http\Controllers;

use BB\Repo\UserRepository;
use Illuminate\Http\Request;
use BB\Entities\Settings;
use BB\Rules\GeneralInductionCodeRule;

class GeneralInductionController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'induction_code' => ['required', new GeneralInductionCodeRule]
        ]);

        $user = \Auth::user();
        $this->userRepository->recordInductionCompleted($user->id);

        \FlashNotification::success('General Induction complete!');
        return redirect()->route('account.show', $user);
    }
}
