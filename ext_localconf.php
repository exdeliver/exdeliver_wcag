<?php

defined('TYPO3') or die();

(static function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
        '@import "EXT:exdeliver_wcag/Configuration/TypoScript/setup.typoscript"'
    );

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['exdeliver_wcag'] = [
        'ChromeDriverOptions' => [
            'chromeDriverUrl' => 'http://localhost:9515',
            'chromeOptions' => [
                '--headless',
                '--disable-gpu',
                '--no-sandbox',
            ],
        ],
    ];
})();
