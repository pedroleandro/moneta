<?php

use JetBrains\PhpStorm\NoReturn;

function appBaseUrl(): string
{
    if (empty($_SERVER['HTTP_HOST'])) {
        return rtrim(APP_URL, '/');
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? null) == 443
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    $scheme = $isHttps ? 'https' : 'http';

    return $scheme . '://' . $_SERVER['HTTP_HOST'];
}

function url(string $path = null): string
{
    $base = appBaseUrl();

    if ($path) {
        return $base . '/' . ltrim($path, '/');
    }

    return $base;
}

#[NoReturn]
function redirect(string $path): void
{
    header("Location: " . url($path));
    exit;
}

function assets(string $path = null): string
{
    $base = appBaseUrl() . "/public/assets";

    if ($path) {
        return $base . '/' . ltrim($path, '/');
    }

    return $base;
}

function assets_mazer(string $path = null): string
{
    $base = appBaseUrl() . "/resources/themes/dist";

    if ($path) {
        return $base . '/' . ltrim($path, '/');
    }

    return $base;
}

function assets_sneat(string $path = null): string
{
    $base = appBaseUrl() . "/resources/themes/sneat/sneat-bootstrap-html-admin-template-free";

    if ($path) {
        return $base . '/' . ltrim($path, '/');
    }

    return $base;
}