<?php

$EM_CONF['ws_guestbook'] = [
    'title' => 'WapplerSystems Guestbook TYPO3 Plugin',
    'description' => 'Guestbook Extension',
    'category' => 'plugin',
    'author' => 'Sven Wappler, Franziska Krug',
    'author_email' => '',
    'author_company' => 'WapplerSystems',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '12.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
