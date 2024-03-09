<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use WapplerSystems\WsGuestbook\Controller\GuestbookController;

ExtensionUtility::configurePlugin(
    'ws_guestbook',
    'List',
    [
        GuestbookController::class => 'list',
    ],
    [
    ]
);

ExtensionUtility::configurePlugin(
    'ws_guestbook',
    'Form',
    [
        GuestbookController::class => 'new,done,decline,confirm,entryNotFound',
    ],
    [
        GuestbookController::class => 'new,done,decline,confirm,entryNotFound',
    ]
);


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['ws_guestbook'] = \WapplerSystems\WsGuestbook\Hooks\PageLayoutView::class;


// register cache table
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wsguestbookcaptcha'] ?? null)) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wsguestbookcaptcha'] = [];
}

ExtensionManagementUtility::addTypoScriptSetup(
    'module.tx_form {
    settings {
        yamlConfigurations {
            90 = EXT:ws_guestbook/Configuration/Yaml/FormSetup.yaml
        }
    }
}
plugin.tx_form {
    settings {
        yamlConfigurations {
            90 = EXT:ws_guestbook/Configuration/Yaml/FormSetup.yaml
        }
    }
}'
);
