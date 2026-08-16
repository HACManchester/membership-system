<?php

namespace BB\Http\Controllers;

use BB\Entities\AccessLockdown;
use BB\Entities\Role;
use BB\Http\Requests\Admin\StoreAccessLockdownRequest;
use BB\Http\Resources\AccessLockdownResource;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AccessLockdownController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', AccessLockdown::class);

        $current = AccessLockdown::current();
        if ($current) {
            $current->load('startedBy');
        }

        $history = AccessLockdown::whereNotNull('lifted_at')
            ->with(['startedBy', 'liftedBy'])
            ->latest('id')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/AccessLockdown/Index', [
            'lockdown' => $current ? new AccessLockdownResource($current) : null,
            'history' => AccessLockdownResource::collection($history),
            'roles' => $this->rolePickerOptions(),
            'defaultRoles' => $this->defaultRoles(),
            'urls' => [
                'store' => route('access-lockdown.store', [], false),
                'destroy' => route('access-lockdown.destroy', [], false),
            ],
        ]);
    }

    public function store(StoreAccessLockdownRequest $request)
    {
        return DB::transaction(function () use ($request) {
            if (AccessLockdown::current()) {
                return redirect()
                    ->route('access-lockdown.index')
                    ->with('error', 'A lockdown is already running. Lift it before starting another.');
            }

            AccessLockdown::create($request->validated() + [
                'started_by' => $request->user()->id,
            ]);

            return redirect()
                ->route('access-lockdown.index')
                ->with(
                    'success',
                    'Space access is now locked down. The door system will pick this up on its next poll.'
                );
        });
    }

    public function destroy()
    {
        $lockdown = AccessLockdown::current();

        if (! $lockdown) {
            $this->authorize('viewAny', AccessLockdown::class);

            return redirect()
                ->route('access-lockdown.index')
                ->with('error', 'There is no lockdown running.');
        }

        $this->authorize('delete', $lockdown);

        // Lift every active row, not just the newest - a stray duplicate would
        // otherwise keep the space shut after the admin thinks they've reopened it.
        AccessLockdown::active()->update([
            'lifted_at' => now(),
            'lifted_by' => auth()->id(),
        ]);

        return redirect()
            ->route('access-lockdown.index')
            ->with('success', 'Lockdown lifted. Full access returns on the door system\'s next poll.');
    }

    /**
     * Inertia ships every prop to the browser whether it's rendered or not, so the
     * picker gets only what it draws - not RoleResource's email addresses.
     *
     * @return array<int, array{id: int, name: string, title: string|null}>
     */
    private function rolePickerOptions(): array
    {
        return Role::orderBy('title')
            ->get()
            ->map(function (Role $role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'title' => $role->title,
                ];
            })
            ->all();
    }

    /**
     * The configured defaults, narrowed to roles that actually exist here. Role
     * rows differ between environments, and a default with no matching checkbox
     * would arrive pre-selected with no way to clear it and then fail validation.
     *
     * @return string[]
     */
    private function defaultRoles(): array
    {
        return Role::whereIn('name', config('membership.access.default_lockdown_roles'))
            ->pluck('name')
            ->all();
    }
}
