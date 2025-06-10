<?php
declare(strict_types=1);

return [
    'providers' => [
        PrismPHP\Logging\Provider\LoggerServiceProvider::class,
    ],

    'services' => [
    ],

    'parameters' => [
        /*'kernel.environment' => '%env:APP_ENV%',
        'kernel.debug' => '%env:APP_DEBUG%',
        'kernel.project_dir' => '%runtime%',
        'kernel.config_dir' => '%runtime%',
        'kernel.cache_dir' => '%runtime%',
        'kernel.logs_dir' => '%runtime%',
        'kernel.public_dir' => '%runtime%',
        'kernel.tmp_dir' => '%runtime%',

        'app.name' => '%env:APP_NAME%',
        'app.secret' => '%env:APP_SECRET%',

        'database.url' => '%env:DATABASE_URL%',

        'template.path' => '%runtime%',*/
    ],

    'dic_settings' => [
        'autowire' => true,
        'attributes' => true,
        'compilation' => false,
    ],
];