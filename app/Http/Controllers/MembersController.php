<?php namespace BB\Http\Controllers;

use BB\Entities\User;

class MembersController extends Controller
{
    
    /**
     * @var
     */
    private $profileRepo;
    /**
     * @var \BB\Repo\ProfileSkillsRepository
     */
    private $profileSkillsRepository;
    /**
     * @var \BB\Repo\UserRepository
     */
    private $userRepository;

    /**
     * @param \BB\Repo\ProfileDataRepository   $profileRepo
     * @param \BB\Repo\ProfileSkillsRepository $profileSkillsRepository
     * @param \BB\Repo\UserRepository          $userRepository
     */
    function __construct(\BB\Repo\ProfileDataRepository $profileRepo, \BB\Repo\ProfileSkillsRepository $profileSkillsRepository, \BB\Repo\UserRepository $userRepository)
    {
        $this->profileRepo = $profileRepo;
        $this->profileSkillsRepository = $profileSkillsRepository;
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        $users = $this->userRepository->getActivePublicList();
        return \View::make('members.index')->with('users', $users);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        // TODO: Is this privacy check necessary? This route is not accessible by guests
        if (\Auth::guest() && $user->profile_private) {
            return abort(404);
        }

        if (!\Auth::user()->isAdmin() && !$user->active) {
            \FlashNotification::error("This user's profile is no longer available as they are not an active member.");
            return \Redirect::route('members.index');
        }

        $profileData = $this->profileRepo->getUserProfile($id);
        $userSkills = array_intersect_ukey($this->profileSkillsRepository->getAll(), array_flip($profileData->skills), [$this, 'key_compare_func']);
        return \View::make('members.show')->with('user', $user)->with('profileData', $profileData)->with('userSkills', $userSkills);
    }

    private function key_compare_func($key1, $key2)
    {
        if ($key1 == $key2) {
            return 0;
        } else if ($key1 > $key2) {
            return 1;
        } else {
            return -1;
        }
    }

}
