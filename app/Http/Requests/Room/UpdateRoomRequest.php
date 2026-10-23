<?php

namespace BB\Http\Requests\Room;

use BB\Support\CurrentUser;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends StoreRoomRequest
{
    public function authorize()
    {
        return CurrentUser::get()->can('update', $this->route('room'));
    }

    public function rules()
    {
        /** @var \BB\Entities\Room $room */
        $room = $this->route('room');

        return array_merge(parent::rules(), [
            'name' => ['required', Rule::unique('rooms', 'name')->ignore($room->id)],
            'slug' => ['required', 'alpha_dash', Rule::unique('rooms', 'slug')->ignore($room->id)],
        ]);
    }
}
