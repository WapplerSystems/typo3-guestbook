<?php

$ll = 'LLL:EXT:ws_guestbook/Resources/Private/Language/locallang_db.xlf:tx_wsguestbook_domain_model_entry';

return [
    'ctrl' => [
        'title' => $ll,
        'label' => 'name',
        'label_alt' => 'city',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'default_sortby' => 'crdate DESC',
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
        'searchFields' => 'name,city,email,website,message',
        'iconfile' => 'EXT:ws_guestbook/Resources/Public/Icons/tx_wsguestbook_domain_model_entry.gif',
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --div--;core.form.tabs:general,
                    hidden, tstamp, name, city, email, website, message, action_key,
                --div--;core.form.tabs:language,
                    sys_language_uid, l10n_parent,
                --div--;core.form.tabs:access,
                    starttime, endtime',
        ],
    ],
    'columns' => [
        // tstamp wird im Frontend als Datum des Eintrags ausgegeben (Entry::getTstamp())
        'tstamp' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:date',
            'config' => [
                'type' => 'datetime',
                'readOnly' => true,
            ],
        ],
        'name' => [
            'exclude' => true,
            'label' => $ll . '.name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'action_key' => [
            'exclude' => true,
            'label' => $ll . '.action_key',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => true,
            ],
        ],
        'city' => [
            'exclude' => true,
            'label' => $ll . '.city',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'email' => [
            'exclude' => true,
            'label' => $ll . '.email',
            'config' => [
                'type' => 'email',
                'size' => 30,
            ],
        ],
        'website' => [
            'exclude' => true,
            'label' => $ll . '.website',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'message' => [
            'exclude' => true,
            'label' => $ll . '.message',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
            ],
        ],
    ],
];
