<?php namespace BB\Http\Controllers;

use BB\Entities\Induction;
use BB\Entities\User;
use BB\Exceptions\PaymentException;
use BB\Repo\PaymentRepository;

class InductionController extends Controller
{

    /**
     * @var \BB\Repo\InductionRepository
     */
    private $inductionRepository;
    /**
     * @var \BB\Repo\EquipmentRepository
     */
    private $equipmentRepository;
    /**
     * @var PaymentRepository
     */
    private $paymentRepository;

    /**
     * @param \BB\Repo\InductionRepository $inductionRepository
     */
    function __construct(\BB\Repo\InductionRepository $inductionRepository, \BB\Repo\EquipmentRepository $equipmentRepository, PaymentRepository $paymentRepository)
    {
        $this->inductionRepository = $inductionRepository;
        $this->equipmentRepository = $equipmentRepository;
        $this->paymentRepository = $paymentRepository;
    }

    public function create(){
        $slug = \Input::get('slug', false);
        $userId = \Input::get('user_id');

        $equipment = $this->equipmentRepository->findBySlug($slug);

        $isTrainerOrAdmin = $this
            ->inductionRepository
            ->isTrainerForEquipment($equipment->induction_category) || \Auth::user()->isAdmin();

        if(!$isTrainerOrAdmin) {
            throw new \BB\Exceptions\AuthenticationException();
        }

        Induction::create([
            'user_id' => $userId,
            'key' => $equipment->induction_category,
            'paid' => true,
            'payment_id' => 0
        ]);
        
        return \Redirect::route('equipment.show', $slug);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param      $userId
     * @param  int $id
     * @throws \BB\Exceptions\NotImplementedException
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($userId, $id)
    {
        $slug = \Input::get('slug', false);
        $induction = Induction::findOrFail($id);

        $equipment = $this->equipmentRepository->findBySlug($slug);

        $isTrainerOrAdmin = $this
            ->inductionRepository
            ->isTrainerForEquipment($equipment->induction_category) || \Auth::user()->isAdmin();

        if(!$isTrainerOrAdmin) {
            throw new \BB\Exceptions\AuthenticationException();
        }

        if (\Input::get('mark_trained', false)) {
            $induction->trained = \Carbon\Carbon::now();
            $induction->trainer_user_id = \Input::get('trainer_user_id', false);
            $induction->save();
        } elseif (\Input::get('is_trainer', false)) {
            $induction->is_trainer = true;
            $induction->save();
        } elseif (\Input::get('not_trainer', false)) {
            $induction->is_trainer = false;
            $induction->save();
        } elseif (\Input::get('mark_untrained', false)) {
            $induction->trained = null;
            $induction->trainer_user_id = 0;
            $induction->save();
        } elseif (\Input::get('cancel_payment', false)) {
            if ($induction->trained) {
                throw new \BB\Exceptions\NotImplementedException();
            }
            //$payment = $this->paymentRepository->getById($induction->payment_id);
            $this->paymentRepository->refundPaymentToBalance($induction->payment_id);
            $this->inductionRepository->delete($id);
        } else {
            throw new \BB\Exceptions\NotImplementedException();
        }
        \Notification::success("Updated");
        if($slug){
            return \Redirect::route('equipment.show', $slug);
        }
        return \Redirect::route('account.show', $userId);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($uid, $id)
    {
        $slug = \Input::get('slug', false);
        $induction = Induction::findOrFail($id);
        $equipment = $this->equipmentRepository->findBySlug($slug);

        $isTrainerOrAdmin = $this
            ->inductionRepository
            ->isTrainerForEquipment($equipment->induction_category) || \Auth::user()->isAdmin();

        if(!$isTrainerOrAdmin) {
            throw new \BB\Exceptions\AuthenticationException();
        }

        $induction->delete();
        
        return \Redirect::route('equipment.show', $slug);
    }


        /*
    *  Quiz callback takes the webhook from TypeForm and processes the result
    */
    public function quiz_callback(Request $request){
        $response = json_encode($request->getContent())['form_response'];

        // From the response, get the hidden fields `induction_id` and `hash`, and normal field `score`
        $form_id        =   $response['form_id']
        $user_id        =   $response['hidden']['user_id'];
        $equipment_id   =   $response['hidden']['equipment_id'];
        $hash           =   $response['hidden']['hash'];
        $score          =   $response['calculated']['score'];

        // Check the IDs match the hash
        $calculatedHash = hash_hmac('sha256', "{$user_id}/{$equipment_id}", env('QUIZ_HASH_SECRET'), false);

        if($calculatedHash != $hash) {
            throw new \BB\Exceptions\AuthenticationException("Invalid hash for quiz callback");
        }
        
        // Get the user and equipment
        $user = User::findOrFail($user_id);
        $equipment = Equipment::findOrFail($equipment_id);
        $induction = Induction::where('user_id', $user->id)->where('key', $equipment->induction_category)->first();
        
        // From the response, check the form ID matches the split form ID in the Equipment
        if($form_id != end(explode('/', $equipment->quiz_url))) {
            throw new \BB\Exceptions\AuthenticationException("Invalid form ID for quiz callback");
        }
        
        if($score >= $equipment->quiz_pass_mark){
            $induction->trained = \Carbon\Carbon::now();
            $induction->trainer_user_id = \Input::get($user->id, false);
            $induction->save();
        }else{
            $induction->delete();
        }

        return \Response::make('Success', 200);
    }


}
