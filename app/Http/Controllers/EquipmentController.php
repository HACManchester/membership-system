<?php

namespace BB\Http\Controllers;

use BB\Entities\Equipment;
use BB\Entities\MaintainerGroup;
use BB\Entities\Room;
use BB\Entities\TrainingRecord;
use BB\Exceptions\ImageFailedException;
use BB\Http\Requests\Equipment\StoreEquipmentRequest;
use BB\Http\Requests\Equipment\UpdateEquipmentRequest;
use BB\Http\Resources\EquipmentFormResource;
use BB\Http\Resources\EquipmentListResource;
use BB\Repo\EquipmentRepository;
use BB\Repo\TrainingRecordRepository;
use BB\Repo\UserRepository;
use BB\Support\PpeOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Input;

class EquipmentController extends Controller
{

    /**
     * @var TrainingRecordRepository
     */
    private $trainingRecordRepository;

    /**
     * @var EquipmentRepository
     */
    private $equipmentRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /** @var \Illuminate\Filesystem\FilesystemAdapter */
    protected $disk;


    function __construct(
        TrainingRecordRepository $trainingRecordRepository,
        EquipmentRepository $equipmentRepository,
        UserRepository $userRepository
    ) {
        $this->trainingRecordRepository    = $trainingRecordRepository;
        $this->equipmentRepository    = $equipmentRepository;
        $this->userRepository         = $userRepository;
        $this->disk = Storage::disk('public');

        //Only members of the equipment group can create/update records
    }

    public function index()
    {
        /** @var \BB\Entities\User $user */
        $user = \Auth::user();
        $equipment = $this->equipmentRepository->getAll();
        $equipment->load(['courses', 'roomModel']);

        // Resolve the viewer's training status for the whole catalogue up front,
        // rather than one query per item, honouring both the course and legacy-key
        // linkage.
        $trained = TrainingRecord::where('user_id', $user->id)
            ->whereNotNull('trained')
            ->get(['course_id', 'key']);
        $trainedCourseIds = $trained->pluck('course_id')->filter()->unique();
        $trainedKeys = $trained->pluck('key')->filter()->unique();

        $equipment->each(function (Equipment $item) use ($trainedCourseIds, $trainedKeys) {
            $viaCourse = $item->courses->pluck('id')->intersect($trainedCourseIds)->isNotEmpty();
            $viaKey = ! empty($item->induction_category)
                && $trainedKeys->contains($item->induction_category);
            $item->setAttribute('trained_for_user', $item->requires_induction && ($viaCourse || $viaKey));
        });

        return Inertia::render('Equipment/Index', [
            'equipment' => EquipmentListResource::collection($equipment),
            'can' => [
                'create' => $user->can('create', Equipment::class),
            ],
            'urls' => [
                'create' => route('equipment.create', [], false),
            ],
        ]);
    }

    /**
     * The admin-editable room list as an id => name map for the equipment form's
     * room dropdown.
     *
     * @return array<int, string>
     */
    private function roomList(): array
    {
        return Room::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function show(Equipment $equipment)
    {
        $this->authorize('view', $equipment);

        $trainers  = $this->trainingRecordRepository->getTrainersForEquipment($equipment);

        $userTrainingRecord = $this->trainingRecordRepository->getUserForEquipment($equipment, \Auth::user()->id);

        $trainedUsers = $this->trainingRecordRepository->getTrainedUsersForEquipment($equipment);

        $usersPendingTraining = $this->trainingRecordRepository->getUsersPendingTrainingForEquipment($equipment);

        $memberList = $this->userRepository->getAllAsDropdown();

        $now = new \DateTime("");

        return \View::make('equipment.show')
            ->with('equipment', $equipment)
            ->with('trainers', $trainers)
            ->with('userTrainingRecord', $userTrainingRecord)
            ->with('trainedUsers', $trainedUsers)
            ->with('usersPendingTraining', $usersPendingTraining)
            ->with('memberList', $memberList)
            ->with('now', $now);
    }

    public function create()
    {
        $this->authorize('create', Equipment::class);

        return Inertia::render('Equipment/Create', array_merge($this->formSharedProps(), [
            'urls' => [
                'index' => route('equipment.index', [], false),
                'store' => route('equipment.store', [], false),
            ],
        ]));
    }

    /**
     * Shared option lists for the equipment create/edit form.
     *
     * @return array
     */
    private function formSharedProps(): array
    {
        return [
            'rooms' => $this->roomList(),
            'maintainerGroupOptions' => MaintainerGroup::orderBy('name', 'ASC')->pluck('name', 'id'),
            'ppeOptions' => PpeOptions::all(),
            'memberList' => $this->userRepository->getAllAsDropdown(),
            'usageCostPerOptions' => ['hour' => 'hour', 'gram' => 'gram', 'page' => 'page'],
            'canManageGlobally' => \Auth::user()->isAdmin() || \Auth::user()->hasRole('equipment'),
        ];
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

        Equipment::create($request->validated());

        return \Redirect::route('equipment.show', $request->get('slug'));
    }


    public function edit(Equipment $equipment)
    {
        $this->authorize('update', $equipment);

        $equipment->load('roomModel');

        return Inertia::render('Equipment/Edit', array_merge($this->formSharedProps(), [
            'equipment' => new EquipmentFormResource($equipment),
            'urls' => [
                'index' => route('equipment.index', [], false),
            ],
        ]));
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Equipment $equipment, UpdateEquipmentRequest $request)
    {
        $this->authorize('update', $equipment);

        $equipment->update($request->validated());

        return \Redirect::route('equipment.show', $equipment);
    }


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

                $newFilename = sprintf('%s.%s', Str::random(), $ext);

                $this->disk->put($equipment->getPhotoBasePath() . $newFilename, $stream);

                $equipment->addPhoto($newFilename);
            } catch (\Exception $e) {
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

        if (\Auth::user()->online_only) {
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
