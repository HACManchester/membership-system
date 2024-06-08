<?php namespace BB\Http\Controllers;

use BB\Entities\Equipment;
use BB\Exceptions\ImageFailedException;
use BB\Http\Requests\Equipment\StoreEquipmentRequest;
use BB\Http\Requests\Equipment\UpdateEquipmentRequest;
use BB\Repo\EquipmentLogRepository;
use BB\Repo\EquipmentRepository;
use BB\Repo\InductionRepository;
use BB\Repo\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Input;

class EquipmentController extends Controller
{

    /**
     * @var InductionRepository
     */
    private $inductionRepository;

    /**
     * @var EquipmentRepository
     */
    private $equipmentRepository;
    
    /**
     * @var EquipmentLogRepository
     */
    private $equipmentLogRepository;
    
    /**
     * @var UserRepository
     */
    private $userRepository;

    /** @var \Illuminate\Filesystem\FilesystemAdapter */
    protected $disk;

    /**
     * @var array
     */
    protected $ppeList;

    /**
     * @param InductionRepository                    $inductionRepository
     * @param EquipmentRepository                    $equipmentRepository
     * @param EquipmentLogRepository                 $equipmentLogRepository
     * @param UserRepository                         $userRepository
     */
    function __construct(
        InductionRepository $inductionRepository,
        EquipmentRepository $equipmentRepository,
        EquipmentLogRepository $equipmentLogRepository,
        UserRepository $userRepository
    ) {
        $this->inductionRepository    = $inductionRepository;
        $this->equipmentRepository    = $equipmentRepository;
        $this->equipmentLogRepository = $equipmentLogRepository;
        $this->userRepository         = $userRepository;
        $this->disk = Storage::disk('public');

        //Only members of the equipment group can create/update records

        $this->ppeList = [
            'ear-protection'      => 'Ear protection',
            'eye-protection'      => 'Eye protection',
            'face-mask'           => 'Face mask',
            'face-guard'          => 'Full face guard',
            'gloves'              => 'Gloves',
            'protective-clothing' => 'Protective clothing',
            'welding-mask'        => 'Welding mask'
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $user = \Auth::user();
        $allTools = $this->equipmentRepository->getAll();

        $equipmentWithTrainingStatus = $allTools->map(function (Equipment $equipment) use ($user) {
            $trained = $this->inductionRepository->isUserTrained($user->id, $equipment->induction_category);

            return [
                'equipment' => $equipment,
                'trained' => $equipment->requires_induction && $trained
            ];
        });

        $equipmentByRoom = $equipmentWithTrainingStatus->groupBy('equipment.room')->sort();

        return \View::make('equipment.index')
            ->with('equipmentByRoom', $equipmentByRoom);
    }

    public function show(Equipment $equipment)
    {
        $this->authorize('view', $equipment);

        $trainers  = $this->inductionRepository->getTrainersForEquipment($equipment->induction_category);

        $equipmentLog = $this->equipmentLogRepository->getFinishedForEquipment($equipment->device_key);

        $usageTimes = [];
        $usageTimes['billed'] = $this->equipmentLogRepository->getTotalTime($equipment->device_key, true, '');
        $usageTimes['unbilled'] = $this->equipmentLogRepository->getTotalTime($equipment->device_key, false, '');
        $usageTimes['training'] = $this->equipmentLogRepository->getTotalTime($equipment->device_key, null, 'training');
        $usageTimes['testing'] = $this->equipmentLogRepository->getTotalTime($equipment->device_key, null, 'testing');

        $userInduction = $this->inductionRepository->getUserForEquipment(\Auth::user()->id, $equipment->induction_category);

        $trainedUsers = $this->inductionRepository->getTrainedUsersForEquipment($equipment->induction_category);

        $usersPendingInduction = $this->inductionRepository->getUsersPendingInductionForEquipment($equipment->induction_category);

        $memberList = $this->userRepository->getAllAsDropdown();

        // Get info from the docs system
        $docs = $equipment->docs || "";

        $now = new \DateTime("");

        return \View::make('equipment.show')
            ->with('equipment', $equipment)
            ->with('trainers', $trainers)
            ->with('equipmentLog', $equipmentLog)
            ->with('userInduction', $userInduction)
            ->with('trainedUsers', $trainedUsers)
            ->with('usersPendingInduction', $usersPendingInduction)
            ->with('usageTimes', $usageTimes)
            ->with('memberList', $memberList)
            ->with('docs', $docs)
            ->with('now', $now);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->authorize('create', Equipment::class);

        $memberList = $this->userRepository->getAllAsDropdown();
        $roleList = \BB\Entities\Role::pluck('title', 'id');

        return \View::make('equipment.create')
            ->with('memberList', $memberList)
            ->with('roleList', $roleList->toArray())
            ->with('ppeList', $this->ppeList)
            ->with('trusted', true)
            ->with('isTrainerOrAdmin', \Auth::user()->isAdmin());
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws ImageFailedException
     */
    public function store(StoreEquipmentRequest $request)
    {
        $this->authorize('create', Equipment::class);

        $this->equipmentRepository->create($request->validated());

        return \Redirect::route('equipment.show', $request->get('slug'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \BB\Entities\Equipment $equipment
     * @return Response
     */
    public function edit(Equipment $equipment)
    {
        $this->authorize('update', $equipment);

        $memberList = $this->userRepository->getAllAsDropdown();
        $roleList = \BB\Entities\Role::pluck('title', 'id');

        return \View::make('equipment.edit')
            ->with('equipment', $equipment)
            ->with('memberList', $memberList)
            ->with('roleList', $roleList->toArray())
            ->with('ppeList', $this->ppeList);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \BB\Entities\Equipment $equipment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Equipment $equipment, UpdateEquipmentRequest $request)
    {
        $this->authorize('update', $equipment);

        $this->equipmentRepository->update($equipment->id, $request->validated());

        return \Redirect::route('equipment.show', $equipment);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(Equipment $equipment)
    {
        $this->authorize('delete', $equipment);

        $equipment->delete();

        \FlashNotification::success("Deleted {$equipment->name}");
        return redirect()->route('equipment.index');
    }

    public function addPhoto(Equipment $equipment, Request $request)
    {
        $this->authorize('update', $equipment);

        ['photo' => $photo] = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png',
        ]);

        if ($photo) {
            try {
                $ext = $photo->guessClientExtension() ?: 'png';
                $stream = \Image::make($photo->getRealPath())->fit(1000)->stream($ext);

                $newFilename = sprintf('%s.%s', str_random(), $ext);

                $this->disk->put($equipment->getPhotoBasePath() . $newFilename, $stream);

                $equipment->addPhoto($newFilename);

            } catch(\Exception $e) {
                Log::error($e);
                throw new ImageFailedException($e->getMessage());
            }
        }

        \FlashNotification::success("Image added");
        return \Redirect::route('equipment.edit', $equipment);
    }

    public function destroyPhoto(Equipment $equipment, $photoId)
    {
        $this->authorize('update', $equipment);

        if(\Auth::user()->online_only){
            throw new \BB\Exceptions\AuthenticationException();
        }

        $photoPath = $equipment->getPhotoPath($photoId);

        if ($this->disk->exists($photoPath)) {
            $this->disk->delete($photoPath);
        }

        $equipment->removePhoto($photoId);

        \FlashNotification::success("Image deleted");
        return \Redirect::route('equipment.edit', $equipment);
    }
} 
