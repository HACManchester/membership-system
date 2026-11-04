<?php

namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Events\MemberPhotoWasDeclined;

class MemberPhotoApprovalController extends Controller
{
    /**
     * @var \BB\Helpers\UserImage
     */
    private $userImage;

    public function __construct(\BB\Helpers\UserImage $userImage)
    {
        $this->userImage = $userImage;
        $this->middleware('role:admin');
    }

    /**
     * Approve or decline a member's pending profile photo.
     */
    public function update(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('manage', $user);
        $profile = $user->profile()->firstOrFail();

        if (\Request::input('photo_approved')) {
            $this->userImage->approveNewImage($user->hash);
            $profile->new_profile_photo = false;
            $profile->profile_photo = true;
            $profile->save();
        } else {
            $profile->update(['new_profile_photo' => false]);
            event(new MemberPhotoWasDeclined($user));
        }

        \FlashNotification::success('Details Updated');
        return \Redirect::route('account.show', [$user->id]);
    }
}
