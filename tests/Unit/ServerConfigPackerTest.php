<?php

declare(strict_types=1);

use App\Services\ServerConfigPackerService;

it('packs a payload into a base64-encoded string with a 4-byte length header', function (): void {
    $packer = new ServerConfigPackerService;
    $payload = ['foo' => 'bar', 'n' => 42];

    $packed = $packer->pack($payload);

    expect($packed)->toBeString()->not->toBeEmpty();

    $raw = base64_decode($packed, true);

    expect($raw)->not->toBeFalse();

    $expectedJson = json_encode(
        $payload,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );

    expect(strlen($raw))->toBeGreaterThan(4);

    $header = unpack('N', substr($raw, 0, 4))[1];
    expect($header)->toBe(strlen($expectedJson));
});

it('is reversible through unpack', function (): void {
    $packer = new ServerConfigPackerService;
    $payload = [
        'server_name' => 'aleex GT',
        'max_players' => 16,
        'cycle' => true,
        'cars' => [
            ['car_name' => 'preset_r8gt4_mech_1', 'ballast' => 0, 'restrictor' => 0.25],
        ],
    ];

    $packed = $packer->pack($payload);
    $unpacked = $packer->unpack($packed);

    expect($unpacked)->toBe($payload);
});

it('is compatible with the python reference implementation', function (): void {
    $packer = new ServerConfigPackerService;
    $payload = ['message' => 'hello', 'value' => 7];
    $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $packed = $packer->pack($payload);

    $raw = base64_decode($packed, true);
    $headerSize = unpack('N', substr($raw, 0, 4))[1];

    expect($headerSize)->toBe(strlen($json));

    $body = gzuncompress(substr($raw, 4));
    expect($body)->toBe($json);
});
