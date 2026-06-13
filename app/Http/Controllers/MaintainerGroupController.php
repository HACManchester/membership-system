<?php

namespace BB\Http\Controllers;

use BB\Entities\EquipmentArea;
use BB\Entities\MaintainerGroup;
use BB\Http\Requests\StoreMaintainerGroupRequest;
use BB\Http\Requests\UpdateMaintainerGroupRequest;
use BB\Repo\UserRepository;
use FlashNotification;

class MaintainerGroupController extends Controller
{
    /** @var UserRepository */
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->authorizeResource(MaintainerGroup::class, 'maintainer_group');
    }

    public function index()
    {
        $maintainerGroups = MaintainerGroup::with('maintainers')->orderBy('name', 'ASC')->get();
        return view('maintainer_groups.index', compact('maintainerGroups'));
    }

    public function create()
    {
        $memberList = $this->userRepository->getAllAsDropdown();
        $equipmentAreaOptions = EquipmentArea::orderBy('name', 'ASC')->pluck('name', 'id');

        return view('maintainer_groups.create', compact('memberList', 'equipmentAreaOptions'));
    }

    public function store(StoreMaintainerGroupRequest $request)
    {
        $maintainerGroup = MaintainerGroup::create($request->all());
        $maintainerGroup->maintainers()->sync($request->input('maintainers'));

        return redirect()->route('maintainer_groups.show', $maintainerGroup);
    }

    public function show(MaintainerGroup $maintainerGroup)
    {
        return view('maintainer_groups.show', compact('maintainerGroup'));
    }

    public function edit(MaintainerGroup $maintainerGroup)
    {
        $memberList = $this->userRepository->getAllAsDropdown();
        $equipmentAreaOptions = EquipmentArea::orderBy('name', 'ASC')->pluck('name', 'id');

        return view('maintainer_groups.edit', compact('maintainerGroup', 'memberList', 'equipmentAreaOptions'));
    }

    public function update(UpdateMaintainerGroupRequest $request, MaintainerGroup $maintainerGroup)
    {
        $maintainerGroup->update($request->all());
        $maintainerGroup->maintainers()->sync($request->input('maintainers'));

        return redirect()->route('maintainer_groups.show', $maintainerGroup);
    }

    public function destroy(MaintainerGroup $maintainerGroup)
    {
        $maintainerGroup->delete();
        FlashNotification::success("Equipment Area, {$maintainerGroup->name}, deleted successfully.");

        return redirect()->route('maintainer_groups.index');
    }
}
