<?php

namespace BB\Http\Controllers;

use BB\Entities\Course;
use BB\Entities\Equipment;
use BB\Entities\MaintainerGroup;
use BB\Entities\Room;
use BB\Entities\TrainingRecord;
use BB\Exceptions\ImageFailedException;
use BB\Http\Requests\Equipment\BulkStoreEquipmentRequest;
use BB\Http\Requests\Equipment\StoreEquipmentRequest;
use BB\Http\Requests\Equipment\UpdateEquipmentRequest;
use BB\Http\Resources\EquipmentFormResource;
use BB\Http\Resources\EquipmentListResource;
use BB\Http\Resources\EquipmentShowResource;
use BB\Repo\EquipmentRepository;
use BB\Repo\TrainingRecordRepository;
use BB\Repo\UserRepository;
use BB\Support\PpeOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                'bulkCreate' => route('equipment.bulk-create', [], false),
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

        /** @var \BB\Entities\User $user */
        $user = \Auth::user();
        $equipment->load('courses', 'roomModel', 'maintainerGroup');

        $userRecord = $this->trainingRecordRepository->getUserForEquipment($equipment, $user->id);

        $hasCourse = $equipment->courses->count() > 0;
        $liveCourse = $hasCourse && $equipment->courses->first()->live;
        $hasLegacyInduction = ! empty($equipment->induction_category);
        $useLegacyInduction = $equipment->requiresInduction()
            && ! $liveCourse
            && ($hasLegacyInduction || ! $hasCourse);

        $canTrain = $user->can('train', $equipment);

        return Inertia::render('Equipment/Show', [
            'equipment' => new EquipmentShowResource($equipment),
            'courses' => $equipment->courses->map(function ($course) {
                return [
                    'name' => $course->name,
                    'live' => (bool) $course->live,
                    'url' => route('courses.show', $course->slug, false),
                ];
            })->values(),
            'flags' => [
                'useLegacyInduction' => $useLegacyInduction,
                'liveCourse' => $liveCourse,
            ],
            'userStatus' => [
                'hasRecord' => (bool) $userRecord,
                'trained' => $userRecord && $userRecord->trained ? true : false,
                'isTrainer' => $userRecord ? (bool) $userRecord->is_trainer : false,
            ],
            'canRequestInduction' => $useLegacyInduction
                && ! $userRecord
                && (bool) $equipment->accepting_inductions
                && ! $user->online_only,
            // The trainer / trained / awaiting-training member lists are management
            // data — only built for members who can manage training on this
            // equipment, never shipped to ordinary viewers.
            'training' => ($useLegacyInduction && $canTrain)
                ? $this->legacyTrainingData($equipment, $user)
                : null,
            'memberList' => $canTrain ? $this->userRepository->getAllAsDropdown() : (object) [],
            'authUserId' => $user->id,
            'can' => [
                'update' => $user->can('update', $equipment),
                'delete' => $user->can('delete', $equipment),
                'train' => $canTrain,
            ],
            'urls' => [
                'index' => route('equipment.index', [], false),
                'edit' => route('equipment.edit', $equipment->slug, false),
                'destroy' => route('equipment.destroy', $equipment->slug, false),
                'requestInduction' => route('equipment_training.create', $equipment->slug, false),
                'emailTrainers' => route('notificationemail.equipment', [$equipment->slug, 'trainer'], false),
                'emailTrained' => route('notificationemail.equipment', [$equipment->slug, 'trained'], false),
                'emailAwaiting' => route('notificationemail.equipment', [$equipment->slug, 'awaiting_training'], false),
            ],
        ]);
    }

    /**
     * Shape the legacy trainer / trained / awaiting-training lists for the show
     * page, with per-record capabilities and action URLs.
     *
     * @param  \BB\Entities\User  $user
     * @return array
     */
    private function legacyTrainingData(Equipment $equipment, $user): array
    {
        $trainers = $this->trainingRecordRepository->getTrainersForEquipment($equipment);
        $trained = $this->trainingRecordRepository->getTrainedUsersForEquipment($equipment);
        $pending = $this->trainingRecordRepository->getUsersPendingTrainingForEquipment($equipment);

        return [
            'trainers' => $trainers->map(function ($record) use ($equipment, $user) {
                return [
                    'id' => $record->id,
                    'user' => $this->trainingUser($record),
                    'can' => ['demote' => $user->can('demote', $record)],
                    'urls' => ['demote' => route('equipment_training.demote', [$equipment->slug, $record->id], false)],
                ];
            })->values(),
            'trained' => $trained->map(function ($record) use ($equipment, $user) {
                return [
                    'id' => $record->id,
                    'user' => $this->trainingUser($record),
                    'is_trainer' => (bool) $record->is_trainer,
                    'trained_on' => optional($record->trained)->toFormattedDateString(),
                    'can' => [
                        'untrain' => $user->can('untrain', $record),
                        'promote' => $user->can('promote', $record),
                    ],
                    'urls' => [
                        'untrain' => route('equipment_training.untrain', [$equipment->slug, $record->id], false),
                        'promote' => route('equipment_training.promote', [$equipment->slug, $record->id], false),
                    ],
                ];
            })->values(),
            'pending' => $pending->filter(function ($record) use ($user) {
                return $user->can('view', $record) || $record->user_id == $user->id;
            })->map(function ($record) use ($equipment, $user) {
                return [
                    'id' => $record->id,
                    'user' => $this->trainingUser($record),
                    'requested_on' => optional($record->created_at)->toFormattedDateString(),
                    'can' => [
                        'delete' => $user->can('delete', $record),
                        'train' => $user->can('train', $record),
                    ],
                    'urls' => [
                        'destroy' => route('equipment_training.destroy', [$equipment->slug, $record->id], false),
                        'train' => route('equipment_training.train', [$equipment->slug, $record->id], false),
                    ],
                ];
            })->values(),
        ];
    }

    /**
     * @param  \BB\Entities\TrainingRecord  $record
     * @return array
     */
    private function trainingUser($record): array
    {
        return [
            'id' => $record->user->id,
            'name' => $record->user->name,
            'pronouns' => $record->user->pronouns,
            'url' => route('members.show', $record->user->id, false),
        ];
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
            'courseOptions' => Course::orderBy('name')->get(['id', 'name', 'live'])
                ->map(function (Course $course) {
                    return ['id' => $course->id, 'name' => $course->name, 'live' => (bool) $course->live];
                })
                ->values(),
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

        $data = $request->validated();
        $courseId = $data['course_id'] ?? null;
        unset($data['course_id']);

        // Associating a course means the equipment requires induction, managed
        // through that course.
        if ($courseId) {
            $data['requires_induction'] = true;
        }

        $equipment = DB::transaction(function () use ($data, $courseId) {
            $equipment = Equipment::create($data);
            $equipment->courses()->sync($courseId ? [$courseId] : []);

            return $equipment;
        });

        return \Redirect::route('equipment.show', $equipment->slug);
    }


    public function edit(Equipment $equipment)
    {
        $this->authorize('update', $equipment);

        $equipment->load('roomModel', 'courses');

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

        $data = $request->validated();
        $courseId = $data['course_id'] ?? null;
        unset($data['course_id']);

        if ($courseId) {
            $data['requires_induction'] = true;
        }

        DB::transaction(function () use ($equipment, $data, $courseId) {
            $equipment->update($data);
            $equipment->courses()->sync($courseId ? [$courseId] : []);
        });

        return \Redirect::route('equipment.show', $equipment);
    }


    public function bulkCreate()
    {
        $this->authorize('create', Equipment::class);

        $shared = $this->formSharedProps();

        return Inertia::render('Equipment/BulkCreate', [
            'rooms' => $shared['rooms'],
            'maintainerGroupOptions' => $shared['maintainerGroupOptions'],
            'courseOptions' => $shared['courseOptions'],
            'canManageGlobally' => $shared['canManageGlobally'],
            'urls' => [
                'index' => route('equipment.index', [], false),
                'store' => route('equipment.bulk-store', [], false),
            ],
        ]);
    }

    public function bulkStore(BulkStoreEquipmentRequest $request)
    {
        $this->authorize('create', Equipment::class);

        $validated = $request->validated();
        $courseId = $validated['course_id'] ?? null;
        $shared = [
            'room_id' => $validated['room_id'],
            'maintainer_group_id' => $validated['maintainer_group_id'] ?? null,
        ];

        $count = DB::transaction(function () use ($validated, $shared, $courseId) {
            foreach ($validated['items'] as $item) {
                $data = array_merge($shared, [
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                ]);
                if ($courseId) {
                    $data['requires_induction'] = true;
                }

                $equipment = Equipment::create($data);
                if ($courseId) {
                    $equipment->courses()->sync([$courseId]);
                }
            }

            return count($validated['items']);
        });

        \FlashNotification::success("Created {$count} equipment item(s).");

        return \Redirect::route('equipment.index');
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
