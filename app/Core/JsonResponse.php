<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Respuestas JSON uniformes para endpoints API.
 */
final class JsonResponse
{
    public static function send(array $data, int $status = 200): void
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function ok(array $data = []): void
    {
        self::send(array_merge(['ok' => true], $data));
    }

    public static function fail(string $message, int $status = 400): void
    {
        self::send(['ok' => false, 'error' => $message], $status);
    }
}
