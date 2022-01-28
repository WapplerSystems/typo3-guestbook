<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'ws_guestbook',
    'List',
    [
        \WapplerSystems\WsGuestbook\Controller\GuestbookController::class => 'list',
    ],
    [
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'ws_guestbook',
    'Form',
    [
        \WapplerSystems\WsGuestbook\Controller\GuestbookController::class => 'new,done,decline,confirm,entryNotFound',
    ],
    [
        \WapplerSystems\WsGuestbook\Controller\GuestbookController::class => 'new,done,decline,confirm,entryNotFound',
    ]
);


$icons = [
    'ext-ws-guestbook-icon' => 'ws_guestbook.svg',
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


if (!function_exists('gregwar_captcha_php_autoload') && !\TYPO3\CMS\Core\Core\Environment::isComposerMode()) {
    function gregwar_captcha_php_autoload($className)
    {
        $classPath = explode('\\', $className);
        if ($classPath[0] !== 'Gregwar') {
            return;
        }
        $path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('ws_guestbook');

        $filePath = $path . DIRECTORY_SEPARATOR . 'Resources/Private/PHP/' . implode('/', $classPath) . '.php';
        if (file_exists($filePath)) {
            require_once($filePath);
        }
    }

    spl_autoload_register('gregwar_captcha_php_autoload');
}

// register cache table
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wsguestbookcaptcha'] ?? null)) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wsguestbookcaptcha'] = [];
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeRendering'][1571076908]
    = \WapplerSystems\WsGuestbook\Hooks\FormElementCaptchaHook::class;
