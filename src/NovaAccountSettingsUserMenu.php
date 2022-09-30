<?php

namespace Outl1ne\NovaAccountSettings;

use Laravel\Nova\Menu\MenuItem;

class NovaAccountSettingsUserMenu
{
    public static function make($label, $slug)
    {
        return MenuItem::make($label)
            ->path('nova-account-settings/'.$slug);
    }
}
