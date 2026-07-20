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
            'group' => 'special',
        ],
    );

    // Type icon for the content element.
    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$cType] = 'ratingstar-seal';

    // Field layout for the new CType: the full standard content element
    // anatomy (headers, appearance, language, access, categories, notes)
    // with the FlexForm on the general tab.
    $GLOBALS['TCA']['tt_content']['types'][$cType] = [
        'showitem' => '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;;general,
                --palette--;;headers,
                pi_flexform,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
                --palette--;;frames,
                --palette--;;appearanceLinks,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;;access,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                categories,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                rowDescription,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
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
            'group' => 'special',
        ],
    );
    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$jsonLdType] = 'ratingstar-seal';
    // Invisible element (renders a <script type="application/ld+json"> only),
    // so headers/appearance stay out on purpose — but language, access and
    // notes belong to a complete authoring form.
    $GLOBALS['TCA']['tt_content']['types'][$jsonLdType] = [
        'showitem' => '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;;general,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;;access,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                rowDescription,
        ',
    ];
})();
