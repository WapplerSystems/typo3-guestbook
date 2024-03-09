<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function () {
// Adding fields to the tt_content table definition in TCA
    ExtensionManagementUtility::addStaticFile(
        'ws_guestbook',
        'Configuration/TypoScript',
        'WapplerSystems Guestbook'
    );
});
