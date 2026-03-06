<?php
// JWT Helper with WEAK signing key for brute-force lab
// Matches JWTbypassVIAweaksignkey.pdf teaching materials
// Key can be cracked with: hashcat -a 0 -m 16500 <JWT> wordlist.txt

define('JWT_SECRET_WEAK', 'secret1');  // Weak key - crackable with wordlist

function base64url_encode_weak($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode_weak($data) {
    $padding = 4 - strlen($data) % 4;
    if ($padding < 4) {
        $data .= str_repeat('=', $padding);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function create_jwt_weak($payload) {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];

    $header_encoded = base64url_encode_weak(json_encode($header));
    $payload_encoded = base64url_encode_weak(json_encode($payload));

    $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", JWT_SECRET_WEAK, true);
    $signature_encoded = base64url_encode_weak($signature);

    return "$header_encoded.$payload_encoded.$signature_encoded";
}

function decode_jwt_weak($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }

    return [
        'header' => json_decode(base64url_decode_weak($parts[0]), true),
        'payload' => json_decode(base64url_decode_weak($parts[1]), true),
        'signature' => $parts[2]
    ];
}

function verify_jwt_weak($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }

    $header_payload = $parts[0] . '.' . $parts[1];
    $signature = hash_hmac('sha256', $header_payload, JWT_SECRET_WEAK, true);
    $expected_signature = base64url_encode_weak($signature);

    return hash_equals($expected_signature, $parts[2]);
}
?>
