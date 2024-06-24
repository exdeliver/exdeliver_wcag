<?php

return [
    'web_ExdeliverWcag' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/web/ExdeliverWcag',
        'labels' => 'LLL:EXT:exdeliver_wcag/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'ExdeliverWcag',
        'controllerActions' => [
            \Exdeliver\ExdeliverWcag\Controller\WcagAnalysisController::class => [
                'list', 'analyze'
            ],
        ],
    ],
];