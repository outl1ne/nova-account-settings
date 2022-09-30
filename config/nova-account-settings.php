<?php

return [
    /**
     * URL path of settings page
     */
    'base_path' => 'nova-account-settings',

    /**
     * Reload the entire page on save. Useful when updating any Nova UI related settings.
     */
    'reload_page_on_save' => false,

    /**
     * Define a model which will be used in account settings views.
     */
    'models' => [
        'user' => \App\Models\User::class,
    ],
];
