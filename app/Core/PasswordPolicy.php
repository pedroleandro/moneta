<?php

namespace App\Core;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PasswordPolicy
{
    private static ?Client $client = null;

    private static function client(): Client
    {
        if (self::$client === null) {
            self::$client = new Client([
                "base_uri" => "https://api.pwnedpasswords.com",
                "timeout" => 3,
                "headers" => ["User-Agent" => "Moneta-App"],
            ]);
        }

        return self::$client;
    }

    public static function isPwned(string $password): ?bool
    {
        $sha1 = strtoupper(sha1($password));
        $prefix = substr($sha1, 0, 5);
        $suffix = substr($sha1, 5);

        try {
            $response = self::client()->get("/range/{$prefix}");
            $body = (string)$response->getBody();

            foreach (explode("\r\n", trim($body)) as $line) {
                [$hashSuffix, $count] = explode(":", $line);

                if (hash_equals($hashSuffix, $suffix)) {
                    return true;
                }
            }

            return false;
        } catch (GuzzleException $exception) {
            Logger::warning("Falha ao consultar Have I Been Pwned", [
                "exception" => $exception->getMessage(),
            ]);
            return null;
        }
    }
}