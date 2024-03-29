<?php

call_user_func(function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'ws_guestbook',
        'List',
        'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:wsguestbook_list'
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'ws_guestbook',
        'Form',
        'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:wsguestbook_form'
    );

    /* Flexform setting  */
    $pluginSignatureform = 'wsguestbook_form';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignatureform] = 'pi_flexform';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignatureform, 'FILE:EXT:ws_guestbook/Configuration/FlexForm/form.xml');

    $pluginSignatureform = 'wsguestbook_list';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignatureform] = 'pi_flexform';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignatureform, 'FILE:EXT:ws_guestbook/Configuration/FlexForm/list.xml');

});
