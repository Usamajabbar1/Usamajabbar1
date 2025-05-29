
<?php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // ✅ Add sanctum route

    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:8080'], // ✅ Vue dev server
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
