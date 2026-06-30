<?php

$publicPath = __DIR__;

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Only serve real files as static — directories must pass through to Laravel routing.
if ($uri !== '/' && file_exists($publicPath.$uri) && !is_dir($publicPath.$uri)) {
    return false;
}

require_once $publicPath.'/index.php';
