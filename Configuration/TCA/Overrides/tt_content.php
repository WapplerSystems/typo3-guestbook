<?php

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die();

$ll = 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:';

foreach (['Form' => 'form', 'List' => 'list'] as $pluginName => $flexForm) {
    $cType = ExtensionUtility::registerPlugin(
        'WsGuestbook',
        $pluginName,
        $ll . 'wsguestbook_' . $flexForm,
        'ext-ws-guestbook-icon',
        'plugins',
        $ll . 'tsconfig.wizard.WsGuestbook' . $pluginName . '.description',
        'FILE:EXT:ws_guestbook/Configuration/FlexForm/' . $flexForm . '.xml',
    );
    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$cType] = 'ext-ws-guestbook-icon';
}
