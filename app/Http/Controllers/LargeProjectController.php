<?php

namespace BB\Http\Controllers;

use Illuminate\Http\Request;

use BB\Http\Requests;
use BB\Http\Controllers\Controller;

class LargeProjectController extends Controller
{

    /**
     * @var \BB\Repo\LargeProjectRepository
     */
    private $LargeProjectRepository;
    /**
     * @var \BB\Repo\PaymentRepository
     */
    private $paymentRepository;
    /**
     * @var \BB\Services\LargeProject
     */
    private $memberStorage;

    public function __construct(\BB\Repo\LargeProjectRepository $LargeProjectRepository, \BB\Repo\PaymentRepository $paymentRepository, \BB\Services\LargeProject $memberStorage)
    {
        $this->LargeProjectRepository = $LargeProjectRepository;
        $this->paymentRepository = $paymentRepository;
        $this->memberStorage = $memberStorage;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $storageBoxes = $this->LargeProjectRepository->getAll();

        $availableBoxes = $this->LargeProjectRepository->numAvailableBoxes();

        //Setup the member storage object
        $this->memberStorage->setMember(\Auth::user()->id);
        
        $volumeAvailable = $this->memberStorage->volumeAvailable();
        $memberBoxes = $this->memberStorage->getMemberBoxes();

        //Work out how much the user has paid
        $boxPayments = $this->memberStorage->getBoxPayments();


        $paymentTotal = $this->memberStorage->getPaymentTotal();
        $boxesTaken = $this->memberStorage->getNumBoxesTaken();
        $moneyAvailable = $this->memberStorage->getMoneyAvailable();


        //Can we accept more money from them
        $canPayMore = false;
        if (($volumeAvailable >= 4) && ($moneyAvailable <= 0)) {
            $canPayMore = true;
        }


        return \View::make('projects_storage.index')
            ->with('storageBoxes', $storageBoxes)
            ->with('boxPayments', $boxPayments)
            ->with('availableBoxes', $availableBoxes)
            ->with('memberBoxes', $memberBoxes)
            ->with('volumeAvailable', $volumeAvailable)
            ->with('paymentTotal', $paymentTotal)
            ->with('boxesTaken', $boxesTaken)
            ->with('canPayMore', $canPayMore)
            ->with('moneyAvailable', $moneyAvailable);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        //
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param $boxId
     * @throws \BB\Exceptions\AuthenticationException
     * @throws \BB\Exceptions\ValidationException
     * @internal param int $id
     * @return Response
     */
    public function update($boxId)
    {
        $userId = \Request::get('user_id');

        if ($userId) {
            $this->selfClaimBox($boxId, $userId);
        } else {
            $box = $this->LargeProjectRepository->getById($boxId);
            if ($box->user_id == \Auth::user()->id) {
                //User is returning their own box
            } else {
                //No id - reclaiming the box
                if ( ! \Auth::user()->hasRole('storage')) {
                    throw new \BB\Exceptions\AuthenticationException();
                }
            }
            $this->LargeProjectRepository->update($boxId, ['user_id'=>0]);
        }

        \Notification::success("Claimed");
        return \Redirect::route('projects_storage.index');
    }

    private function selfClaimBox($boxId, $userId)
    {
        if ($userId != \Auth::user()->id) {
            throw new \BB\Exceptions\AuthenticationException();
        }

        $box = $this->LargeProjectRepository->getById($boxId);

        //Make sure the box is available
        if ( ! $box->available) {
            throw new \BB\Exceptions\ValidationException();
        }

        //Does the user have a box
       // $this->memberStorage->setMember(\Auth::user()->id);

        //$volumeAvailable = $this->memberStorage->volumeAvailable();
        //if ($volumeAvailable < $box->size) {
       //     throw new \BB\Exceptions\ValidationException("You have reached your storage limit");
       // }

        //Have the paid for a box
       // if ($this->memberStorage->getRemainingBoxesPaidFor() <= 0) {
       //     throw new \BB\Exceptions\ValidationException("You need to pay the deposit first");
       // }
       
        
        $this->LargeProjectRepository->update($boxId, ['user_id'=>\Auth::user()->id]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }


}
