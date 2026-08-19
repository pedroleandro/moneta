<?php

namespace App\Core;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Turnstile
{
    private static ?Client $client = null;

    private static function client(): Client
    {
        if (self::$client === null) {
            self::$client = new Client([
                "base_uri" => "https://challenges.cloudflare.com",
                "timeout" => 5,
            ]);
        }

        return self::$client;
    }

    public static function verify(?string $token, ?string $ip = null): bool
    {
        if (!$token) {
            return false;
        }

        if (APP_ENV === "local" && !TURNSTILE_SECRET_KEY) {
            return true;
        }

        try {
            $response = self::client()->post("/turnstile/v0/siteverify", [
                "form_params" => [
                    "secret" => TURNSTILE_SECRET_KEY,
                    "response" => $token,
                    "remoteip" => $ip,
                ],
            ]);

            $body = json_decode((string)$response->getBody(), true);

            return (bool)($body["success"] ?? false);
        } catch (GuzzleException $exception) {
            Logger::error("Falha ao validar Turnstile", [
                "exception" => $exception->getMessage(),
            ]);
            return true;
        }
    }
}