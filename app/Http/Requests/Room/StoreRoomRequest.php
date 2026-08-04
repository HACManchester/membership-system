<?php

namespace BB\Http\Requests\Room;

use BB\Entities\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreRoomRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', Room::class);
    }

    /**
     * Derive a slug from the name when one isn't provided.
     */
    protected function prepareForValidation()
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }

    public function rules()
    {
        return [
            'name' => ['required', 'unique:rooms,name'],
            'slug' => ['required', 'alpha_dash', 'unique:rooms,slug'],
            'description' => ['nullable'],
        ];
    }
}
