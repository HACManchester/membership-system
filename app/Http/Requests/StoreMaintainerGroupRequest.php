<?php

namespace BB\Http\Requests;

use BB\Entities\MaintainerGroup;
use Illuminate\Foundation\Http\FormRequest;

class StoreMaintainerGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', MaintainerGroup::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'unique:maintainer_groups'],
            'slug' => ['required', 'unique:maintainer_groups'],
            'description' => [],
            'equipment_area_id' => ['required', 'integer', 'exists:equipment_areas,id'],
            'maintainers' => ['array', 'exists:users,id'],
        ];
    }
}
