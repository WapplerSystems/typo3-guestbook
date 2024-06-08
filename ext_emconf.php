<?php

$EM_CONF['ws_guestbook'] = [
    'title' => 'WapplerSystems Guestbook',
    'description' => 'Guestbook Extension',
    'category' => 'plugin',
    'author' => 'Sven Wappler, Franziska Krug',
    'author_email' => '',
    'author_company' => 'WapplerSystems',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '12.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'form_crshield' => '1.0.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
