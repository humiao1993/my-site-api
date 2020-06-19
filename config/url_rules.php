<?php

$base = [
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/account',
        'extraPatterns' => [
            'POST login' => 'login',
            'PUT logout' => 'logout'
        ],
        'pluralize' => false,
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/article',
        'extraPatterns' => [],
        'pluralize' => false,
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/author',
        'extraPatterns' => [],
        'pluralize' => false,
    ],

];

return array_merge($base);