<?php

// Router for the local PHP development server; Apache uses docker/apache.conf.
if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path !== '/' && is_file(__DIR__.$path)) {
    return false;
}

require __DIR__.'/index.php';
