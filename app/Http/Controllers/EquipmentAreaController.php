<?php

namespace BB\Http\Controllers;

use BB\Entities\EquipmentArea;
use BB\Http\Requests\StoreEquipmentAreaRequest;
use BB\Http\Requests\UpdateEquipmentAreaRequest;
use BB\Http\Resources\EquipmentAreaResource;
use FlashNotification;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EquipmentAreaController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(EquipmentArea::class, 'equipment_area');
    }

    public function index()
    {
        $areas = EquipmentArea::with('areaCoordinators.profile')->orderBy('name')->get();

        return Inertia::render('EquipmentAreas/Index', [
            'areas' => EquipmentAreaResource::collection($areas),
            'can' => [
                'create' => auth()->user()->can('create', EquipmentArea::class),
            ],
            'urls' => [
                'create' => route('equipment_area.create', [], false),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('EquipmentAreas/Create', [
            'urls' => [
                'index' => route('equipment_area.index', [], false),
                'store' => route('equipment_area.store', [], false),
            ],
            'searchUrl' => route('members.search', [], false),
        ]);
    }

    public function store(StoreEquipmentAreaRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $validated = $request->validated();

            // The description column is NOT NULL; default it when omitted.
            $equipmentArea = EquipmentArea::create(array_merge(['description' => ''], $validated));
            $equipmentArea->areaCoordinators()->sync($validated['area_coordinators'] ?? []);

            FlashNotification::success("Equipment Area, {$equipmentArea->name}, created successfully.");

            return redirect()->route('equipment_area.show', $equipmentArea);
        });
    }

    public function show(EquipmentArea $equipmentArea)
    {
        $equipmentArea->load('areaCoordinators.profile');
        $user = auth()->user();

        return Inertia::render('EquipmentAreas/Show', [
            'area' => new EquipmentAreaResource($equipmentArea),
            'can' => [
                'update' => $user->can('update', $equipmentArea),
                'delete' => $user->can('delete', $equipmentArea),
            ],
            'urls' => [
                'index' => route('equipment_area.index', [], false),
                'edit' => route('equipment_area.edit', $equipmentArea, false),
                'destroy' => route('equipment_area.destroy', $equipmentArea, false),
            ],
        ]);
    }

    public function edit(EquipmentArea $equipmentArea)
    {
        $equipmentArea->load('areaCoordinators.profile');

        return Inertia::render('EquipmentAreas/Edit', [
            'area' => new EquipmentAreaResource($equipmentArea),
            'urls' => [
                'index' => route('equipment_area.index', [], false),
                'show' => route('equipment_area.show', $equipmentArea, false),
                'update' => route('equipment_area.update', $equipmentArea, false),
            ],
            'searchUrl' => route('members.search', [], false),
        ]);
    }

    public function update(UpdateEquipmentAreaRequest $request, EquipmentArea $equipmentArea)
    {
        return DB::transaction(function () use ($request, $equipmentArea) {
            $validated = $request->validated();

            $equipmentArea->update($validated);
            $equipmentArea->areaCoordinators()->sync($validated['area_coordinators'] ?? []);

            FlashNotification::success("Equipment Area, {$equipmentArea->name}, updated successfully.");

            return redirect()->route('equipment_area.show', $equipmentArea);
        });
    }

    public function destroy(EquipmentArea $equipmentArea)
    {
        $equipmentArea->delete();
        FlashNotification::success("Equipment Area, {$equipmentArea->name}, deleted successfully.");

        return redirect()->route('equipment_area.index');
    }
}
