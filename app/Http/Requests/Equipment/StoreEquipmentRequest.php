<?php

namespace BB\Http\Requests\Equipment;

use BB\Entities\MaintainerGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
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
     * Derive a slug from the name when one isn't supplied.
     */
    protected function prepareForValidation()
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }

        // The Inertia form always submits these optional foreign keys, so a blank
        // selection arrives as '' and would otherwise be written to the database
        // (a non-existent id / FK violation). Normalise blanks to null.
        foreach (['maintainer_group_id', 'permaloan_user_id'] as $key) {
            if ($this->input($key) === '') {
                $this->merge([$key => null]);
            }
        }

        // An item that isn't on permaloan can't have a holder, so drop any stale
        // value (e.g. a former holder who has since left) — otherwise it would
        // fail the exists check on a hidden field, or be stored inconsistently.
        if (! $this->boolean('permaloan')) {
            $this->merge(['permaloan_user_id' => null]);
        }
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
            'room_id'                   => 'required|exists:rooms,id',
            'detail'                    => '',
            'slug'                      => 'required|alpha_dash|unique:equipment,slug',
            'description'               => '',
            'help_text'                 => '',
            'maintainer_group_id'       => [
                'nullable',
                Rule::requiredIf(function () use ($canGloballyManage) {
                    return !$canGloballyManage;
                }),
                'exists:maintainer_groups,id',
                function ($attribute, $value, $fail) use ($canGloballyManage) {
                    if ($canGloballyManage) {
                        return;
                    }

                    $maintainerGroup = MaintainerGroup::find($value);
                    if (! $maintainerGroup instanceof MaintainerGroup) {
                        // A non-existent group is already reported by the exists rule.
                        return;
                    }
                    $inMaintainerGroup = $this->user()->maintainerGroups->contains($maintainerGroup);
                    $isAreaCoordinator = $this->user()->equipmentAreas->contains($maintainerGroup->equipmentArea);

                    if (!$inMaintainerGroup && !$isAreaCoordinator) {
                        $fail('You can only create equipment managed by maintainer groups you are in.');
                    }
                }
            ],
            'requires_induction'        => 'boolean',
            // Legacy, always-shown, deprecated field — kept lenient so it can never
            // block a save; the induction course above is the supported path.
            'induction_category'        => 'nullable',
            'working'                   => 'boolean',
            'permaloan'                 => 'boolean',
            'dangerous'                 => 'boolean',
            'permaloan_user_id'         => 'nullable|exists:users,id|required_if:permaloan,1',
            'access_fee'                => 'nullable|integer',
            'usage_cost'                => 'nullable|numeric',
            'usage_cost_per'            => 'nullable|in:hour,gram,page',
            'admin_notes'               => 'nullable',
            'obtained_at'               => 'date_format:Y-m-d|before:tomorrow|nullable',
            'removed_at'                => 'date_format:Y-m-d|before:tomorrow|nullable',
            'induction_instructions'    => '',
            'trainer_instructions'      => '',
            'trained_instructions'      => '',
            'accepting_inductions'      => 'nullable|boolean',
            'ppe'                       => '',
            'docs'                      => '',
            'access_code'               => '',
            'lone_working'              => 'boolean',
        ];
    }
}
