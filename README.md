# Nova Account Settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/outl1ne/nova-account-settings.svg?style=flat-square)](https://packagist.org/packages/outl1ne/nova-account-settings)
[![Total Downloads](https://img.shields.io/packagist/dt/outl1ne/nova-account-settings.svg?style=flat-square)](https://packagist.org/packages/outl1ne/nova-account-settings)

This [Laravel Nova](https://nova.laravel.com) package creates you account settings views.

## Screenshots

// TODO
![Settings View](docs/index.png)

## Installation

```
composer require outl1ne/nova-account-settings
```

Register the tool with Nova in the `tools()` method of the `NovaServiceProvider`:

```php
// in app/Providers/NovaServiceProvider.php

public function tools()
{
    return [
        // ...
        \Outl1ne\NovaAccountSettings\NovaAccountSettings::make(),
    ];
}
```

## Usage / Customisation

Add menu items to the preferred location in user menu.

```php
Nova::userMenu(function (Request $request, Menu $menu) {
    $menu->append([
        NovaAccountSettingsUserMenu::make('Account settings', 'account-settings'),
        NovaAccountSettingsUserMenu::make('Change password', 'change-password'),
    ]);
    return $menu;
});
```

## Credits

- [outl1ne](https://github.com/outl1ne)

## License

Nova Account Settings is open-sourced software licensed under the [MIT license](LICENSE.md).
