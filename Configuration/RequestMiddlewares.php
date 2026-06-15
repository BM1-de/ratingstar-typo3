<?php

declare(strict_types=1);

/**
 * Registers the server-side JSON-LD injector in the frontend stack. Placed after
 * TSFE rendering is prepared so the resolved site/page are available and the
 * middleware sees the fully rendered HTML on the way out; outer middlewares
 * (e.g. content-length) recompute afterwards.
 */
return [
    'frontend' => [
        'ratingstar/seal/jsonld-injector' => [
            'target' => \RatingStar\Seal\Middleware\JsonLdInjector::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
        ],
    ],
];
