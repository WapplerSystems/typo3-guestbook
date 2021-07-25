<?php

return [
    'ctrl' => [
        'title'	=> 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:tx_wsguestbook_domain_model_entry',
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
        'iconfile' => 'EXT:ws_guestbook/Resources/Public/Icons/tx_wsguestbook_domain_model_entry.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, city, email, website, message, terms',
    ],
    'types' => [
        '1' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, tstamp, name, city, email, website, message, terms, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, starttime, endtime'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],

    ],
    'columns' => [

        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ],
                ],
                'default' => 0,
            ]
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
                'foreign_table' => 'tx_wsguestbook_domain_model_entry',
                'foreign_table_where' => 'AND tx_wsguestbook_domain_model_entry.pid=###CURRENT_PID### AND tx_wsguestbook_domain_model_entry.sys_language_uid IN (-1,0)',
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
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 1,
                'items' => [
                    [
                        0 => '',
                        1 => '',
                        'invertStateDisplay' => false
                    ]
                ],
            ]
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
            ]
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
            ]
        ],
        'tstamp' => [
            'exclude' => true,
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:date',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'readOnly' => true,
            ]
        ],
        'name' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:tx_wsguestbook_domain_model_entry.name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'city' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:tx_wsguestbook_domain_model_entry.city',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'email' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:tx_wsguestbook_domain_model_entry.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,email,required'
            ],
        ],
        'website' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:tx_wsguestbook_domain_model_entry.website',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'message' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:tx_wsguestbook_domain_model_entry.message',
            'config' => [
                     'type' => 'text',
                     'enableRichtext' => true,
             ],
        ],
        'terms' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:tx_wsguestbook_domain_model_entry.terms',
            'config' => [
                'type' => 'check',
                'readOnly' => 1,
            ],
        ],
    ],
];
