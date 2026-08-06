<?php namespace BB\Http\Controllers;

use BB\Entities\User;

class MembersController extends Controller
{
    
    /**
     * @var \BB\Repo\ProfileDataRepository
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

    /**
     * Lightweight active-member search for async pickers — returns id + display
     * label matching the member dropdown, so nothing has to be shipped up front.
     */
    public function search(\Illuminate\Http\Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $members = User::where('active', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('display_name', 'like', "%{$q}%")
                        ->orWhere('given_name', 'like', "%{$q}%")
                        ->orWhere('family_name', 'like', "%{$q}%");
                });
            })
            ->orderBy('display_name')
            ->limit(20)
            ->get()
            ->map(function (User $member) {
                $label = $member->name;
                if (! $member->suppress_real_name) {
                    $label .= " ({$member->given_name} {$member->family_name})";
                }

                return ['id' => $member->id, 'name' => $label];
            });

        return response()->json($members);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        // TODO: Is this privacy check necessary? This route is not accessible by guests
        if (\Auth::guest() && $user->profile_private) {
            abort(404);
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
