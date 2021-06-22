<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'WapplerSystems.ws_guestbook',
    'List',
    'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang.xlf:wsguestbook_list'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'WapplerSystems.ws_guestbook',
    'Form',
    'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang.xlf:wsguestbook_form'
);

/* Flexform setting  */
$pluginSignatureform = 'wsguestbook_form';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignatureform] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignatureform, 'FILE:EXT:ws_guestbook/Configuration/FlexForm/form.xml');

$pluginSignatureform = 'wsguestbook_list';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignatureform] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignatureform, 'FILE:EXT:ws_guestbook/Configuration/FlexForm/list.xml');
