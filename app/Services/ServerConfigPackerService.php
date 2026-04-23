<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class ServerConfigPackerService
{
    /**
     * Packs the payload into the base64 format expected by the AC EVO dedicated server.
     *
     * Format: 4-byte big-endian uint32 (uncompressed length) || zlib-compressed JSON, base64-encoded.
     *
     * @param  array<string, mixed>  $payload
     */
    public function pack(array $payload): string
    {
        $json = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        $compressed = gzcompress($json, 6);

        if ($compressed === false) {
            throw new RuntimeException('Unable to compress payload.');
        }

        return base64_encode(pack('N', strlen($json)).$compressed);
    }

    /**
     * Reverses a previously packed payload. Useful for verification/testing.
     *
     * @return array<string, mixed>
     */
    public function unpack(string $base64): array
    {
        $raw = base64_decode($base64, true);

        if ($raw === false || strlen($raw) < 4) {
            throw new RuntimeException('Invalid base64 payload.');
        }

        $body = gzuncompress(substr($raw, 4));

        if ($body === false) {
            throw new RuntimeException('Unable to decompress payload.');
        }

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($body, true, flags: JSON_THROW_ON_ERROR);

        return $decoded;
    }
}
