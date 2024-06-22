<?php

namespace BB\Http\Controllers;

use BB\Entities\Induction;
use BB\Entities\Payment;
use BB\Entities\User;
use BB\Events\Inductions\InductionRequestedEvent;
use BB\Exceptions\NotImplementedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{


    /**
     *
     * @TODO: Workout exactly what this is used for - I think most of the functionality has been moved elsewhere
     *
     */


    /**
     * @var \BB\Repo\EquipmentRepository
     */
    private $equipmentRepository;
    /**
     * @var \BB\Repo\PaymentRepository
     */
    private $paymentRepository;
    /**
     * @var \BB\Repo\UserRepository
     */
    private $userRepository;
    /**
     * @var \BB\Repo\SubscriptionChargeRepository
     */
    private $subscriptionChargeRepository;

    function __construct(
        \BB\Helpers\GoCardlessHelper $goCardless,
        \BB\Repo\EquipmentRepository $equipmentRepository,
        \BB\Repo\PaymentRepository $paymentRepository,
        \BB\Repo\UserRepository $userRepository,
        \BB\Repo\SubscriptionChargeRepository $subscriptionChargeRepository
    ) {
        $this->goCardless                   = $goCardless;
        $this->equipmentRepository          = $equipmentRepository;
        $this->paymentRepository            = $paymentRepository;
        $this->userRepository               = $userRepository;
        $this->subscriptionChargeRepository = $subscriptionChargeRepository;

        $this->middleware('role:member', array('only' => ['create', 'destroy']));
    }


    public function index()
    {
        $sortBy       = \Request::get('sortBy', 'created_at');
        $direction    = \Request::get('direction', 'desc');
        $dateFilter   = \Request::get('date_filter', '');
        $memberFilter = \Request::get('member_filter', '');
        $reasonFilter = \Request::get('reason_filter', '');
        $this->paymentRepository->setPerPage(50);

        if ($dateFilter) {
            $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $dateFilter)->setTime(0, 0, 0);
            $this->paymentRepository->dateFilter($startDate, $startDate->copy()->addMonth());
        }

        if ($memberFilter) {
            $this->paymentRepository->memberFilter($memberFilter);
        }

        if ($reasonFilter) {
            $this->paymentRepository->reasonFilter($reasonFilter);
        }

        $payments = $this->paymentRepository->getPaginated(compact('sortBy', 'direction'));

        $paymentTotal = $this->paymentRepository->getTotalAmount();

        $dateRangeEarliest = \Carbon\Carbon::create(2009, 07, 01);
        $dateRangeStart    = \Carbon\Carbon::now();
        $dateRange         = [];
        while ($dateRangeStart->gt($dateRangeEarliest)) {
            $dateRange[$dateRangeStart->toDateString()] = $dateRangeStart->format('F Y');
            $dateRangeStart->subMonth();
        }

        $memberList = $this->userRepository->getAllAsDropdown();

        $reasonList = Payment::getPaymentReasons();

        return \View::make('payments.index')->with('payments', $payments)->with('dateRange', $dateRange)
            ->with('memberList', $memberList)->with('reasonList', $reasonList)->with('paymentTotal', $paymentTotal);
    }


    /**
     * Start the creation of a new gocardless payment
     *   Details get posted into this method and the redirected to gocardless
     *
     * @depreciated
     * @param $userId
     */
    public function create($userId)
    {
        throw new \BB\Exceptions\NotImplementedException();
    }


    /**
     * Store a manual payment
     *
     * @param $userId
     * @return Illuminate\Http\RedirectResponse
     * @deprecated
     */
    public function store($userId, Request $request)
    {
        // TODO: Review this method. Should have no active use?
        $user = User::findWithPermission($userId);

        if (!\Auth::user()->hasRole('admin') &&  !\Auth::user()->hasRole('finance')) {
            throw new \BB\Exceptions\AuthenticationException;
        }

        Log::debug('Manual payment endpoint getting hit. account/{id}/payment. paymentController@store ' . json_encode(\Input::all()));

        $reason = \Input::get('reason');

        if ($reason == 'subscription') {
            $payment = new Payment([
                'reason'           => $reason,
                'source'           => \Input::get('source'),
                'source_id'        => '',
                'amount'           => $user->monthly_subscription,
                'amount_minus_fee' => $user->monthly_subscription,
                'status'           => 'paid'
            ]);
            $user->payments()->save($payment);

            $user->extendMembership(\Input::get('source'), \Carbon\Carbon::now()->addMonth());
        } elseif ($reason == 'induction') {
            if (\Input::get('source') == 'manual') {
                $ref = \Input::get('induction_key');
                ($item = $this->equipmentRepository->findBySlug($ref)) || App::abort(404);
                $payment = new Payment([
                    'reason'           => $reason,
                    'source'           => 'manual',
                    'source_id'        => '',
                    'amount'           => $item->cost,
                    'amount_minus_fee' => $item->cost,
                    'status'           => 'paid'
                ]);
                $payment = $user->payments()->save($payment);
                $induction = Induction::create([
                    'user_id'    => $user->id,
                    'key'        => $ref,
                    'paid'       => true,
                    'payment_id' => $payment->id
                ]);
                \Event::dispatch(new InductionRequestedEvent($induction));
            } else {
                throw new \BB\Exceptions\NotImplementedException();
            }
        } elseif ($reason == 'door-key') {
            $payment = new Payment([
                'reason'           => $reason,
                'source'           => \Input::get('source'),
                'source_id'        => '',
                'amount'           => 10,
                'amount_minus_fee' => 10,
                'status'           => 'paid'
            ]);
            $user->payments()->save($payment);

            $user->key_deposit_payment_id = $payment->id;
            $user->save();
        } elseif ($reason == 'storage-box') {
            $payment = new Payment([
                'reason'           => $reason,
                'source'           => \Input::get('source'),
                'source_id'        => '',
                'amount'           => 5,
                'amount_minus_fee' => 5,
                'status'           => 'paid'
            ]);
            $user->payments()->save($payment);

            $user->storage_box_payment_id = $payment->id;
            $user->save();
        } elseif ($reason == 'balance') {
            $amount = $request->validate([
                'amount' => 'required|numeric'
            ]);
            $payment = new Payment([
                'reason'           => 'balance',
                'source'           => \Input::get('source'),
                'source_id'        => '',
                'amount'           => $amount,
                'amount_minus_fee' => $amount,
                'status'           => 'paid'
            ]);
            $user->payments()->save($payment);

            $memberCreditService = \App::make('\BB\Services\Credit');
            $memberCreditService->setUserId($user->id);
            $memberCreditService->recalculate();

            //This needs to be improved
            \FlashNotification::success('Payment recorded');

            return \Redirect::route('account.bbcredit.index', $user->id);
        } else {
            throw new \BB\Exceptions\NotImplementedException();
        }
        \FlashNotification::success('Payment recorded');

        return \Redirect::route('account.show', [$user->id]);
    }

    /**
     * Update a payment
     * Change where the money goes by altering the original record or creating a secondary payment
     *
     * @param Request $request
     * @param  int    $paymentId
     *
     * @return Illuminate\Http\RedirectResponse
     * @throws NotImplementedException
     * @throws \BB\Exceptions\PaymentException
     */
    public function update(Request $request, $paymentId)
    {
        $payment = $this->paymentRepository->getById($paymentId);

        switch ($request->get('change')) {
            case 'assign-unknown-to-user':
                $newUserId = $request->get('user_id');
                try {
                    $newUser = $this->userRepository->getById($newUserId);
                } catch (ModelNotFoundException $e) {
                    \FlashNotification::error('User not found');
                    break;
                }

                $this->paymentRepository->assignPaymentToUser($paymentId, $newUser->id);

                \FlashNotification::success('Payment updated');

                break;

            case 'refund-to-balance':

                if ($payment->reason === 'induction') {
                    throw new NotImplementedException('Please refund via the member induction list');
                }

                $this->paymentRepository->refundPaymentToBalance($paymentId);

                \FlashNotification::success('Payment updated');

                break;

            default:
                throw new NotImplementedException('This hasn\'t been built yet');
        }

        return \Redirect::back();
    }


    /**
     * Remove the specified payment
     *
     * @param  int $id
     * @return Illuminate\Http\RedirectResponse
     * @throws \BB\Exceptions\ValidationException
     */
    public function destroy($id)
    {
        $payment = $this->paymentRepository->getById($id);

        //we can only allow some records to get deleted, only cash payments can be removed, everything else must be refunded off
        if ($payment->source != 'cash') {
            throw new \BB\Exceptions\ValidationException('Only cash payments can be deleted');
        }
        if ($payment->reason != 'balance') {
            throw new \BB\Exceptions\ValidationException('Currently only payments to the members balance can be deleted');
        }

        //The delete event will broadcast an event and allow related actions to occur
        $this->paymentRepository->delete($id);

        \FlashNotification::success('Payment deleted');

        return \Redirect::back();
    }


    /**
     * This is a method for migrating user to the variable gocardless subscription
     * It will cancel the existing direct debit and direct the user to setup a pre auth
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function migrateDD()
    {
        $user = \Auth::user();

        //cancel the existing dd
        try {
            $subscription = $this->goCardless->cancelSubscription($user->subscription_id);
            if ($subscription->status != 'cancelled') {
                \FlashNotification::error('Could not cancel the existing subscription');

                return \Redirect::back();
            }
        } catch (\Exception $e) {
        }

        $user->payment_method  = '';
        $user->subscription_id = '';
        $user->save();

        $payment_details = array(
            "description"          => "Hackspace Manchester",
            'success_redirect_url' => str_replace('http://', 'https://', route('account.subscription.store', $user->id)),
            "session_token"        => 'user-token-' . $user->id,
            'prefilled_customer'   => [
                'given_name'    => $user->given_name,
                'family_name'   => $user->family_name,
                'email'         => $user->email,
                'address_line1' => $user->address->line_1,
                'address_line2' => $user->address->line_2,
                'city'          => $user->address->line_3,
                'postal_code'   => $user->address->postcode,
                'country_code'  => 'GB'
            ]
        );

        return \Redirect::to($this->goCardless->newPreAuthUrl($user, $payment_details));
    }

    public function possibleDuplicates()
    {
        $possibleDuplicates = $this->paymentRepository->getPossibleDuplicates();

        return view('payments.possible-duplicates', [
            'possibleDuplicates' => $possibleDuplicates
        ]);
    }
}
