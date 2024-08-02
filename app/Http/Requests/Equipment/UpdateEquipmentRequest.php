<?php

namespace BB\Http\Requests\Equipment;

use BB\Entities\Equipment;
use Illuminate\Validation\Rule;

class UpdateEquipmentRequest extends StoreEquipmentRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $equipment = $this->route('equipment');
        return $this->user()->can('update', $equipment);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /** @var Equipment */
        $equipment = $this->route('equipment');

        return array_merge(
            parent::rules(),
            [
                'slug' => ['required', Rule::unique('equipment')->ignore($equipment->id)],
                'asset_tag_id' => [Rule::unique('equipment')->ignore($equipment->id)],
            ]
        );
    }
}
