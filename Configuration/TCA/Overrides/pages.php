<?php
defined('TYPO3') or die();

$tempColumns = [
    'tx_exdeliverwcag_conformance_level' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:exdeliver_wcag/Resources/Private/Language/locallang_db.xlf:pages.tx_exdeliverwcag_conformance_level',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['label' => 'LLL:EXT:exdeliver_wcag/Resources/Private/Language/locallang_db.xlf:pages.tx_exdeliverwcag_conformance_level.A', 'value' => 'A'],
                ['label' => 'LLL:EXT:exdeliver_wcag/Resources/Private/Language/locallang_db.xlf:pages.tx_exdeliverwcag_conformance_level.AA', 'value' => 'AA'],
                ['label' => 'LLL:EXT:exdeliver_wcag/Resources/Private/Language/locallang_db.xlf:pages.tx_exdeliverwcag_conformance_level.AAA', 'value' => 'AAA'],
            ],
            'default' => 'AA',
        ],
    ],
    'tx_exdeliverwcag_readability_score' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:exdeliver_wcag/Resources/Private/Language/locallang_db.xlf:pages.tx_exdeliverwcag_readability_score',
        'config' => [
            'type' => 'input',
            'size' => 5,
            'eval' => 'double2',
            'readOnly' => true,
        ],
    ],
    'tx_exdeliverwcag_problems' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:exdeliver_wcag/Resources/Private/Language/locallang_db.xlf:pages.tx_exdeliverwcag_problems',
        'config' => [
            'type' => 'text',
            'cols' => 40,
            'rows' => 5,
            'readOnly' => true,
        ],
    ],
    'tx_exdeliverwcag_improvements' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:exdeliver_wcag/Resources/Private/Language/locallang_db.xlf:pages.tx_exdeliverwcag_improvements',
        'config' => [
            'type' => 'text',
            'cols' => 40,
            'rows' => 5,
            'readOnly' => true,
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $tempColumns);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--div--;LLL:EXT:exdeliver_wcag/Resources/Private/Language/locallang_db.xlf:pages.tab.wcag,
    tx_exdeliverwcag_conformance_level, tx_exdeliverwcag_readability_score, tx_exdeliverwcag_problems, tx_exdeliverwcag_improvements'
);

$GLOBALS['TCA']['pages']['palettes']['wcag-analysis'] = [
    'showitem' => 'tx_exdeliverwcag_readability_analysis',
];
