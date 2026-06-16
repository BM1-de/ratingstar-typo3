<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

(static function (): void {
    $cType = 'ratingstar_seal';
    $ll = 'LLL:EXT:ratingstar_seal/Resources/Private/Language/locallang_db.xlf:';

    // Register the CType in the type dropdown / "New Content Element" wizard.
    ExtensionManagementUtility::addTcaSelectItem(
        'tt_content',
        'CType',
        [
            'label' => $ll . 'ce.seal.title',
            'description' => $ll . 'ce.seal.description',
            'value' => $cType,
            'icon' => 'ratingstar-seal',
            'group' => 'default',
        ],
    );

    // Type icon for the content element.
    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$cType] = 'ratingstar-seal';

    // Field layout for the new CType: general tab with the FlexForm, plus the
    // standard appearance and access tabs.
    $GLOBALS['TCA']['tt_content']['types'][$cType] = [
        'showitem' => '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;;general,
                pi_flexform,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
                --palette--;;frames,
                --palette--;;appearanceLinks,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;;access,
        ',
    ];

    // Bind the FlexForm (variant + position) to this CType.
    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:ratingstar_seal/Configuration/FlexForms/Seal.xml',
        $cType,
    );

    // Second CType: server-side Google-stars JSON-LD, for targeted per-page
    // placement (alternative to the site-wide middleware injection). No options.
    $jsonLdType = 'ratingstar_jsonld';
    ExtensionManagementUtility::addTcaSelectItem(
        'tt_content',
        'CType',
        [
            'label' => $ll . 'ce.jsonld.title',
            'description' => $ll . 'ce.jsonld.description',
            'value' => $jsonLdType,
            'icon' => 'ratingstar-seal',
            'group' => 'default',
        ],
    );
    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$jsonLdType] = 'ratingstar-seal';
    $GLOBALS['TCA']['tt_content']['types'][$jsonLdType] = [
        'showitem' => '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;;general,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;;access,
        ',
    ];
})();
