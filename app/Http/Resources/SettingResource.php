<?php

namespace BB\Http\Resources;

use BB\Support\SettingKey;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Only for the admin settings page: `value` is the secret itself.
 *
 * @mixin \BB\Entities\Settings
 */
class SettingResource extends JsonResource
{
    public function toArray($request)
    {
        $settingKey = SettingKey::from($this->key);

        return [
            'key' => $this->key,
            'label' => $settingKey->label(),
            'description' => $settingKey->description(),
            // A known setting whose row is missing is listed so it can be set.
            'value' => $this->exists ? $this->value : null,
            'updated_by' => $this->updatedBy?->name,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'update_url' => route('settings.update', $settingKey, false),
        ];
    }
}
