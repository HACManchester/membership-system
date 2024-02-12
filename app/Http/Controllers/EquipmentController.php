<?php namespace BB\Http\Controllers;

use Auth;
use BB\Entities\Equipment;
use BB\Entities\Role;
use BB\Exceptions\AuthenticationException;
use BB\Exceptions\FormValidationException;
use BB\Exceptions\ImageFailedException;
use BB\Repo\EquipmentLogRepository;
use BB\Repo\EquipmentRepository;
use BB\Repo\InductionRepository;
use BB\Repo\UserRepository;
use BB\Validators\EquipmentPhotoValidator;
use BB\Validators\EquipmentValidator;
use DateTime;
use Exception;
use FlashNotification;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Image;
use Input;
use Log;
use Redirect;
use Request;
use View;

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
    /**
     * @var EquipmentValidator
     */
    private $equipmentValidator;
    /**
     * @var EquipmentPhotoValidator
     */
    private $equipmentPhotoValidator;

    /** @var FilesystemAdapter */
    protected $disk;

    /**
     * @param InductionRepository     $inductionRepository
     * @param EquipmentRepository     $equipmentRepository
     * @param EquipmentLogRepository  $equipmentLogRepository
     * @param UserRepository          $userRepository
     * @param EquipmentValidator      $equipmentValidator
     * @param EquipmentPhotoValidator $equipmentPhotoValidator
     */
    function __construct(
        InductionRepository $inductionRepository,
        EquipmentRepository $equipmentRepository,
        EquipmentLogRepository $equipmentLogRepository,
        UserRepository $userRepository,
        EquipmentValidator $equipmentValidator,
        EquipmentPhotoValidator $equipmentPhotoValidator
    ) {
        $this->inductionRepository = $inductionRepository;
        $this->equipmentRepository = $equipmentRepository;
        $this->equipmentLogRepository = $equipmentLogRepository;
        $this->userRepository = $userRepository;
        $this->equipmentValidator = $equipmentValidator;
        $this->equipmentPhotoValidator = $equipmentPhotoValidator;
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
     * @return \Illuminate\Contracts\View\View|\Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $allTools = $this->equipmentRepository->getAll();

        $equipmentWithTrainingStatus = $allTools->map(function (Equipment $equipment) use ($user) {
            $trained = $this->inductionRepository->isUserTrained($user->id, $equipment->induction_category);

            return [
                'equipment' => $equipment,
                'trained'   => $equipment->requires_induction && $trained
            ];
        });

        $equipmentByRoom = $equipmentWithTrainingStatus->groupBy('equipment.room')->sort();

        return View::make('equipment.index')
            ->with('equipmentByRoom', $equipmentByRoom);
    }

    public function show(Equipment $equipment)
    {
        $this->authorize('view', $equipment);

        $trainers = $this->inductionRepository->getTrainersForEquipment($equipment->induction_category);

        $equipmentLog = $this->equipmentLogRepository->getFinishedForEquipment($equipment->device_key);

        $usageTimes = [];
        $usageTimes['billed'] = $this->equipmentLogRepository->getTotalTime($equipment->device_key, true, '');
        $usageTimes['unbilled'] = $this->equipmentLogRepository->getTotalTime($equipment->device_key, false, '');
        $usageTimes['training'] = $this->equipmentLogRepository->getTotalTime($equipment->device_key, null, 'training');
        $usageTimes['testing'] = $this->equipmentLogRepository->getTotalTime($equipment->device_key, null, 'testing');

        $userInduction = $this->inductionRepository->getUserForEquipment(Auth::user()->id, $equipment->induction_category);

        $trainedUsers = $this->inductionRepository->getTrainedUsersForEquipment($equipment->induction_category);

        $usersPendingInduction = $this->inductionRepository->getUsersPendingInductionForEquipment($equipment->induction_category);

        $memberList = $this->userRepository->getAllAsDropdown();

        $isTrainerOrAdmin = $this
                ->inductionRepository
                ->isTrainerForEquipment($equipment->induction_category) || Auth::user()->isAdmin() || $this->authorize('create', Equipment::class);

        // Get info from the docs system
        $docs = $equipment->docs || "";

        $now = new DateTime("");

        return View::make('equipment.show')
            ->with('equipmentId', $equipment->id)
            ->with('equipment', $equipment)
            ->with('trainers', $trainers)
            ->with('equipmentLog', $equipmentLog)
            ->with('userInduction', $userInduction)
            ->with('trainedUsers', $trainedUsers)
            ->with('usersPendingInduction', $usersPendingInduction)
            ->with('usageTimes', $usageTimes)
            ->with('user', Auth::user())
            ->with('isTrainerOrAdmin', $isTrainerOrAdmin)
            ->with('memberList', $memberList)
            ->with('docs', $docs)
            ->with('now', $now);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('create', Equipment::class);

        $memberList = $this->userRepository->getAllAsDropdown();
        $roleList = Role::pluck('title', 'id');

        return View::make('equipment.create')
            ->with('memberList', $memberList)
            ->with('roleList', $roleList->toArray())
            ->with('ppeList', $this->ppeList)
            ->with('trusted', true)
            ->with('isTrainerOrAdmin', Auth::user()->isAdmin());
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return RedirectResponse
     * @throws FormValidationException
     */
    public function store()
    {
        $this->authorize('create', Equipment::class);

        $data = Request::only([
            'name',
            'manufacturer',
            'model_number',
            'serial_number',
            'colour',
            'room',
            'detail',
            'slug',
            'device_key',
            'description',
            'help_text',
            'managing_role_id',
            'working',
            'usage_cost_per',
            'permaloan',
            'permaloan_user_id',
            'obtained_at',
            'removed_at',
            'asset_tag_id',
            'ppe',
            'dangerous',
            'requires_induction',
            'induction_category',
            'access_fee',
            'usage_cost',
            'induction_instructions',
            'trainer_instructions',
            'trained_instructions',
            'docs',
            'accepting_inductions'
        ]);

        $this->equipmentValidator->validate($data);

        $this->equipmentRepository->create($data);

        return Redirect::route('equipment.show', $data['slug']);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param Equipment $equipment
     * @return \Illuminate\Contracts\View\View|\Illuminate\View\View
     */
    public function edit(Equipment $equipment)
    {
        $this->authorize('update', $equipment);

        $memberList = $this->userRepository->getAllAsDropdown();
        $roleList = Role::pluck('title', 'id');

        $isTrainerOrAdmin = $this
                ->inductionRepository
                ->isTrainerForEquipment($equipment->induction_category) || Auth::user()->isAdmin() || $this->authorize('create', Equipment::class);


        return View::make('equipment.edit')
            ->with('equipment', $equipment)
            ->with('memberList', $memberList)
            ->with('roleList', $roleList->toArray())
            ->with('ppeList', $this->ppeList)
            ->with('isTrainerOrAdmin', $isTrainerOrAdmin);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Equipment $equipment
     * @return RedirectResponse
     * @throws FormValidationException
     */
    public function update(Equipment $equipment)
    {
        $this->authorize('update', $equipment);

        $normalFields = [
            'name', 'manufacturer', 'model_number', 'serial_number', 'colour', 'room', 'detail', 'slug',
            'device_key', 'description', 'help_text', 'managing_role_id', 'working', 'usage_cost_per',
            'permaloan', 'permaloan_user_id', 'obtained_at', 'removed_at', 'asset_tag_id', 'docs'
        ];

        $isTrainerOrAdmin = $this
                ->inductionRepository
                ->isTrainerForEquipment($equipment->induction_category) || Auth::user()->isAdmin() || $this->authorize('create', Equipment::class);


        $additionalFields = $isTrainerOrAdmin ?
            [
                'dangerous',
                'requires_induction',
                'induction_category',
                'access_fee',
                'usage_cost',
                'induction_instructions',
                'trainer_instructions',
                'trained_instructions',
                'ppe',
                'access_code',
                'accepting_inductions'
            ] : [];

        $data = Request::only(array_merge($additionalFields, $normalFields));
        $this->equipmentValidator->validate($data, $equipment->id);

        $this->equipmentRepository->update($equipment->id, $data);

        return Redirect::route('equipment.show', $equipment->toArray());
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param Equipment $equipment
     * @return RedirectResponse
     * @throws Exception
     */
    public function destroy(Equipment $equipment)
    {
        $this->authorize('delete', $equipment);

        $equipment->delete();

        FlashNotification::success("Deleted {$equipment->name}");
        return redirect()->route('equipment.index');
    }

    /**
     * @throws FormValidationException
     * @throws ImageFailedException
     */
    public function addPhoto(Equipment $equipment)
    {
        $this->authorize('update', $equipment);

        $data = Request::only(['photo']);

        $this->equipmentPhotoValidator->validate($data);

        $photo = Input::file('photo');
        if ($photo) {
            try {
                $ext = $photo->guessClientExtension() ?: 'png';
                $stream = Image::make($photo->getRealPath())->fit(1000)->stream($ext);

                $newFilename = sprintf('%s.%s', str_random(), $ext);

                $this->disk->put($equipment->getPhotoBasePath() . $newFilename, $stream);

                $equipment->addPhoto($newFilename);

            } catch (Exception $e) {
                Log::error($e);
                throw new ImageFailedException($e->getMessage());
            }
        }

        FlashNotification::success("Image added");
        return Redirect::route('equipment.edit', $equipment->toArray());
    }

    public function destroyPhoto(Equipment $equipment, $photoId)
    {
        $this->authorize('update', $equipment);

        if (Auth::user()->online_only) {
            throw new AuthenticationException();
        }

        $photoPath = $equipment->getPhotoPath($photoId);

        if ($this->disk->exists($photoPath)) {
            $this->disk->delete($photoPath);
        }

        $equipment->removePhoto($photoId);

        FlashNotification::success("Image deleted");
        return Redirect::route('equipment.edit', $equipment->toArray());
    }
} 
