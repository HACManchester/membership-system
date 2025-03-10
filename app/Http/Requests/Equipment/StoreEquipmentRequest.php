<?php

namespace BB\Http\Requests\Equipment;

use BB\Entities\MaintainerGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', \BB\Entities\Equipment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $canGloballyManage = $this->user()->isAdmin() || $this->user()->hasRole('equipment');

        return [
            'name'                      => 'required',
            'manufacturer'              => '',
            'model_number'              => '',
            'serial_number'             => '',
            'colour'                    => '',
            'room'                      => 'required',
            'detail'                    => '',
            'slug'                      => 'required|alpha_dash|unique:equipment,slug',
            'description'               => '',
            'help_text'                 => '',
            'maintainer_group_id'       => [
                Rule::requiredIf(function () use ($canGloballyManage) {
                    return !$canGloballyManage;
                }),
                'exists:maintainer_groups,id',
                function ($attribute, $value, $fail) use ($canGloballyManage) {
                    if ($canGloballyManage) {
                        return;
                    }

                    $maintainerGroup = MaintainerGroup::find($value);
                    $inMaintainerGroup = $this->user()->maintainerGroups->contains($maintainerGroup);
                    $isAreaCoordinator = $this->user()->equipmentAreas->contains($maintainerGroup->equipmentArea);

                    if (!$inMaintainerGroup && !$isAreaCoordinator) {
                        $fail('You can only create equipment managed by maintainer groups you are in.');
                    }
                }
            ],
            'requires_induction'        => 'boolean',
            'induction_category'        => 'required_if:requires_induction,1|alpha_dash',
            'working'                   => 'boolean',
            'permaloan'                 => 'boolean',
            'dangerous'                 => 'boolean',
            'permaloan_user_id'         => 'exists:users,id|required_if:permaloan,1',
            'access_fee'                => 'integer',
            'usage_cost'                => 'required|numeric',
            'usage_cost_per'            => 'required|in:hour,gram,page',
            'asset_tag_id'              => 'unique:equipment,asset_tag_id',
            'obtained_at'               => 'date_format:Y-m-d|before:tomorrow|nullable',
            'removed_at'                => 'date_format:Y-m-d|before:tomorrow|nullable',
            'induction_instructions'    => '',
            'trainer_instructions'      => '',
            'trained_instructions'      => '',
            'accepting_inductions'      => 'required_if:requires_induction,1',
            'ppe'                       => '',
            'docs'                      => '',
            'access_code'               => '',
        ];
    }
}
