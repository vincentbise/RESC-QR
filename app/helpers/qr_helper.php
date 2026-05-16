<?php

function generateQRCodeUrl($data, $size = 200) {
    $encoded = urlencode($data);
    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encoded}&format=png&margin=10";
}

function generateQRCodeDataUri($data, $size = 200) {
    return generateQRCodeUrl($data, $size);
}
