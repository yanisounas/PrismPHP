<?php

return [
    'services' => [
        'PrismPHP\\' => ['resource' => 'src-app']
    ],

    'parameters' => [
        'example' => 'bar'
    ],

    'dic_settings' => [
        'autowire' => true,
        'attributes' => true,
        'compilation' => false,
    ],
];