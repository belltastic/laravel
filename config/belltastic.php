<?php

return [
    'base_uri' => env('BELLTASTIC_API_URL', 'https://belltastic.com/api/v1/'),

    /**
     * Owner API key (starts with user_...) that you can retrieve from here:
     * @link https://belltastic.com/user/api-tokens
     *
     * This will be the token used by default, unless otherwise provided
     * in the $options parameter for Belltastic models.
     */
    'api_key' => env('BELLTASTIC_API_KEY'),

    /**
     * A list of Belltastic projects that this app interacts with.
     *
     * By default, and in most cases, you only need one project and its
     * secret in order to generate valid HMAC authorization tokens.
     */
    'projects' => [
        // this is a configuration for a Belltastic project with ID of 1
        env('BELLTASTIC_PROJECT_ID', '1') => [
            // The secret is used to calculate User HMAC values with $user->hmac() method.
            'secret' => env('BELLTASTIC_PROJECT_SECRET', ''),
        ],

        // add more projects if needed
    ]
];
