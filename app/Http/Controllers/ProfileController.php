<?php namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Http\Requests\Account\UpdateProfileRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class ProfileController extends Controller
{

    /**
     * @var \BB\Repo\ProfileSkillsRepository
     */
    private $profileSkillsRepository;
    /**
     * @var \BB\Helpers\UserImage
     */
    private $userImage;

    function __construct(
        \BB\Repo\ProfileSkillsRepository $profileSkillsRepository,
        \BB\Helpers\UserImage $userImage)
    {
        $this->profileSkillsRepository = $profileSkillsRepository;
        $this->userImage = $userImage;
    }

    public function edit(string $userId)
    {
        $user = User::findOrFail($userId);
        $this->authorize('updateProfile', $user);

        $profileData = $user->profile()->firstOrFail();
        $skills = $this->profileSkillsRepository->getSelectArray();
        return \View::make('account.profile.edit')
            ->with('profileData', $profileData)
            ->with('userId', $userId)
            ->with('skills', $skills)
            ->with('user', $user);
    }

    public function update(UpdateProfileRequest $request, int $userId)
    {
        $user = User::findOrFail($userId);

        $input = $request->validated();
        $input['profile_photo_private'] = ! empty($input['profile_photo_private']);

        $profile = $user->profile()->firstOrFail();
        $profile->update($input);

        // A single file only: new_profile_photo[] arrives as an array
        $photo = $request->file('new_profile_photo');
        if ($photo instanceof UploadedFile) {
            try {
                $this->userImage->uploadPhoto($user->hash, $photo->getRealPath(), true);

                $profile->update(['new_profile_photo' => 1]);

                \FlashNotification::success("Photo uploaded, it will be checked and appear shortly");
            } catch (\Exception $e) {
                Log::error($e);
            }
        } else {
            \FlashNotification::success("Profile Updated");
        }

        return \Redirect::route('members.show', $userId);
    }

} 