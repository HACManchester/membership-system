<?php namespace BB\Http\Controllers;

use BB\Repo\UserRepository;

class StorageBoxController extends Controller
{

    /**
     * @var \BB\Repo\StorageBoxRepository
     */
    private $storageBoxRepository;
    /**
     * @var \BB\Repo\PaymentRepository
     */
    private $paymentRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var \BB\Services\MemberStorage
     */
    private $memberStorage;

    public function __construct(
        \BB\Repo\StorageBoxRepository $storageBoxRepository, 
        \BB\Repo\PaymentRepository $paymentRepository, 
        \BB\Repo\UserRepository $userRepository,
        \BB\Services\MemberStorage $memberStorage
    ){
        $this->storageBoxRepository = $storageBoxRepository;
        $this->paymentRepository = $paymentRepository;
        $this->userRepository = $userRepository;
        $this->memberStorage = $memberStorage;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $storageBoxes = $this->storageBoxRepository->getAll()->sortBy(
            function($post){
                if($post->id < 104) {return 1000 + $post->id;}
                return $post->id;
            }
        );

        $availableBoxes = $this->storageBoxRepository->numAvailableBoxes();

        //Setup the member storage object
        $this->memberStorage->setMember(\Auth::user()->id);
        
        $volumeAvailable = $this->memberStorage->volumeAvailable();
        $memberBoxes = $this->memberStorage->getMemberBoxes();

        //Work out how much the user has paid
        $boxPayments = $this->memberStorage->getBoxPayments();


        $paymentTotal = $this->memberStorage->getPaymentTotal();
        $boxesTaken = $this->memberStorage->getNumBoxesTaken();

        $memberList = $this->userRepository->getAllAsDropdown();

        return \View::make('storage_boxes.index')
            ->with('storageBoxes', $storageBoxes)
            ->with('boxPayments', $boxPayments)
            ->with('availableBoxes', $availableBoxes)
            ->with('memberBoxes', $memberBoxes)
            ->with('volumeAvailable', $volumeAvailable)
            ->with('paymentTotal', $paymentTotal)
            ->with('memberList', $memberList)
            ->with('boxesTaken', $boxesTaken);
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
    public function show($boxId)
    {
        $user = \Auth::user();
        $box = $this->storageBoxRepository->getById($boxId);
        //Setup the member storage object
        $this->memberStorage->setMember(\Auth::user()->id);
        $volumeAvailable = $this->memberStorage->volumeAvailable();
        $memberList = $this->userRepository->getAllAsDropdown();

        $thisURL = urlencode(url()->current());
        $QRcodeURL = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={$thisURL}&color=005";

        return \View::make('storage_boxes.show')
        ->with('user', $user)
        ->with('volumeAvailable', $volumeAvailable)
        ->with('box', $box)
        ->with('memberList', $memberList)
        ->with('QRcodeURL', $QRcodeURL);
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
            $box = $this->storageBoxRepository->getById($boxId);
            if ($box->user_id == \Auth::user()->id) {
                //User is returning their own box
            } else {
                //No id - reclaiming the box
                if ( ! \Auth::user()->hasRole('storage')) {
                    throw new \BB\Exceptions\AuthenticationException();
                }
            }
            $this->storageBoxRepository->update($boxId, ['user_id'=>0]);
        }

        \Notification::success("Member box updated");
        return \Redirect::route('storage_boxes.index');
    }

    private function selfClaimBox($boxId, $userId)
    {
        $permittedUser = \Auth::user()->isAdmin() || Auth::user()->hasRole('storage');

        if ($userId != \Auth::user()->id && !$permittedUser) {
            throw new \BB\Exceptions\AuthenticationException();
        }

        if(\Auth::user()->online_only){
            throw new \BB\Exceptions\AuthenticationException();
        }

        $box = $this->storageBoxRepository->getById($boxId);

        //Make sure the box is available
        if ( !$box->available && !$permittedUser) {
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

        $this->storageBoxRepository->update($boxId, ['user_id'=>\Auth::user()->id]);
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
