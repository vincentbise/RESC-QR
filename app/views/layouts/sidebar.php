<?php
$role = getUserRole();
$initials = strtoupper(substr(getUserName(), 0, 2));
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="<?= publicUrl('img/logo.png') ?>" alt="<?= APP_NAME ?>" class="logo-img">
        <div class="logo-text">
            <h2><?= APP_NAME ?></h2>
            <span>Emergency Monitor</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if ($role === 'admin'): ?>
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="<?= baseUrl('dashboard') ?>" class="nav-link <?= isActive('dashboard') ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <a href="<?= baseUrl('student') ?>" class="nav-link <?= isActive('student') ?>">
                <i class="fas fa-user-graduate"></i> Students
            </a>
            <a href="<?= baseUrl('qr') ?>" class="nav-link <?= isActive('qr') ?>">
                <i class="fas fa-qrcode"></i> QR Codes
            </a>
            <a href="<?= baseUrl('event') ?>" class="nav-link <?= isActive('event') ?>">
                <i class="fas fa-exclamation-triangle"></i> Events
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Reports</div>
            <a href="<?= baseUrl('report') ?>" class="nav-link <?= isActive('report') ?>">
                <i class="fas fa-file-alt"></i> Reports
            </a>
            <a href="<?= baseUrl('report/scanlogs') ?>" class="nav-link <?= isActive('report/scanlogs') ?>">
                <i class="fas fa-history"></i> Scan Logs
            </a>
        </div>

        <?php elseif ($role === 'mayor'): ?>
        <div class="nav-section">
            <div class="nav-section-title">Emergency</div>
            <a href="<?= baseUrl('scan') ?>" class="nav-link <?= isActive('scan') ?>">
                <i class="fas fa-camera"></i> QR Scanner
            </a>
            <a href="<?= baseUrl('scan/students') ?>" class="nav-link <?= isActive('scan/students') ?>">
                <i class="fas fa-users"></i> Student List
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Quick Actions</div>
            <a href="<?= baseUrl('scan/hotlines') ?>" class="nav-link <?= isActive('scan/hotlines') ?>">
                <i class="fas fa-phone-alt"></i> Emergency Hotlines
            </a>
        </div>

        <?php elseif ($role === 'student'): ?>
        <div class="nav-section">
            <div class="nav-section-title">My Account</div>
            <a href="<?= baseUrl('studentview') ?>" class="nav-link <?= isActive('studentview') ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="<?= baseUrl('studentview/qrcode') ?>" class="nav-link <?= isActive('studentview/qrcode') ?>">
                <i class="fas fa-qrcode"></i> My QR Code
            </a>
            <a href="<?= baseUrl('studentview/profile') ?>" class="nav-link <?= isActive('studentview/profile') ?>">
                <i class="fas fa-user-edit"></i> Profile
            </a>
        </div>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar"><?= $initials ?></div>
            <div class="user-info">
                <div class="name"><?= e(getUserName()) ?></div>
                <div class="role"><?= e(getUserRole()) ?></div>
            </div>
            <a href="<?= baseUrl('auth/logout') ?>" title="Logout" style="color:var(--text-muted);font-size:14px;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</aside>