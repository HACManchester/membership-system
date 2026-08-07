<?php

namespace BB\Http\Controllers;

use BB\Entities\Role;
use BB\Http\Requests\Role\UpdateRoleRequest;
use BB\Http\Resources\RoleResource;
use FlashNotification;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Role::class, 'role');
    }

    public function index()
    {
        $roles = Role::withCount('users')->orderBy('title')->get();

        return Inertia::render('Roles/Index', [
            'roles' => RoleResource::collection($roles),
        ]);
    }

    public function edit(Role $role)
    {
        $role->load('users');

        return Inertia::render('Roles/Edit', [
            'role' => new RoleResource($role),
            'searchUrl' => route('members.search', [], false),
            'urls' => [
                'index' => route('roles.index', [], false),
                'update' => route('roles.update', $role->id, false),
            ],
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        return DB::transaction(function () use ($request, $role) {
            $validated = $request->validated();

            // title is NOT NULL; default all text fields to '' rather than null so
            // an unset/empty field never violates a not-null column.
            $role->update([
                'title' => $validated['title'] ?? '',
                'description' => $validated['description'] ?? '',
                'email_public' => $validated['email_public'] ?? '',
                'email_private' => $validated['email_private'] ?? '',
                'slack_channel' => $validated['slack_channel'] ?? '',
            ]);

            $role->users()->sync($validated['members'] ?? []);

            FlashNotification::success("Role, {$role->title}, updated successfully.");

            // Back to the list so the change is clearly reflected (and the success
            // flash is visible) rather than sitting on the same edit form.
            return redirect()->route('roles.index');
        });
    }
}
