
<?php

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/helpers.php';

$request = aioa_request($_GET);

$page = [
    'domain' => $request['url']
        ?? ($page['domain'] ?? ''),

    'name' => $request['username']
        ?? ($page['name'] ?? ''),

    'email' => $request['email']
        ?? ($page['email'] ?? ''),

    'theme' => 'light',
];

require __DIR__ . '/views/layout.php';
