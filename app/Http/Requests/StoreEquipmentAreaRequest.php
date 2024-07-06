<?php

namespace BB\Http\Requests;

use BB\Entities\EquipmentArea;
use Illuminate\Foundation\Http\FormRequest;

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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'unique:equipment_areas'],
            'slug' => ['required', 'unique:equipment_areas'],
            'description' => [],
            'area_coordinators' => ['array', 'exists:users,id'],
        ];
    }
}
