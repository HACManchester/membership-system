<?php

namespace BB\Http\Controllers;

use BB\Entities\Settings;
use BB\Http\Requests\Admin\UpdateSettingRequest;
use BB\Http\Resources\SettingResource;
use BB\Support\CurrentUser;
use BB\Support\SettingKey;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Settings::class);

        $rows = Settings::with('updatedBy')
            ->whereIn('key', array_column(SettingKey::cases(), 'value'))
            ->get()
            ->keyBy('key');

        $settings = collect(SettingKey::cases())->map(
            fn (SettingKey $settingKey) => $rows->get($settingKey->value) ?? new Settings(['key' => $settingKey->value])
        );

        return Inertia::render('Admin/Settings/Index', [
            'settings' => SettingResource::collection($settings),
        ]);
    }

    public function update(UpdateSettingRequest $request, SettingKey $settingKey)
    {
        $setting = Settings::findOrNew($settingKey->value);
        $setting->key = $settingKey->value;
        $setting->value = $request->validated('setting_value');

        if ($setting->isDirty('value')) {
            $setting->updated_by = CurrentUser::get()->id;
            $setting->save();
        }

        return redirect()
            ->route('settings.index')
            ->with('success', $settingKey->label() . ' updated.');
    }
}
