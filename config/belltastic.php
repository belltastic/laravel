<?php

return [
    'base_uri' => 'https://belltastic.com/api/v1/',

    'projects' => [
        // this is a configuration for a Belltastic project with ID of 1
        env('BELLTASTIC_PROJECT_ID', '1') => [
            // The secret is used to calculate User HMAC values with $user->hmac() method.
            'secret' => env('BELLTASTIC_PROJECT_SECRET', ''),
        ],

        // add more projects if needed
    ]
];
