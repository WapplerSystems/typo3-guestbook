<?php

$temp = [
    'ctrl' => [
        'title'	=> 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:tx_wsguestbook_domain_model_wsguestbook',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'sortby' => 'crdate DESC',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'name,city,email,website,message,terms,',
        'iconfile' => 'EXT:ws_guestbook/Resources/Public/Icons/tx_wsguestbook_domain_model_wsguestbook.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, city, email, website, message, terms',
    ],
    'types' => [
        '1' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden1, name, city, email, website, message, terms, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, starttime, endtime'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [

        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1],
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0]
                ],
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_wsguestbook_domain_model_wsguestbook',
                'foreign_table_where' => 'AND tx_wsguestbook_domain_model_wsguestbook.pid=###CURRENT_PID### AND tx_wsguestbook_domain_model_wsguestbook.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],

        't3ver_label' => [
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ]
        ],

        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'starttime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 13,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ],

            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 13,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ],
            ],
        ],

        'name' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:tx_wsguestbook_domain_model_wsguestbook.name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'city' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:tx_wsguestbook_domain_model_wsguestbook.city',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'email' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:tx_wsguestbook_domain_model_wsguestbook.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,email,required'
            ],
        ],
        'website' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:tx_wsguestbook_domain_model_wsguestbook.website',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'message' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:tx_wsguestbook_domain_model_wsguestbook.message',
            'config' => [
                     'type' => 'text',
                     'enableRichtext' => true,
             ],
        ],
        'terms' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:tx_wsguestbook_domain_model_wsguestbook.terms',
            'config' => [
                'type' => 'check',
                'readOnly' => 1,
            ],
        ],
    ],
];

if (version_compare(TYPO3_branch, '7.0', '<')) {
    $temp['columns']['message']['config']['type'] = 'text';
    $temp['columns']['message']['config']['cols'] = '80';
    $temp['columns']['message']['config']['rows'] = '3';
    $temp['columns']['message']['config']['softref'] = 'typolink_tag,images,email[subst],url';
    $temp['columns']['message']['defaultExtras'] = 'richtext[]:rte_transform[mode=tx_examples_transformation-ts_css]';
}

return $temp;
