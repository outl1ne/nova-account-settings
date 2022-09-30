<?php

namespace Outl1ne\NovaAccountSettings\Nova\Resources;

use Laravel\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Outl1ne\NovaAccountSettings\NovaAccountSettings;

class AccountSettings extends Resource
{
    public static $title = 'key';
    public static $model = null;
    public static $displayInNavigation = false;

    public function __construct($resource)
    {
        self::$model = NovaAccountSettings::getAccountSettingsModel();
        parent::__construct($resource);
    }

    public function fields(Request $request)
    {
        return [];
    }
}
