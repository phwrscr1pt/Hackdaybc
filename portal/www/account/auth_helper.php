<?php
if (!defined('JWT_SECRET')) define('JWT_SECRET', 'secret123');  // Weak secret for cracking lab

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    $padding = 4 - strlen($data) % 4;
    if ($padding < 4) {
        $data .= str_repeat('=', $padding);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function create_jwt($payload) {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];

    $header_encoded = base64url_encode(json_encode($header));
    $payload_encoded = base64url_encode(json_encode($payload));

    $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", JWT_SECRET, true);
    $signature_encoded = base64url_encode($signature);

    return "$header_encoded.$payload_encoded.$signature_encoded";
}

function decode_jwt($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }

    return [
        'header' => json_decode(base64url_decode($parts[0]), true),
        'payload' => json_decode(base64url_decode($parts[1]), true),
        'signature' => $parts[2]
    ];
}

function verify_jwt($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }

    $header_payload = $parts[0] . '.' . $parts[1];
    $signature = hash_hmac('sha256', $header_payload, JWT_SECRET, true);
    $expected_signature = base64url_encode($signature);

    return hash_equals($expected_signature, $parts[2]);
}
?>
