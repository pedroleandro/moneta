<?php

namespace App\Core;

class SecurityHeaders
{
    public static function apply(): void
    {
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

        if (APP_ENV !== "local") {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        }

        header(
            "Content-Security-Policy: " .
            "default-src 'self'; " .
            "script-src 'self' https://accounts.google.com; " .
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
            "font-src 'self' https://fonts.gstatic.com; " .
            "img-src 'self' data: https:; " .
            "frame-src https://accounts.google.com; " .
            "connect-src 'self'"
        );
    }
}