<?php

namespace App\Core;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BankApiService
{
    private const CACHE_KEY = "brasil_api:banks";
    private const CACHE_TTL_SECONDS = 60 * 60 * 24;

    private const FALLBACK_BANKS = [
        ["code" => "001", "name" => "Banco do Brasil"],
        ["code" => "033", "name" => "Santander"],
        ["code" => "077", "name" => "Banco Inter"],
        ["code" => "104", "name" => "Caixa Econômica Federal"],
        ["code" => "237", "name" => "Bradesco"],
        ["code" => "260", "name" => "Nubank"],
        ["code" => "290", "name" => "PagBank"],
        ["code" => "323", "name" => "Mercado Pago"],
        ["code" => "336", "name" => "C6 Bank"],
        ["code" => "341", "name" => "Itaú Unibanco"],
        ["code" => "380", "name" => "PicPay"],
    ];

    private static ?Client $client = null;
    private static ?\Redis $redis = null;

    private static function client(): Client
    {
        if (self::$client === null) {
            self::$client = new Client([
                "base_uri" => "https://brasilapi.com.br",
                "timeout" => 5,
            ]);
        }

        return self::$client;
    }

    private static function redis(): \Redis
    {
        if (self::$redis === null) {
            self::$redis = new \Redis();
            self::$redis->connect(REDIS_HOST, REDIS_PORT);
        }

        return self::$redis;
    }

    /**
     * @throws \JsonException
     */
    public static function getBanks(): array
    {
        try {
            $cached = self::redis()->get(self::CACHE_KEY);

            if ($cached) {
                return json_decode($cached, true, 512, JSON_THROW_ON_ERROR);
            }
        } catch (\Throwable $exception) {
            Logger::warning("Falha ao ler cache de bancos no Redis", [
                "exception" => $exception->getMessage(),
            ]);
        }

        $banks = self::fetchFromApi();

        if ($banks) {
            try {
                self::redis()->setex(self::CACHE_KEY, self::CACHE_TTL_SECONDS, json_encode($banks, JSON_THROW_ON_ERROR));
            } catch (\Throwable $exception) {
                Logger::warning("Falha ao gravar cache de bancos no Redis", [
                    "exception" => $exception->getMessage(),
                ]);
            }

            return $banks;
        }

        return self::FALLBACK_BANKS;
    }

    private static function fetchFromApi(): ?array
    {
        try {
            $response = self::client()->get("/api/banks/v1");
            $data = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($data)) {
                return null;
            }

            $banks = [];

            foreach ($data as $bank) {
                if (empty($bank["code"])) {
                    continue;
                }

                $name = $bank["fullName"] ?? $bank["name"] ?? null;

                if (!$name) {
                    continue;
                }

                $banks[] = [
                    "code" => str_pad((string)$bank["code"], 3, "0", STR_PAD_LEFT),
                    "name" => self::formatBankName($name),
                ];
            }

            usort($banks, fn($a, $b) => $a["code"] <=> $b["code"]);

            return $banks ?: null;
        } catch (GuzzleException $exception) {
            Logger::warning("Falha ao buscar bancos na Brasil API", [
                "exception" => $exception->getMessage(),
            ]);
            return null;
        }
    }

    private static function formatBankName(string $name): string
    {
        $name = mb_convert_case(mb_strtolower($name, "UTF-8"), MB_CASE_TITLE, "UTF-8");

        $fixes = [
            "/\bS\.a\b\.?/u" => "S.A.",
            "/\bLtda\b\.?/u" => "Ltda.",
            "/\bC\.f\.i\b\.?/u" => "C.F.I.",
            "/\bDtvm\b/u" => "DTVM",
        ];

        return preg_replace(array_keys($fixes), array_values($fixes), $name);
    }
}