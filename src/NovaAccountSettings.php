<?php

namespace Outl1ne\NovaAccountSettings;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\Password;

class NovaAccountSettings extends Tool
{
    /**
     * Perform any tasks that need to happen on tool registration.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('nova-account-settings', __DIR__ . '/../dist/js/entry.js');

        if (!static::doesPathExist('account-settings')) {
            \Outl1ne\NovaAccountSettings\NovaAccountSettings::addAccountSettingsPage([
                Gravatar::make('Avatar', 'avatar')->maxWidth(50)->readonly(),
                Text::make('Name', 'name')->updateRules('required'),
                Email::make('Email', 'email')->updateRules('required', 'email'),
            ]);
        }

        if (!static::doesPathExist('change-password')) {
            \Outl1ne\NovaAccountSettings\NovaAccountSettings::addAccountSettingsPage([
                Password::make('New password', 'password')->updateRules('required', 'string', 'min:8'),
            ], [], 'change-password');
        }
    }

    public function menu(Request $request)
    {
        return null;
    }

    public static function getAuthorizations($key = null)
    {
        $request = request();
        $fakeResource = new \Outl1ne\NovaAccountSettings\Nova\Resources\AccountSettings(NovaAccountSettings::getAccountSettingsModel()::make());

        $authorizations = [
            'authorizedToView' => $fakeResource->authorizedToView($request),
            'authorizedToCreate' => $fakeResource->authorizedToCreate($request),
            'authorizedToUpdate' => $fakeResource->authorizedToUpdate($request),
            'authorizedToDelete' => $fakeResource->authorizedToDelete($request),
        ];

        return $key ? $authorizations[$key] : $authorizations;
    }

    public static function canSeeSettings()
    {
        $auths = static::getAuthorizations();
        return $auths['authorizedToView'] || $auths['authorizedToUpdate'];
    }

    public static function getAccountSettingsModel(): string
    {
        return config('nova-account-settings.models.user');
    }

    public static function getAccount()
    {
        return static::getStore()->getAccount();
    }

    public static function getPageName($key): string
    {
        if (__("novaSettings.$key") === "novaSettings.$key") {
            return Str::title(str_replace('-', ' ', $key));
        } else {
            return __("novaSettings.$key");
        }
    }

    /**
     * @param array|callable $fields Array of fields/panels to be displayed or callable that returns an array.
     * @param array $casts Associative array same as Laravel's $casts on models.
     **/
    public static function addAccountSettingsPage($fields = [], $casts = [], $path = 'account-settings')
    {
        return static::getStore()->addAccountSettingsPage($fields, $casts, $path);
    }

    /**
     * Define casts.
     *
     * @param array $casts Casts same as Laravel's casts on a model.
     **/
    public static function addCasts($casts = [])
    {
        return static::getStore()->addCasts($casts);
    }

    public static function getFields($path = null)
    {
        if (!$path) return static::getStore()->getRawFields();
        return static::getStore()->getFields($path);
    }

    public static function clearFields()
    {
        return static::getStore()->clearFields();
    }

    public static function getCasts()
    {
        return static::getStore()->getCasts();
    }

    public static function getSetting($settingKey, $default = null)
    {
        return static::getStore()->getSetting($settingKey, $default);
    }

    public static function getSettings(array $settingKeys = null, array $defaults = [])
    {
        return static::getStore()->getSettings($settingKeys, $defaults);
    }

    public static function setSettingValue($settingKey, $value = null)
    {
        return static::getStore()->setSettingValue($settingKey, $value);
    }

    public static function doesPathExist($path)
    {
        return array_key_exists($path, static::getFields());
    }

    public static function getStore(): NovaAccountSettingsStore
    {
        return app()->make(NovaAccountSettingsStore::class);
    }
}
