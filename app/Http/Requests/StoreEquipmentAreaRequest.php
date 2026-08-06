<?php

namespace BB\Http\Requests;

use BB\Entities\EquipmentArea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreEquipmentAreaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', EquipmentArea::class);
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'unique:equipment_areas'],
            'slug' => ['required', 'alpha_dash', 'unique:equipment_areas'],
            'description' => ['nullable'],
            'area_coordinators' => ['nullable', 'array'],
            'area_coordinators.*' => ['exists:users,id'],
        ];
    }
}
