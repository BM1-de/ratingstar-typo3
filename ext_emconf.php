<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'RatingStar Seal',
    'description' => 'Kundenbewertungen auf der TYPO3-Website zeigen: RatingStar-Siegel, Bewertungs-Widgets und Google-Sterne (Rich Snippets).',
    'category' => 'fe',
    'author' => 'Phillip Baumgärtner',
    'author_email' => 'baumgaertner@bm1.de',
    'author_company' => 'Baumgärtner Marketing GmbH',
    'state' => 'stable',
    'version' => '1.2.1',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.99.99',
            'php' => '8.2.0-8.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
