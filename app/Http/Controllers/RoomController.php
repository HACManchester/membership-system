<?php

namespace BB\Http\Controllers;

use BB\Entities\Room;
use BB\Http\Requests\Room\StoreRoomRequest;
use BB\Http\Requests\Room\UpdateRoomRequest;
use BB\Http\Resources\RoomResource;
use FlashNotification;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Room::class, 'room');
    }

    public function index()
    {
        $rooms = Room::withCount('equipment')
            ->orderBy('name')
            ->get();

        return Inertia::render('Rooms/Index', [
            'rooms' => RoomResource::collection($rooms),
            'can' => [
                'create' => auth()->user()->can('create', Room::class),
            ],
            'urls' => [
                'create' => route('room.create', [], false),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Rooms/Create', [
            'urls' => [
                'index' => route('room.index', [], false),
                'store' => route('room.store', [], false),
            ],
        ]);
    }

    public function store(StoreRoomRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $room = Room::create($request->validated());

            FlashNotification::success("Room, {$room->name}, created successfully.");

            return redirect()->route('room.show', $room);
        });
    }

    public function show(Room $room)
    {
        $room->loadCount('equipment');
        $user = auth()->user();

        return Inertia::render('Rooms/Show', [
            'room' => new RoomResource($room),
            'can' => [
                'update' => $user->can('update', $room),
                'delete' => $user->can('delete', $room),
            ],
            'urls' => [
                'index' => route('room.index', [], false),
                'edit' => route('room.edit', $room, false),
                'destroy' => route('room.destroy', $room, false),
            ],
        ]);
    }

    public function edit(Room $room)
    {
        return Inertia::render('Rooms/Edit', [
            'room' => new RoomResource($room),
            'urls' => [
                'index' => route('room.index', [], false),
                'show' => route('room.show', $room, false),
                'update' => route('room.update', $room, false),
            ],
        ]);
    }

    public function update(UpdateRoomRequest $request, Room $room)
    {
        return DB::transaction(function () use ($request, $room) {
            $room->update($request->validated());

            FlashNotification::success("Room, {$room->name}, updated successfully.");

            return redirect()->route('room.show', $room);
        });
    }

    public function destroy(Room $room)
    {
        if ($room->equipment()->exists()) {
            FlashNotification::error(
                "Can't delete {$room->name} while equipment is still assigned to it. " .
                'Reassign that equipment to another room first.'
            );

            return redirect()->route('room.show', $room);
        }

        $room->delete();
        FlashNotification::success("Room, {$room->name}, deleted successfully.");

        return redirect()->route('room.index');
    }
}
