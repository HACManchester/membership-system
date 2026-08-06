<?php

namespace BB\Http\Controllers;

use BB\Entities\EquipmentArea;
use BB\Entities\MaintainerGroup;
use BB\Http\Requests\StoreMaintainerGroupRequest;
use BB\Http\Requests\UpdateMaintainerGroupRequest;
use BB\Http\Resources\MaintainerGroupResource;
use FlashNotification;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MaintainerGroupController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(MaintainerGroup::class, 'maintainer_group');
    }

    public function index()
    {
        $maintainerGroups = MaintainerGroup::with('maintainers.profile', 'equipmentArea')
            ->withCount('equipment')
            ->orderBy('name')
            ->get();

        return Inertia::render('MaintainerGroups/Index', [
            'maintainerGroups' => MaintainerGroupResource::collection($maintainerGroups),
            'can' => [
                'create' => auth()->user()->can('create', MaintainerGroup::class),
            ],
            'urls' => [
                'create' => route('maintainer_groups.create', [], false),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('MaintainerGroups/Create', [
            'equipmentAreaOptions' => $this->areaOptions(),
            'urls' => [
                'index' => route('maintainer_groups.index', [], false),
                'store' => route('maintainer_groups.store', [], false),
            ],
            'searchUrl' => route('members.search', [], false),
        ]);
    }

    public function store(StoreMaintainerGroupRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $validated = $request->validated();

            // The description column is NOT NULL; default it when omitted.
            $maintainerGroup = MaintainerGroup::create(array_merge(['description' => ''], $validated));
            $maintainerGroup->maintainers()->sync($validated['maintainers'] ?? []);

            FlashNotification::success("Maintainer Group, {$maintainerGroup->name}, created successfully.");

            return redirect()->route('maintainer_groups.show', $maintainerGroup);
        });
    }

    public function show(MaintainerGroup $maintainerGroup)
    {
        $maintainerGroup->load('maintainers.profile', 'equipmentArea', 'equipment');
        $user = auth()->user();

        return Inertia::render('MaintainerGroups/Show', [
            'maintainerGroup' => new MaintainerGroupResource($maintainerGroup),
            'can' => [
                'update' => $user->can('update', $maintainerGroup),
                'delete' => $user->can('delete', $maintainerGroup),
            ],
            'urls' => [
                'index' => route('maintainer_groups.index', [], false),
                'edit' => route('maintainer_groups.edit', $maintainerGroup, false),
                'destroy' => route('maintainer_groups.destroy', $maintainerGroup, false),
            ],
        ]);
    }

    public function edit(MaintainerGroup $maintainerGroup)
    {
        $maintainerGroup->load('maintainers.profile', 'equipmentArea');

        return Inertia::render('MaintainerGroups/Edit', [
            'maintainerGroup' => new MaintainerGroupResource($maintainerGroup),
            'equipmentAreaOptions' => $this->areaOptions(),
            'urls' => [
                'index' => route('maintainer_groups.index', [], false),
                'show' => route('maintainer_groups.show', $maintainerGroup, false),
                'update' => route('maintainer_groups.update', $maintainerGroup, false),
            ],
            'searchUrl' => route('members.search', [], false),
        ]);
    }

    public function update(UpdateMaintainerGroupRequest $request, MaintainerGroup $maintainerGroup)
    {
        return DB::transaction(function () use ($request, $maintainerGroup) {
            $validated = $request->validated();

            $maintainerGroup->update($validated);
            $maintainerGroup->maintainers()->sync($validated['maintainers'] ?? []);

            FlashNotification::success("Maintainer Group, {$maintainerGroup->name}, updated successfully.");

            return redirect()->route('maintainer_groups.show', $maintainerGroup);
        });
    }

    public function destroy(MaintainerGroup $maintainerGroup)
    {
        $maintainerGroup->delete();
        FlashNotification::success("Maintainer Group, {$maintainerGroup->name}, deleted successfully.");

        return redirect()->route('maintainer_groups.index');
    }

    /**
     * The equipment areas offered in the group's area selector.
     *
     * @return array<int, array{id: int, name: string}>
     */
    private function areaOptions(): array
    {
        return EquipmentArea::orderBy('name')->get()->map(function ($area) {
            return ['id' => $area->id, 'name' => $area->name];
        })->all();
    }
}
