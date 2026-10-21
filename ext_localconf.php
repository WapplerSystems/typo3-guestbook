<?php

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use WapplerSystems\WsGuestbook\Controller\GuestbookController;

defined('TYPO3') || die();

ExtensionUtility::configurePlugin(
    'WsGuestbook',
    'List',
    [
        GuestbookController::class => 'list',
    ],
);

ExtensionUtility::configurePlugin(
    'WsGuestbook',
    'Form',
    [
        GuestbookController::class => 'new,done,decline,confirm,entryNotFound',
    ],
    [
        GuestbookController::class => 'new,done,decline,confirm,entryNotFound',
    ],
);
