<?php namespace BB\Http\Controllers;

use BB\Exceptions\ImageFailedException;
use BB\Repo\EquipmentLogRepository;
use BB\Repo\EquipmentRepository;
use BB\Repo\InductionRepository;
use BB\Repo\UserRepository;
use BB\Validators\EquipmentValidator;
use Illuminate\Support\Facades\Storage;
use Input;
use Michelf\Markdown;

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
     * @var \BB\Validators\EquipmentPhotoValidator
     */
    private $equipmentPhotoValidator;

    /** @var \Illuminate\Filesystem\FilesystemAdapter */
    protected $disk;

    /**
     * @param InductionRepository                    $inductionRepository
     * @param EquipmentRepository                    $equipmentRepository
     * @param EquipmentLogRepository                 $equipmentLogRepository
     * @param UserRepository                         $userRepository
     * @param EquipmentValidator                     $equipmentValidator
     * @param \BB\Validators\EquipmentPhotoValidator $equipmentPhotoValidator
     */
    function __construct(
        InductionRepository $inductionRepository,
        EquipmentRepository $equipmentRepository,
        EquipmentLogRepository $equipmentLogRepository,
        UserRepository $userRepository,
        EquipmentValidator $equipmentValidator,
        \BB\Validators\EquipmentPhotoValidator $equipmentPhotoValidator
    ) {
        $this->inductionRepository    = $inductionRepository;
        $this->equipmentRepository    = $equipmentRepository;
        $this->equipmentLogRepository = $equipmentLogRepository;
        $this->userRepository         = $userRepository;
        $this->equipmentValidator = $equipmentValidator;
        $this->equipmentPhotoValidator = $equipmentPhotoValidator;
        $this->disk = Storage::disk('public');

        //Only members of the equipment group can create/update records
      

        $this->ppeList = [
            'eye-protection' => 'Eye protection',
            'gloves'         => 'Gloves',
            'face-guard'     => 'Full face guard',
            'face-mask'      => 'Face mask',
            'welding-mask'   => 'Welding mask',
            'ear-protection' => 'Ear protection'
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $requiresInduction = $this->equipmentRepository->getRequiresInduction();
        $doesntRequireInduction = $this->equipmentRepository->getDoesntRequireInduction();
        $allTools = $this->equipmentRepository->getAll();

        $rooms = [];
        foreach($allTools as $tool){
          if (!isset($rooms[$tool->room]))
          {
            $rooms[$tool->room] = [];
          }
          array_push($rooms[$tool->room], $tool);
        }

        ksort($rooms);

        return \View::make('equipment.index')
            ->with('byRoom', $rooms)
            ->with('rooms', $rooms)
            ->with('requiresInduction', $requiresInduction)
            ->with('doesntRequireInduction', $doesntRequireInduction);
    }

    public function show($equipmentId)
    {
        $user = \Auth::user();

        $equipment = $this->equipmentRepository->findBySlug($equipmentId);

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

        $isTrainerOrAdmin = $this
            ->inductionRepository
            ->isTrainerForEquipment($equipment->induction_category) || \Auth::user()->isAdmin();

        // Get info from the docs system
        $docs = $equipment->docs || "";

        $now = new \DateTime("");

        return \View::make('equipment.show')
            ->with('equipmentId', $equipmentId)
            ->with('equipment', $equipment)
            ->with('trainers', $trainers)
            ->with('equipmentLog', $equipmentLog)
            ->with('userInduction', $userInduction)
            ->with('trainedUsers', $trainedUsers)
            ->with('usersPendingInduction', $usersPendingInduction)
            ->with('usageTimes', $usageTimes)
            ->with('user', $user)
            ->with('isTrainerOrAdmin', $isTrainerOrAdmin)
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
        if(\Auth::user()->online_only){
            throw new \BB\Exceptions\AuthenticationException();
        }

        $memberList = $this->userRepository->getAllAsDropdown();
        $roleList = \BB\Entities\Role::lists('title', 'id');

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
     * @throws \BB\Exceptions\FormValidationException
     */
    public function store()
    {
        if(\Auth::user()->online_only){
            throw new \BB\Exceptions\AuthenticationException();
        }

        $data = \Request::only([
            'name', 'manufacturer', 'model_number', 'serial_number', 'colour', 'room', 'detail', 'slug',
            'device_key', 'description', 'help_text', 'managing_role_id', 'working', 'usage_cost_per',
            'permaloan', 'permaloan_user_id', 'obtained_at', 'removed_at', 'asset_tag_id', 'ppe',
            'dangerous', 'requires_induction', 'induction_category', 'access_fee', 'usage_cost',
             'induction_instructions', 'trainer_instructions', 'trained_instructions', 'docs'
        ]);
        
        $this->equipmentValidator->validate($data);

        $this->equipmentRepository->create($data);

        return \Redirect::route('equipment.show', $data['slug']);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  string $equipmentId
     * @return Response
     */
    public function edit($equipmentId)
    {
        if(\Auth::user()->online_only){
            throw new \BB\Exceptions\AuthenticationException();
        }

        $equipment = $this->equipmentRepository->findBySlug($equipmentId);
        $memberList = $this->userRepository->getAllAsDropdown();
        $roleList = \BB\Entities\Role::lists('title', 'id');

        $isTrainerOrAdmin = $this
            ->inductionRepository
            ->isTrainerForEquipment($equipment->induction_category) || \Auth::user()->isAdmin();

        
        return \View::make('equipment.edit')
            ->with('equipment', $equipment)
            ->with('memberList', $memberList)
            ->with('roleList', $roleList->toArray())
            ->with('ppeList', $this->ppeList)
            ->with('isTrainerOrAdmin', $isTrainerOrAdmin);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  string $equipmentId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($equipmentId)
    {
        if(\Auth::user()->online_only){
            throw new \BB\Exceptions\AuthenticationException();
        }

        $equipment = $this->equipmentRepository->findBySlug($equipmentId);

        $normalFields = [
            'name', 'manufacturer', 'model_number', 'serial_number', 'colour', 'room', 'detail', 'slug',
            'device_key', 'description', 'help_text', 'managing_role_id', 'working', 'usage_cost_per',
            'permaloan', 'permaloan_user_id', 'obtained_at', 'removed_at', 'asset_tag_id', 'docs'
        ];

        $isTrainerOrAdmin = $this
        ->inductionRepository
        ->isTrainerForEquipment($equipment->induction_category) || \Auth::user()->isAdmin();


        $additionalFields = $isTrainerOrAdmin ? 
        ['dangerous', 'requires_induction', 'induction_category', 'access_fee', 'usage_cost',
            'induction_instructions', 'trainer_instructions', 'trained_instructions', 'ppe', 'access_code'
        ]: [];

        $data = \Request::only(array_merge($additionalFields, $normalFields));
        $this->equipmentValidator->validate($data, $equipment->id);

        $this->equipmentRepository->update($equipment->id, $data);

        return \Redirect::route('equipment.show', $equipmentId);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function addPhoto($equipmentId)
    {
        if(\Auth::user()->online_only){
            throw new \BB\Exceptions\AuthenticationException();
        }

        $equipment = $this->equipmentRepository->findBySlug($equipmentId);

        $data = \Request::only(['photo']);

        $this->equipmentPhotoValidator->validate($data);

        $photo = Input::file('photo');
        if ($photo) {
            try {
                $ext = $photo->guessClientExtension() ?: 'png';
                $stream = \Image::make($photo->getRealPath())->fit(1000)->stream($ext);
                
                $newFilename = sprintf('%s.%s', str_random(), $ext);

                $this->disk->put($equipment->getPhotoBasePath() . $newFilename, $stream);

                $equipment->addPhoto($newFilename);

            } catch(\Exception $e) {
                \Log::error($e);
                throw new ImageFailedException($e->getMessage());
            }
        }

        \Notification::success("Image added");
        return \Redirect::route('equipment.edit', $equipmentId);
    }

    public function destroyPhoto($equipmentId, $photoId)
    {
        if(\Auth::user()->online_only){
            throw new \BB\Exceptions\AuthenticationException();
        }

        $equipment = $this->equipmentRepository->findBySlug($equipmentId);
        $photoPath = $equipment->getPhotoPath($photoId);

        if ($this->disk->exists($photoPath)) {
            $this->disk->delete($photoPath);
        }

        $equipment->removePhoto($photoId);

        \Notification::success("Image deleted");
        return \Redirect::route('equipment.edit', $equipmentId);
    }
} 
