<?php

namespace BB\Http\Requests;

use BB\Entities\MaintainerGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

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
            'name' => ['required', 'unique:maintainer_groups'],
            'slug' => ['required', 'alpha_dash', 'unique:maintainer_groups'],
            'description' => ['nullable'],
            'equipment_area_id' => ['required', 'integer', 'exists:equipment_areas,id'],
            'maintainers' => ['nullable', 'array'],
            'maintainers.*' => ['exists:users,id'],
        ];
    }
}
