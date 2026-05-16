<?php

function baseUrl($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

function publicUrl($path = '') {
    return PUBLIC_URL . '/' . ltrim($path, '/');
}

function currentUrl() {
    return $_SERVER['REQUEST_URI'] ?? '/';
}

function isActive($path) {
    $current = trim(parse_url(currentUrl(), PHP_URL_PATH), '/');
    $check = trim('RESC-QR/' . ltrim($path, '/'), '/');
    return $current === $check ? 'active' : '';
}
