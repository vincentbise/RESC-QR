<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Dashboard') ?> — <?= APP_NAME ?></title>
    <meta name="description" content="<?= APP_FULL_NAME ?> - Emergency student monitoring system">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= publicUrl('css/style.css') ?>">
    <?php if (isset($extraCss)): foreach ((array)$extraCss as $css): ?>
        <link rel="stylesheet" href="<?= publicUrl('css/' . $css) ?>">
    <?php endforeach; endif; ?>
    <meta name="csrf-token" content="<?= generateCSRFToken() ?>">
</head>
<body>
<div class="app-layout">

<?php require_once ROOT_PATH . '/app/views/layouts/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle" id="menuToggle" title="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <span class="page-title"><?= e($pageTitle ?? 'Dashboard') ?></span>
        </div>
        <div class="topbar-right">
            <button class="topbar-btn" title="Notifications" id="notifBtn">
                <i class="fas fa-bell"></i>
                <span class="badge-dot" id="notifDot" style="display:none;"></span>
            </button>
            <a href="<?= baseUrl('auth/logout') ?>" class="topbar-btn" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>

    <div class="page-content">
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" id="flashAlert">
                <i class="fas fa-<?= $flash['type'] === 'error' ? 'exclamation-circle' : ($flash['type'] === 'success' ? 'check-circle' : 'info-circle') ?>"></i>
                <span><?= e($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <div class="toast-container" id="toastContainer"></div>
