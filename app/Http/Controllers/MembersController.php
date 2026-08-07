<?php namespace BB\Http\Controllers;

use BB\Entities\User;
use BB\Http\Resources\MemberCardResource;
use BB\Http\Resources\MemberProfileResource;
use Inertia\Inertia;

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
        return Inertia::render('Members/Index', [
            'members' => MemberCardResource::collection($this->userRepository->getActivePublicList()),
        ]);
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

        if (\Auth::guest() && $user->profile_private) {
            abort(404);
        }

        /** @var User $authUser */
        $authUser = \Auth::user();

        if (! $authUser->isAdmin() && ! $user->active) {
            \FlashNotification::error("This user's profile is no longer available as they are not an active member.");
            return \Redirect::route('members.index');
        }

        $profileData = $this->profileRepo->getUserProfile($id);
        $user->setRelation('profile', $profileData);
        $userSkills = array_intersect_ukey($this->profileSkillsRepository->getAll(), array_flip($profileData->skills), [$this, 'key_compare_func']);

        $canManage = $authUser->id == $user->id || $authUser->isAdmin();

        return Inertia::render('Members/Show', [
            'profile' => new MemberProfileResource($user, $this->shapeSkills($userSkills)),
            'can' => [
                'edit' => $authUser->id == $user->id,
                'viewAccount' => $canManage,
            ],
            'urls' => [
                'index' => route('members.index', [], false),
                'editProfile' => route('account.profile.edit', $user->id, false),
                'account' => route('account.show', $user->id, false),
            ],
        ]);
    }

    /**
     * Flatten the matched skills to the fields the profile page renders.
     *
     * @param  array  $userSkills
     * @return array<int, array{name: string, icon: string}>
     */
    private function shapeSkills($userSkills): array
    {
        $skills = [];
        foreach ($userSkills as $skill) {
            $skills[] = ['name' => $skill['name'], 'icon' => $skill['icon']];
        }

        return $skills;
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
