<?php

namespace BB\Http\Requests;

use BB\Entities\MaintainerGroup;
use Illuminate\Validation\Rule;

class UpdateMaintainerGroupRequest extends StoreMaintainerGroupRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        /** @var MaintainerGroup */
        $maintainerGroup = $this->route('maintainer_group');
        return $this->user()->can('update', $maintainerGroup);
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /** @var MaintainerGroup */
        $maintainerGroup = $this->route('maintainer_group');

        return array_merge(
            parent::rules(),
            [
                'name' => ['required', Rule::unique('maintainer_groups')->ignore($maintainerGroup->id)],
                'slug' => ['required', Rule::unique('maintainer_groups')->ignore($maintainerGroup->id)],
            ]
        );
    }
}
