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
     * Verbose exceptions will contain more information about the request in the exception
     * message. The request URL and the beginning of an API token used (user_zx312****)
     * will be added for easier debugging of configuration.
     */
    'verbose_exceptions' => true,

    /**
     * The default Belltastic project ID. This will be used by the Blade directives
     */
    'default_project' => env('BELLTASTIC_PROJECT_ID', '1'),

    /**
     * A list of Belltastic projects that this app interacts with.
     *
     * By default, and in most cases, you only need one project and its
     * secret in order to generate valid HMAC authorization tokens.
     */
    'projects' => [
        // this is a configuration for a Belltastic project with ID of 1
        env('BELLTASTIC_PROJECT_ID', '1') => [
            // The Project-specific API key for this project. You can get it from
            // your Project Settings page in Belltastic
            'api_key' => env('BELLTASTIC_PROJECT_API_KEY', ''),

            // The secret is used to calculate User HMAC values with $user->hmac() method.
            'secret' => env('BELLTASTIC_PROJECT_SECRET', ''),
        ],

        // env('BELLTASTIC_SECOND_PROJECT_ID') => [
        //     'api_key' => env('BELLTASTIC_SECOND_PROJECT_API_KEY'),
        //     'secret'  => env('BELLTASTIC_SECOND_PROJECT_SECRET'),
        // ],
    ]
];
