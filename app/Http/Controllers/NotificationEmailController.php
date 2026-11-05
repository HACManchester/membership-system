<?php namespace BB\Http\Controllers;

use BB\Entities\Equipment;
use BB\Exceptions\AuthenticationException;
use BB\Exceptions\NotImplementedException;
use BB\Http\Requests\StoreNotificationEmailRequest;
use BB\Mailer\UserMailer;
use BB\Repo\TrainingRecordRepository;
use BB\Repo\UserRepository;
use BB\Support\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NotificationEmailController extends Controller
{
    /**
     * The training statuses a tool's members can be emailed by, with their labels.
     * The route only accepts these.
     */
    private const TRAINING_STATUSES = [
        'awaiting_training' => 'awaiting training',
        'trained' => 'trained',
        'trainer' => 'trainer/maintainer',
    ];

    /**
     * @var UserRepository
     */
    private $userRepository;
    
    /**
     * @var TrainingRecordRepository
     */

    private $trainingRecordRepository;

    /**
     * @param UserRepository               $userRepository
     * @param TrainingRecordRepository $trainingRecordRepository
     * @throws AuthenticationException
     */
    public function __construct(
        UserRepository $userRepository,
        TrainingRecordRepository $trainingRecordRepository
    ) {
        $this->userRepository             = $userRepository;
        $this->trainingRecordRepository        = $trainingRecordRepository;
    }

    public function create(): Response
    {
        $this->authorize('email-members');

        return Inertia::render('NotificationEmail/Create', [
            'recipients' => [
                ['value' => 'all', 'label' => 'All Members'],
            ],
            'urls' => [
                'store' => route('notificationemail.store', [], false),
            ],
        ]);
    }

    public function tool(string $tool_id, string $status): Response|RedirectResponse
    {
        $equipment = Equipment::where('slug', $tool_id)->firstOrFail();

        if (! $equipment->requiresInduction()) {
            return redirect()->route('equipment.show', $equipment->slug)
                ->with('error', "This tool doesn't require an induction.");
        }

        if (CurrentUser::get()->cannot('email-equipment-members', $equipment)) {
            return redirect()->route('equipment.show', $equipment->slug)
                ->with('error', "You may not email members, as you aren't a trainer of this tool, or an admin");
        }

        return Inertia::render('NotificationEmail/Equipment', [
            'equipment' => [
                'name' => $equipment->name,
            ],
            'trainingStatus' => self::TRAINING_STATUSES[$status],
            'recipient' => "tool/{$equipment->slug}/{$status}",
            'urls' => [
                'equipment' => route('equipment.show', $equipment->slug, false),
                'store' => route('notificationemail.store', [], false),
            ],
        ]);
    }

    public function store(StoreNotificationEmailRequest $request)
    {
        $input = $request->validated();

        $parts = explode('/', $input['recipient']);
        $status = null;
        $equipment = null;

        if(count($parts) == 3){
            if($parts[0] == "tool"){
                $tool_slug = $parts[1];
                $status = $parts[2];
                $equipment = Equipment::where('slug', $tool_slug)->firstOrFail();
            }
        }

        if ($input['send_to_all']) {

            if ($input['recipient'] == 'all') {
                if ( ! CurrentUser::get()->isAdmin()) {
                    throw new AuthenticationException("You don't have permission to send to this group");
                }
                $users = $this->userRepository->getActive();
            } elseif($equipment){

                
                
                if(!in_array($status, ["trainer", "trained", "awaiting_training"])){
                    throw new NotImplementedException("Recipient not supported");
                }

                if (CurrentUser::get()->cannot('email-equipment-members', $equipment)) {
                    throw new AuthenticationException("You don't have permission to send to this group");
                }

                if($status == "trainer"){
                    $users = $this->trainingRecordRepository
                        ->getTrainersForEquipment($equipment)
                        ->map(function($item){
                            return $item->user;
                        });
                }elseif($status == "trained"){
                    $users = $this->trainingRecordRepository
                        ->getTrainedUsersForEquipment($equipment)
                        ->map(function($item){
                            return $item->user;
                        });
                }else{
                    $users = $this->trainingRecordRepository
                        ->getUsersPendingTrainingForEquipment($equipment)
                        ->map(function($item){
                            return $item->user;
                        });
                }
            } else {
                throw new NotImplementedException("Recipient not supported");
            }

            foreach ($users as $user) {
                $notification = new UserMailer($user);
                if($equipment){
                    $notification->sendEquipmentNotificationEmail($input['subject'], nl2br(e($input['message'])), $equipment->name, $status);
                }else{
                    $notification->sendNotificationEmail($input['subject'], nl2br(e($input['message'])));
                }
            }

            
        } else {
            //Just send to the current user
            $notification = new UserMailer(CurrentUser::get());
            if($equipment){
                $notification->sendEquipmentNotificationEmail($input['subject'], nl2br(e($input['message'])), $equipment->name, $status);
            }else{
                $notification->sendNotificationEmail($input['subject'], nl2br(e($input['message'])));
            } 
        }
        
        if($equipment){
            \FlashNotification::success("Email Queued to Send to `$equipment->name` equipment users with status of `$status`");
            return \Redirect::route('equipment.show', $equipment->slug);
        }

        return redirect()->route('notificationemail.create')->with('success', 'Email Queued to Send');
    }
} 