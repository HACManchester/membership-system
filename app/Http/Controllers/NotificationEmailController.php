<?php namespace BB\Http\Controllers;

use BB\Exceptions\AuthenticationException;
use BB\Exceptions\NotImplementedException;
use BB\Http\Requests\StoreNotificationEmailRequest;
use BB\Mailer\UserMailer;
use BB\Repo\InductionRepository;
use BB\Repo\UserRepository;
use BB\Repo\EquipmentRepository;

class NotificationEmailController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    
    /**
     * @var InductionRepository
     */

    private $inductionRepository;
    /**
     * 
     * @var EquipmentRepository
     */
    private $equipmentRepository;

    /**
     * @param UserRepository               $userRepository
     * @param InductionRepository $inductionRepository
     * @throws AuthenticationException
     */
    public function __construct(
        UserRepository $userRepository,
        EquipmentRepository $equipmentRepository,
        InductionRepository $inductionRepository
    ) {
        $this->userRepository             = $userRepository;
        $this->equipmentRepository        = $equipmentRepository;
        $this->inductionRepository        = $inductionRepository;
    }

    public function create()
    {
        if ( ! \Auth::user()->isAdmin() && \Auth::user()->roles()->get()->count() <= 0) {
            throw new AuthenticationException("You don't have permission to be here");
        }

        $recipients = ['all' => 'All Members', 'laser_induction_members' => 'Laser Induction Members'];
        return \View::make('notification_email.create')
            ->with('recipients', $recipients);
    }

    public function tool($tool_id, $status)
    {
        $equipment = $this->equipmentRepository->findBySlug($tool_id);

        if(!$equipment->requiresInduction()){
            \FlashNotification::error("This tool doesn't require an induction.");
            return \Redirect::route('equipment.show', $equipment->slug);
        }

        $statuses = [
            "awaiting_training" => "awaiting training",
            "trained" => "trained",
            "trainer" => "trainer/maintainer"
        ];

        if(!array_key_exists($status, $statuses)){
            throw new NotImplementedException("Recipient not supported");
        }

        // Check the user is an admin or a trainer of the tool they specified
        $trainers  = $this->inductionRepository->getTrainersForEquipment($equipment->induction_category);
        $allowed = false;

        if(\Auth::user()->isAdmin()){
            $allowed = true;
        }

        foreach($trainers as $t){
            if(\Auth::user()->id == $t->user->id){
                $allowed = true;
            }
        }

        if(!$allowed){
            \FlashNotification::error("You may not email members, as you aren't a trainer of this tool, or an admin");
            return \Redirect::route('equipment.show', $equipment->slug);
        }

        return \View::make('notification_email.tool')
            ->with("equipment", $equipment)
            ->with("statuses", $statuses)
            ->with("status", $status);
    }

    public function store(StoreNotificationEmailRequest $request)
    {
        $input = $request->validated();

        $parts = explode('/', $input['recipient']);
        $isToolEmail = false;

        if(count($parts) == 3){
            if($parts[0] == "tool"){
                $isToolEmail = true;
                $tool_slug = $parts[1];
                $status = $parts[2];
                $equipment = $this->equipmentRepository->findBySlug($tool_slug);
            }
        }

        if ($input['send_to_all']) {

            if ($input['recipient'] == 'all') {
                if ( ! \Auth::user()->isAdmin()) {
                    throw new AuthenticationException("You don't have permission to send to this group");
                }
                $users = $this->userRepository->getActive();
            } elseif($isToolEmail){

                
                
                if(!in_array($status, ["trainer", "trained", "awaiting_training"])){
                    throw new NotImplementedException("Recipient not supported");
                }

                //TODO: look at how to tidy this up, as it's duplicated above
                $trainers  = $this->inductionRepository->getTrainersForEquipment($equipment->induction_category);
                $allowed = false;

                if(\Auth::user()->isAdmin()){
                    $allowed = true;
                }

                foreach($trainers as $t){
                    if(\Auth::user()->id == $t->user->id){
                        $allowed = true;
                    }
                }

                if($allowed){
                    if($status == "trainer"){
                        $users = $this->inductionRepository
                            ->getTrainersForEquipment($equipment->induction_category)
                            ->map(function($item){
                                return $item->user;
                            });
                    }elseif($status == "trained"){
                        $users = $this->inductionRepository
                            ->getTrainedUsersForEquipment($equipment->induction_category)
                            ->map(function($item){
                                return $item->user;
                            });
                    }elseif($status == "awaiting_training"){
                        $users = $this->inductionRepository
                            ->getUsersPendingInductionForEquipment($equipment->induction_category)
                            ->map(function($item){
                                return $item->user;
                            });
                    }
                }

                
            }


            foreach ($users as $user) {
                $notification = new UserMailer($user);
                if($isToolEmail){
                    $notification->sendEquipmentNotificationEmail($input['subject'], nl2br($input['message']), $equipment->name, $status);
                }else{
                    $notification->sendNotificationEmail($input['subject'], nl2br($input['message']));
                }
            }

            
        } else {
            //Just send to the current user
            $notification = new UserMailer(\Auth::user());
            if($isToolEmail){
                $notification->sendEquipmentNotificationEmail($input['subject'], nl2br($input['message']), $equipment->name, $status);
            }else{
                $notification->sendNotificationEmail($input['subject'], nl2br($input['message']));
            } 
        }
        
        if($isToolEmail){
            \FlashNotification::success("Email Queued to Send to `$equipment->name` equipment users with status of `$status`");
            return \Redirect::route('equipment.show', $equipment->slug);
        }

        \FlashNotification::success('Email Queued to Send');
        return \Redirect::route('home');
    }
} 