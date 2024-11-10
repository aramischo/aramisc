<?php

return [
    'item_id' => '101101',
    'module_manager_model' => App\AramiscModuleManager::class,
    'module_manager_table' => 'aramisc_module_managers',

    'settings_model' => App\AramiscGeneralSettings::class,
    'module_model' => Nwidart\Modules\Facades\Module::class,

    'user_model' => App\User::class,
    'settings_table' => 'aramisc_general_settings',
    'database_file' => 'aramiscduV6.sql',
];
