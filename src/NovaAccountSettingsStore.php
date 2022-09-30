<?php

namespace Outl1ne\NovaAccountSettings;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class NovaAccountSettingsStore
{
    protected $cache = [];
    protected $fields = [];
    protected $casts = [];

    public function addAccountSettingsPage($fields = [], $casts = [], $path)
    {
        $path = Str::lower(Str::slug($path));

        if (is_callable($fields)) $fields = [$fields];
        $this->fields[$path] = $fields ?? [];

        $this->casts = $casts ?? [];

        return $this;
    }

    public function addCasts($casts = [])
    {
        $this->casts = array_merge($this->casts, $casts);
        return $this;
    }

    public function getRawFields()
    {
        return $this->fields;
    }

    public function getFields($path)
    {
        $rawFields = array_map(function ($fieldItem) {
            return is_callable($fieldItem) ? call_user_func($fieldItem) : $fieldItem;
        }, $this->fields[$path] ?? []);

        $fields = [];
        foreach ($rawFields as $rawField) {
            if (is_array($rawField)) $fields = array_merge($fields, $rawField);
            else $fields[] = $rawField;
        }

        return $fields;
    }

    public function getCasts()
    {
        return $this->casts;
    }

    public function getSetting($settingKey, $default = null)
    {
        if (isset($this->cache[$settingKey])) return $this->cache[$settingKey];
        $this->cache[$settingKey] = NovaAccountSettings::getAccountSettingsModel()::getValueForKey($settingKey) ?? $default;
        return $this->cache[$settingKey];
    }

    public function getSettings(array $settingKeys = null, array $defaults = [])
    {
        $settingsModel = NovaAccountSettings::getAccountSettingsModel();

        if (!empty($settingKeys)) {
            $hasMissingKeys = !empty(array_diff($settingKeys, array_keys($this->cache)));

            if (!$hasMissingKeys) return collect($settingKeys)->mapWithKeys(function ($settingKey) {
                return [$settingKey => $this->cache[$settingKey]];
            })->toArray();

            $settings = $settingsModel::find($settingKeys)->pluck('value', 'key');

            return collect($settingKeys)->flatMap(function ($settingKey) use ($settings, $defaults) {
                $settingValue = $settings[$settingKey] ?? null;

                if (isset($settingValue)) {
                    $this->cache[$settingKey] = $settingValue;
                    return [$settingKey => $settingValue];
                } else {
                    $defaultValue = $defaults[$settingKey] ?? null;
                    return [$settingKey => $defaultValue];
                }
            })->toArray();
        }

        return $settingsModel::all()->map(function ($setting) {
            $this->cache[$setting->key] = $setting->value;
            return $setting;
        })->pluck('value', 'key')->toArray();
    }

    public function setSettingValue($settingKey, $value = null)
    {
        $setting = NovaAccountSettings::getAccountSettingsModel()::firstOrCreate(['key' => $settingKey]);
        $setting->value = $value;
        $setting->save();
        unset($this->cache[$settingKey]);
        return $setting;
    }

    public function clearCache($keyNames = null)
    {
        // Clear whole cache
        if (empty($keyNames)) {
            $this->cache = [];
            return;
        }

        // Clear specific keys
        if (is_string($keyNames)) $keyNames = [$keyNames];
        foreach ($keyNames as $key) {
            unset($this->cache[$key]);
        }
    }

    public function clearFields()
    {
        $this->fields = [];
        $this->casts = [];
        $this->cache = [];
    }

    public function getAccount()
    {
        return Auth::user();
    }
}
