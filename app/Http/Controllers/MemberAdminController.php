<?php

namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Events\MemberGivenTrustedStatus;
use BB\Http\Requests\Account\AdminUpdateMemberRequest;

class MemberAdminController extends Controller
{
    /**
     * @var \BB\Repo\UserRepository
     */
    private $userRepository;

    public function __construct(\BB\Repo\UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->middleware('role:admin');
    }

    public function index()
    {
        $filter = \Request::get('filter');
        $include_online_only = \Request::get('include_online_only');
        $new_only = \Request::get('new_only');
        $sortBy = \Request::get('sortBy');
        $direction = \Request::get('direction', 'asc');
        $showLeft = \Request::get('showLeft', 0);
        $limit = \Request::get('limit');

        $users = $this->userRepository->getPaginated(compact('sortBy', 'direction', 'showLeft', 'filter', 'include_online_only', 'new_only', 'limit'));

        return \View::make('account.index')->with('users', $users);
    }

    /**
     * Update the admin-managed flags on a member. The action-bar submits these
     * from several small forms, so each field is applied only when present.
     *
     * @param  int  $id
     */
    public function update(AdminUpdateMemberRequest $request, $id)
    {
        $user = User::findWithPermission($id);

        $madeTrusted = false;

        if ($request->has('trusted')) {
            if (! $user->trusted && $request->input('trusted')) {
                //User has been made a trusted member
                $madeTrusted = true;
            }
            $user->trusted = $request->input('trusted');
        }

        if ($request->has('key_holder')) {
            $user->key_holder = $request->input('key_holder');
        }

        if ($request->has('induction_completed')) {
            $user->induction_completed = $request->input('induction_completed');
        }

        if ($request->has('profile_photo_on_wall')) {
            $profileData = $user->profile()->first();
            $profileData->profile_photo_on_wall = $request->input('profile_photo_on_wall');
            $profileData->save();
        }

        // Handle membership state fields
        if ($request->has('active')) {
            $user->active = $request->input('active');
        }
        if ($request->has('status')) {
            $user->status = $request->input('status');
        }
        if ($request->has('subscription_expires')) {
            $expiryDate = $request->input('subscription_expires');
            if (!empty($expiryDate)) {
                $user->subscription_expires = $expiryDate;
            }
        }

        $user->save();

        if ($madeTrusted) {
            event(new MemberGivenTrustedStatus($user));
        }

        if ($request->wantsJson()) {
            return \Response::json('Updated', 200);
        } else {
            \FlashNotification::success('Details Updated');
            return \Redirect::route('account.show', [$user->id]);
        }
    }
}
