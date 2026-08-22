<?php

class InputValidator {

    public static function sanitizeString($input) {
        return htmlspecialchars(strip_tags(trim($input ?? '')), ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail($email) {
        $email = filter_var(trim($email ?? ''), FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
    }

    public static function validateId($id) {
        return filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    }

    public static function validatePhone($phone) {
        $phone = preg_replace('/[^0-9+]/', '', $phone ?? '');
        if (preg_match('/^(\+63|0)\d{10}$/', $phone)) {
            return $phone;
        }
        if (preg_match('/^\d{7,11}$/', $phone)) {
            return $phone;
        }
        return false;
    }

    public static function validateName($name) {
        $name = trim($name ?? '');
        if (strlen($name) < 1 || strlen($name) > 100) return false;
        return $name;
    }

    public static function validatePassword($password) {
        if (strlen($password) < 8) return 'Password must be at least 8 characters.';
        if (!preg_match('/[A-Z]/', $password)) return 'Must include an uppercase letter.';
        if (!preg_match('/[a-z]/', $password)) return 'Must include a lowercase letter.';
        if (!preg_match('/[0-9]/', $password)) return 'Must include a number.';
        return true;
    }

    public static function validateQRCode($qrValue) {
        $qrValue = trim($qrValue ?? '');
        return preg_match('/^[A-Za-z0-9\-_]{5,255}$/', $qrValue) ? $qrValue : false;
    }

    /**
     * Validates an uploaded image ($_FILES entry) and moves it into $destDir.
     * Returns the stored filename on success, or an array ['error' => message] on failure.
     */
    public static function handleImageUpload($file, $destDir, $maxBytes = 3145728) {
        if (!isset($file) || !is_array($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // nothing uploaded, not an error
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload failed. Please try again.'];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return ['error' => 'Invalid upload.'];
        }

        if ($file['size'] > $maxBytes) {
            return ['error' => 'Image is too large. Maximum size is ' . round($maxBytes / 1048576, 1) . 'MB.'];
        }

        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['error' => 'The uploaded file is not a valid image.'];
        }

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];
        $mime = $imageInfo['mime'];
        if (!isset($allowedMimes[$mime])) {
            return ['error' => 'Only JPG, PNG, WEBP, or GIF images are allowed.'];
        }

        if (!is_dir($destDir)) {
            @mkdir($destDir, 0755, true);
        }

        $ext = $allowedMimes[$mime];
        $filename = 'img_' . bin2hex(random_bytes(16)) . '.' . $ext;
        $destPath = rtrim($destDir, '/') . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['error' => 'Failed to save the uploaded image.'];
        }

        return $filename;
    }
}