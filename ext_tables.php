<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_wsguestbook_domain_model_wsguestbook', 'EXT:ws_guestbook/Resources/Private/Language/locallang_csh_tx_wsguestbook_domain_model_wsguestbook.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_wsguestbook_domain_model_wsguestbook');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:ws_guestbook/Configuration/TSconfig/ContentElementWizard.txt">'
);
