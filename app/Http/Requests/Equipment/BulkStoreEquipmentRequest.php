<?php

namespace BB\Http\Requests\Equipment;

use BB\Entities\Equipment;
use BB\Entities\MaintainerGroup;
use BB\Support\CurrentUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BulkStoreEquipmentRequest extends FormRequest
{
    public function authorize()
    {
        return CurrentUser::get()->can('create', Equipment::class);
    }

    /**
     * Fill in per-row slugs from names, and normalise an empty course to null.
     */
    protected function prepareForValidation()
    {
        $items = $this->input('items', []);

        if (is_array($items)) {
            foreach ($items as $i => $item) {
                if (empty($item['slug'] ?? null) && ! empty($item['name'] ?? null)) {
                    $items[$i]['slug'] = Str::slug($item['name']);
                }
            }
            $this->merge(['items' => $items]);
        }

        foreach (['course_id', 'maintainer_group_id'] as $key) {
            if ($this->input($key) === '') {
                $this->merge([$key => null]);
            }
        }
    }

    public function rules()
    {
        $canGloballyManage = CurrentUser::get()->isAdmin() || CurrentUser::get()->hasRole('equipment');

        return [
            'room_id' => 'required|exists:rooms,id',
            'course_id' => 'nullable|exists:courses,id',
            'maintainer_group_id' => [
                'nullable',
                Rule::requiredIf(function () use ($canGloballyManage) {
                    return ! $canGloballyManage;
                }),
                'exists:maintainer_groups,id',
                function ($attribute, $value, $fail) use ($canGloballyManage) {
                    if ($canGloballyManage) {
                        return;
                    }

                    $maintainerGroup = MaintainerGroup::find($value);
                    if (! $maintainerGroup instanceof MaintainerGroup) {
                        return;
                    }
                    $inMaintainerGroup = CurrentUser::get()->maintainerGroups->contains($maintainerGroup);
                    $isAreaCoordinator = $maintainerGroup->equipmentArea !== null
                        && CurrentUser::get()->equipmentAreas->contains($maintainerGroup->equipmentArea);

                    if (! $inMaintainerGroup && ! $isAreaCoordinator) {
                        $fail('You can only create equipment managed by maintainer groups you are in.');
                    }
                },
            ],
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.slug' => 'required|alpha_dash|distinct|unique:equipment,slug',
        ];
    }
}
