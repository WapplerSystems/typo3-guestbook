<?php


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

if (!is_array($GLOBALS['TCA']['tt_content']['types']['wsguestbook_form'] ?? null)) {
    $GLOBALS['TCA']['tt_content']['types']['wsguestbook_form'] = [];
}

ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:wsguestbook_form',
        'wsguestbook_form',
        'ext-ws-guestbook-icon'
    ],
    'textmedia',
    'after'
);

$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['wsguestbook_form'] = 'ext-ws-guestbook-icon';


$GLOBALS['TCA']['tt_content']['types']['wsguestbook_form'] = array_replace_recursive(
    $GLOBALS['TCA']['tt_content']['types']['wsguestbook_form'],
    [
        'showitem' => '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.headers;headers,
                pi_flexform;LLL:EXT:ws_guestbook/Resources/Private/Language/locallang.xlf:title,
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                categories,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                rowDescription,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
        '
    ]
);

ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:ws_guestbook/Configuration/FlexForm/form.xml',
    'wsguestbook_form'
);


if (!is_array($GLOBALS['TCA']['tt_content']['types']['wsguestbook_list'] ?? null)) {
    $GLOBALS['TCA']['tt_content']['types']['wsguestbook_list'] = [];
}

ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:wsguestbook_list',
        'wsguestbook_list',
        'ext-ws-guestbook-icon'
    ],
    'textmedia',
    'after'
);

$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['wsguestbook_list'] = 'ext-ws-guestbook-icon';


$GLOBALS['TCA']['tt_content']['types']['wsguestbook_list'] = array_replace_recursive(
    $GLOBALS['TCA']['tt_content']['types']['wsguestbook_list'],
    [
        'showitem' => '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.headers;headers,
                pi_flexform;LLL:EXT:ws_guestbook/Resources/Private/Language/locallang.xlf:title,
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                categories,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                rowDescription,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
        '
    ]
);

ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:ws_guestbook/Configuration/FlexForm/list.xml',
    'wsguestbook_list'
);
