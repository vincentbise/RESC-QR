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

        <?php // ========== MAIN (Dashboard — always visible) ========== ?>
        <?php if ($role === 'admin'): ?>
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="<?= baseUrl('dashboard') ?>" class="nav-link <?= isActive('dashboard') ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
        </div>
        <?php else: ?>
        <div class="nav-section">
            <div class="nav-section-title">My Account</div>
            <a href="<?= baseUrl('studentview') ?>" class="nav-link <?= isActive('studentview') ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <?php if (hasPermission('perm_my_qr')): ?>
            <a href="<?= baseUrl('studentview/qrcode') ?>" class="nav-link <?= isActive('studentview/qrcode') ?>">
                <i class="fas fa-qrcode"></i> My QR Code
            </a>
            <?php endif; ?>
            <a href="<?= baseUrl('studentview/profile') ?>" class="nav-link <?= isActive('studentview/profile') ?>">
                <i class="fas fa-user-edit"></i> Profile
            </a>
        </div>
        <?php endif; ?>

        <?php // ========== EMERGENCY SECTION ========== ?>
        <?php if (hasPermission('perm_qr_scanner') || hasPermission('perm_student_list_mayor')): ?>
        <div class="nav-section">
            <div class="nav-section-title">Emergency</div>
            <?php if (hasPermission('perm_qr_scanner')): ?>
            <a href="<?= baseUrl('scan') ?>" class="nav-link <?= isActive('scan') ?>">
                <i class="fas fa-camera"></i> QR Scanner
            </a>
            <?php endif; ?>
            <?php if (hasPermission('perm_student_list_mayor')): ?>
            <a href="<?= baseUrl('scan/students') ?>" class="nav-link <?= isActive('scan/students') ?>">
                <i class="fas fa-users"></i> Student List
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php // ========== MANAGEMENT SECTION ========== ?>
        <?php if (hasPermission('perm_student_list_admin') || hasPermission('perm_qr_codes') || hasPermission('perm_declare_emergency')): ?>
        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <?php if (hasPermission('perm_student_list_admin')): ?>
            <a href="<?= baseUrl('student') ?>" class="nav-link <?= isActive('student') ?>">
                <i class="fas fa-user-graduate"></i> Students
            </a>
            <?php endif; ?>
            <?php if (hasPermission('perm_qr_codes')): ?>
            <a href="<?= baseUrl('qr') ?>" class="nav-link <?= isActive('qr') ?>">
                <i class="fas fa-qrcode"></i> QR Codes
            </a>
            <?php endif; ?>
            <?php if (hasPermission('perm_declare_emergency')): ?>
            <a href="<?= baseUrl('event') ?>" class="nav-link <?= isActive('event') ?>">
                <i class="fas fa-exclamation-triangle"></i> Events
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php // ========== REPORTS SECTION ========== ?>
        <?php if (hasPermission('perm_generate_report')): ?>
        <div class="nav-section">
            <div class="nav-section-title">Reports</div>
            <a href="<?= baseUrl('report') ?>" class="nav-link <?= isActive('report') ?>">
                <i class="fas fa-file-alt"></i> Reports
            </a>
            <a href="<?= baseUrl('report/scanlogs') ?>" class="nav-link <?= isActive('report/scanlogs') ?>">
                <i class="fas fa-history"></i> Scan Logs
            </a>
        </div>
        <?php endif; ?>

        <?php // ========== QUICK ACTIONS SECTION ========== ?>
        <?php if (hasPermission('perm_emergency_hotlines')): ?>
        <div class="nav-section">
            <div class="nav-section-title">Quick Actions</div>
            <a href="<?= baseUrl('scan/hotlines') ?>" class="nav-link <?= isActive('scan/hotlines') ?>">
                <i class="fas fa-phone-alt fa-flip-horizontal"></i> Emergency Hotlines
            </a>
        </div>
        <?php endif; ?>

        <?php // ========== ADMINISTRATION (admin only) ========== ?>
        <?php if ($role === 'admin'): ?>
        <div class="nav-section">
            <div class="nav-section-title">Administration</div>
            <a href="<?= baseUrl('classroom') ?>" class="nav-link <?= isActive('classroom') ?>">
                <i class="fas fa-chalkboard-teacher"></i> Classrooms
            </a>
            <a href="<?= baseUrl('rbac') ?>" class="nav-link <?= isActive('rbac') ?>">
                <i class="fas fa-user-shield"></i> Access Control
            </a>
        </div>
        <?php endif; ?>

    </nav>

    <div class="sidebar-footer">
        <div class="theme-toggle" id="sidebarThemeToggle">
            <i class="fas fa-sun theme-toggle-icon" id="sidebarThemeIcon"></i>
            <span class="theme-toggle-label" id="sidebarThemeLabel">Light Mode</span>
            <span class="theme-switch">
                <input type="checkbox" id="sidebarThemeSwitch" tabindex="-1">
                <span class="slider"></span>
            </span>
        </div>
        <script>document.getElementById('sidebarThemeToggle').addEventListener('click', function(e) { e.preventDefault(); toggleTheme(); });</script>
        <div class="user-card">
            <div class="user-avatar" id="sidebarUserAvatar"style="overflow:hidden;">
                <?php if (getUserAvatar()): ?>
                    <img src="<?= e(publicUrl('img/profiles/' . getUserAvatar())) ?>" alt="Profile photo" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>
                    <?= $initials ?>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <div class="name" id="sidebarUserName"><?= e(getUserName()) ?></div>
                <div class="role"><?= e(getUserRole()) ?></div>
            </div>

        </div>
    </div>
</aside>
