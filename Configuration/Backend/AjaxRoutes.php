<?php

use Exdeliver\ExdeliverWcag\Controller\WcagAnalysisController;

return [
    'exdeliver_wcag_analyze' => [
        'path' => '/exdeliver-wcag/analyze',
        'target' => WcagAnalysisController::class . '::analyzeAction',
    ],
];
