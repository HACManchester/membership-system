<?php

namespace BB\Http\Controllers;

use Auth;
use BB\Entities\StorageBox;
use BB\Repo\StorageBoxRepository;
use Illuminate\Http\Request;
use BB\Repo\UserRepository;

class StorageBoxController extends Controller
{
    /** @var StorageBoxRepository */
    protected $storageBoxRepository;

    /** @var UserRepository */
    protected $userRepository;

    public function __construct(StorageBoxRepository $storageBoxRepository, UserRepository $userRepository)
    {
        $this->storageBoxRepository = $storageBoxRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $storageBoxes = $this->storageBoxRepository->getAll();
        $memberBoxes = $this->storageBoxRepository->getMemberBoxes(Auth::user()->id);

        return view('storage_boxes.index', [
            'storageBoxes' => $storageBoxes,
            'memberBoxes' => $memberBoxes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', StorageBox::class);

        \FlashNotification::warning('Creating storage boxes has not been implemented yet.');
        return redirect()->back();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', StorageBox::class);

        \FlashNotification::warning('Creating storage boxes has not been implemented yet.');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  StorageBox  $storageBox
     * @return \Illuminate\Http\Response
     */
    public function show(StorageBox $storageBox)
    {
        $this->authorize('view', $storageBox);

        $memberList = $this->userRepository->getAllAsDropdown();
        $thisURL = urlencode(url('storage_boxes', $storageBox->id));

        // todo: swap with some library rather than calling out to an external service
        $QRcodeURL = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$thisURL}&color=00a";

        return view('storage_boxes.show', [
            'box' => $storageBox,
            'memberList' => $memberList,
            'QRcodeURL' => $QRcodeURL,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  StorageBox  $storageBox
     * @return \Illuminate\Http\Response
     */
    public function edit(StorageBox $storageBox)
    {
        $this->authorize('update', $storageBox);

        \FlashNotification::warning('Editing storage boxes has not been implemented yet.');
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  StorageBox  $storageBox
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StorageBox $storageBox)
    {
        $this->authorize('update', $storageBox);

        $storageBox->update($request->all());

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  StorageBox  $storageBox
     * @return \Illuminate\Http\Response
     */
    public function destroy(StorageBox $storageBox)
    {
        $this->authorize('delete', $storageBox);

        \FlashNotification::warning('Deleting storage boxes has not been implemented yet.');
        return redirect()->back();
    }
}
