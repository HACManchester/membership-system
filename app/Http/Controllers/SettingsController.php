<?php

namespace BB\Http\Controllers;

use BB\Entities\Settings;
use BB\Events\SettingUpdated;
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
            $admin = CurrentUser::get();
            $setting->updated_by = $admin->id;
            $setting->save();

            event(new SettingUpdated($settingKey, $admin));
        }

        return redirect()
            ->route('settings.index')
            ->with('success', $settingKey->label() . ' updated.');
    }
}
