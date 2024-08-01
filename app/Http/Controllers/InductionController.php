<?php namespace BB\Http\Controllers;

use BB\Entities\Induction;
use BB\Events\Inductions\InductionCompletedEvent;
use BB\Events\Inductions\InductionMarkedAsTrainerEvent;
use BB\Events\Inductions\InductionRequestedEvent;
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
        $slug = \Request::input('slug', false);
        $userId = \Request::input('user_id');

        $equipment = $this->equipmentRepository->findBySlug($slug);

        $this->authorize('train', $equipment);

        $induction = Induction::create([
            'user_id' => $userId,
            'key' => $equipment->induction_category,
            'paid' => true,
            'payment_id' => 0
        ]);
        \Event::dispatch(new InductionRequestedEvent($induction));
        
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
        $slug = \Request::input('slug', false);
        $induction = Induction::findOrFail($id);

        $equipment = $this->equipmentRepository->findBySlug($slug);

        $this->authorize('train', $equipment);

        if (\Request::input('mark_trained', false)) {
            $induction->trained = \Carbon\Carbon::now();
            $induction->trainer_user_id = \Request::input('trainer_user_id', false);
            $induction->save();
            
            \Event::dispatch(new InductionCompletedEvent($induction));
        } elseif (\Request::input('is_trainer', false)) {
            $induction->is_trainer = true;
            $induction->save();
            
            \Event::dispatch(new InductionMarkedAsTrainerEvent($induction));
        } elseif (\Request::input('not_trainer', false)) {
            $induction->is_trainer = false;
            $induction->save();
        } elseif (\Request::input('mark_untrained', false)) {
            $induction->trained = null;
            $induction->trainer_user_id = 0;
            $induction->save();
        } elseif (\Request::input('cancel_payment', false)) {
            if ($induction->trained) {
                throw new \BB\Exceptions\NotImplementedException();
            }
            //$payment = $this->paymentRepository->getById($induction->payment_id);
            $this->paymentRepository->refundPaymentToBalance($induction->payment_id);
            $this->inductionRepository->delete($id);
        } else {
            throw new \BB\Exceptions\NotImplementedException();
        }
        \FlashNotification::success("Induction record has been updated");
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
        $slug = \Request::input('slug', false);
        $induction = Induction::findOrFail($id);
        $equipment = $this->equipmentRepository->findBySlug($slug);

        $this->authorize('train', $equipment);

        $induction->delete();
        
        return \Redirect::route('equipment.show', $slug);
    }


}
