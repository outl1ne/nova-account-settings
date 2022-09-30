<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::namespace('\Outl1ne\NovaAccountSettings\Http\Controllers')->group(function () {
    Route::prefix('nova-vendor/nova-account-settings')->group(function () {
        Route::get('/fields', 'AccountSettingsController@get')->name('nova-account-settings.fields');
        Route::post('/', 'AccountSettingsController@save')->name('nova-account-settings.save');
    });
});
