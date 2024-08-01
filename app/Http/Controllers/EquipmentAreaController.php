<?php

namespace BB\Http\Controllers;

use BB\Entities\EquipmentArea;
use BB\Http\Requests\StoreEquipmentAreaRequest;
use BB\Http\Requests\UpdateEquipmentAreaRequest;
use BB\Repo\UserRepository;
use FlashNotification;

class EquipmentAreaController extends Controller
{
    /** @var UserRepository */
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->authorizeResource(EquipmentArea::class, 'equipment_area');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $areas = EquipmentArea::with('areaCoordinators')->orderBy('name', 'ASC')->get();
        return view('equipment_areas.index', compact('areas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $memberList = $this->userRepository->getAllAsDropdown();
        return view('equipment_areas.create', compact('memberList'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEquipmentAreaRequest $request)
    {
        $equipmentArea = EquipmentArea::create($request->all());
        $equipmentArea->areaCoordinators()->sync($request->input('area_coordinators'));

        return redirect()->route('equipment_area.show', $equipmentArea);
    }

    /**
     * Display the specified resource.
     *
     * @param  \BB\EquipmentArea  $equipmentArea
     * @return \Illuminate\Http\Response
     */
    public function show(EquipmentArea $equipmentArea)
    {
        return view('equipment_areas.show', compact('equipmentArea'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \BB\EquipmentArea  $equipmentArea
     * @return \Illuminate\Http\Response
     */
    public function edit(EquipmentArea $equipmentArea)
    {
        $memberList = $this->userRepository->getAllAsDropdown();
        return view('equipment_areas.edit', compact('equipmentArea', 'memberList'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \BB\EquipmentArea  $equipmentArea
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEquipmentAreaRequest $request, EquipmentArea $equipmentArea)
    {
        $equipmentArea->update($request->all());
        $equipmentArea->areaCoordinators()->sync($request->input('area_coordinators'));

        return redirect()->route('equipment_area.show', $equipmentArea);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \BB\EquipmentArea  $equipmentArea
     * @return \Illuminate\Http\Response
     */
    public function destroy(EquipmentArea $equipmentArea)
    {
        $equipmentArea->delete();
        FlashNotification::success("Equipment Area, {$equipmentArea->name}, deleted successfully.");

        return redirect()->route('equipment_area.index');
    }
}
