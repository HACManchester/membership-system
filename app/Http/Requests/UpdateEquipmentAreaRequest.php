<?php

namespace BB\Http\Requests;

use BB\Entities\EquipmentArea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentAreaRequest extends StoreEquipmentAreaRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $equipmentArea = $this->route('equipment_area');
        return $this->user()->can('update', $equipmentArea);
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $equipmentArea = $this->route('equipment_area');

        return array_merge(
            parent::rules(),
            [
                'name' => ['required', Rule::unique('equipment_areas')->ignore($equipmentArea->id)],
                'slug' => ['required', Rule::unique('equipment_areas')->ignore($equipmentArea->id)],
            ]
        );
    }
}
