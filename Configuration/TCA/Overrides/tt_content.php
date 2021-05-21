<?php
defined('TYPO3_MODE') or die();

$_EXTKEY = 'ws_guestbook';

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'WapplerSystems.' . $_EXTKEY,
    'Form',
    'Guestbook'
);

/* Flexform setting  */
$pluginSignatureform = str_replace('_', '', $_EXTKEY) . '_' . 'form';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignatureform] = 'recursive,select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignatureform] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignatureform, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForm/flexform.xml');
