<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'ws_guestbook',
    'List',
    [
        \WapplerSystems\WsGuestbook\Controller\GuestbookController::class => 'list,review,decline,confirm',
    ],
    [
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'ws_guestbook',
    'Form',
    [
        \WapplerSystems\WsGuestbook\Controller\GuestbookController::class => 'new',
    ],
    [
        \WapplerSystems\WsGuestbook\Controller\GuestbookController::class => 'new',
    ]
);


$icons = [
    'ext-ns-guestbook-icon' => 'ws_guestbook.svg',
];
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
foreach ($icons as $identifier => $path) {
    $iconRegistry->registerIcon(
        $identifier,
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:ws_guestbook/Resources/Public/Icons/' . $path]
    );
}


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['ws_guestbook']= \WapplerSystems\WsGuestbook\Hooks\PageLayoutView::class;
