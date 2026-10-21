<?php

$EM_CONF['ws_guestbook'] = [
    'title' => 'WapplerSystems Guestbook',
    'description' => 'Guestbook Extension',
    'category' => 'plugin',
    'author' => 'Sven Wappler, Franziska Krug',
    'author_email' => '',
    'author_company' => 'WapplerSystems',
    'state' => 'stable',
    'version' => '14.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.3.0-14.3.99',
            'form' => '14.3.0-14.3.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'form_crshield' => '4.0.0-4.99.99',
        ],
    ],
];
