<?php

namespace BB\Http\Controllers;

use BB\Entities\User;

class AddressApprovalController extends Controller
{
    /**
     * @var \BB\Repo\AddressRepository
     */
    private $addressRepository;

    public function __construct(\BB\Repo\AddressRepository $addressRepository)
    {
        $this->addressRepository = $addressRepository;
        $this->middleware('role:admin');
    }

    /**
     * Approve or decline a member's pending address change.
     *
     * @param  int  $id
     */
    public function update($id)
    {
        $user = User::findWithPermission($id);

        if (\Request::input('approve_new_address') == 'Approve') {
            $this->addressRepository->approvePendingMemberAddress($id);
        } elseif (\Request::input('approve_new_address') == 'Decline') {
            $this->addressRepository->declinePendingMemberAddress($id);
        }

        \FlashNotification::success('Details Updated');
        return \Redirect::route('account.show', [$user->id]);
    }
}
