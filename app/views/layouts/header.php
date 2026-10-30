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
    <link rel="stylesheet" href="<?= publicUrl('css/style.css') ?>?v=<?= filemtime(ROOT_PATH . '/public/css/style.css') ?>">
    <?php if (isset($extraCss)): foreach ((array)$extraCss as $css): ?>
        <link rel="stylesheet" href="<?= publicUrl('css/' . $css) ?>">
    <?php endforeach; endif; ?>
    <meta name="csrf-token" content="<?= generateCSRFToken() ?>">
</head>
<body>
<script>
// Apply saved theme immediately to prevent flash of wrong theme
(function() {
    var theme = localStorage.getItem('resc_qr_theme') || 'light';
    document.documentElement.setAttribute('data-theme', theme);
})();

// Theme toggle — inline so it works even if app.js is cached
function getTheme() {
    return document.documentElement.getAttribute('data-theme') || 'light';
}

function toggleTheme() {
    var current = getTheme();
    var next = (current === 'dark') ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('resc_qr_theme', next);
    syncThemeUI(next);
}

function syncThemeUI(theme) {
    var sidebarSwitch = document.getElementById('sidebarThemeSwitch');
    if (sidebarSwitch) {
        sidebarSwitch.checked = (theme === 'dark');
    }
    var sidebarLabel = document.getElementById('sidebarThemeLabel');
    if (sidebarLabel) {
        sidebarLabel.textContent = theme === 'dark' ? 'Dark Mode' : 'Light Mode';
    }
    var sidebarIcon = document.getElementById('sidebarThemeIcon');
    if (sidebarIcon) {
        sidebarIcon.className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    }
}
</script>
<script>
    const BASE_URL = '<?= BASE_URL ?>';
    const CSRF_TOKEN = '<?= generateCSRFToken() ?>';
</script>
<script src="<?= publicUrl('js/app.js') ?>?v=<?= time() ?>"></script>
<script src="<?= publicUrl('js/qrcode-generator.min.js') ?>"></script>
<script>
/**
 * Render a QR code onto a canvas element or create a data URL.
 * Uses the qrcode-generator library (client-side, zero network requests).
 * @param {string} data    - The string to encode (e.g. "RESC-STU-12345-1698000000")
 * @param {number} size    - Desired pixel size of the output image
 * @returns {string}       - A data:image/png;base64,... URL
 */
function generateQRDataUrl(data, size) {
    size = size || 200;
    var qr = qrcode(0, 'M');
    qr.addData(data);
    qr.make();
    var moduleCount = qr.getModuleCount();
    var cellSize = Math.max(1, Math.floor(size / moduleCount));
    var actualSize = cellSize * moduleCount;
    var margin = Math.floor((size - actualSize) / 2);
    var canvas = document.createElement('canvas');
    canvas.width = size;
    canvas.height = size;
    var ctx = canvas.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, size, size);
    ctx.fillStyle = '#000000';
    for (var r = 0; r < moduleCount; r++) {
        for (var c = 0; c < moduleCount; c++) {
            if (qr.isDark(r, c)) {
                ctx.fillRect(margin + c * cellSize, margin + r * cellSize, cellSize, cellSize);
            }
        }
    }
    return canvas.toDataURL('image/png');
}

/**
 * Find all elements with data-qr attribute and render QR codes into them.
 * Call this after page load or after dynamically inserting QR elements.
 */
function renderAllQRCodes(container) {
    var root = container || document;
    var imgs = root.querySelectorAll('img[data-qr]');
    for (var i = 0; i < imgs.length; i++) {
        var img = imgs[i];
        if (img.src && img.src.indexOf('data:') === 0) continue; // already rendered
        var data = img.getAttribute('data-qr');
        var size = parseInt(img.getAttribute('data-qr-size') || '200', 10);
        if (data && data !== 'NONE') {
            img.src = generateQRDataUrl(data, size);
        }
    }
}

document.addEventListener('DOMContentLoaded', function() { renderAllQRCodes(); });
</script>
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
         <a href="<?= baseUrl('auth/logout') ?>" class="topbar-btn" title="Logout" id="logoutBtn" onclick="event.preventDefault(); document.getElementById('logoutModal').classList.add('active');" style="display:flex;align-items:center;gap:6px;">
          <i class="fas fa-sign-out-alt"></i>
          <span>Logout</span>
         </a>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="logout-modal-overlay" onclick="if(event.target===this) this.classList.remove('active');">
        <div class="logout-modal">
            <div class="logout-modal-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <h3 class="logout-modal-title">Confirm Logout</h3>
            <p class="logout-modal-text">Are you sure you want to sign out? You will need to log in again to access your account.</p>
            <div class="logout-modal-actions">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('logoutModal').classList.remove('active');">Cancel</button>
                <a href="<?= baseUrl('auth/logout') ?>" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <style>
        .logout-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }
        .logout-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .logout-modal {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 32px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            transform: scale(0.9) translateY(20px);
            transition: transform 0.25s ease;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .logout-modal-overlay.active .logout-modal {
            transform: scale(1) translateY(0);
        }
        .logout-modal-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.12);
            color: var(--accent-danger);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 20px;
        }
        .logout-modal-title {
            font-size: 18px;
            font-weight: 800;
            margin: 0 0 8px;
            color: var(--text-primary);
        }
        .logout-modal-text {
            font-size: 14px;
            color: var(--text-muted);
            margin: 0 0 24px;
            line-height: 1.5;
        }
        .logout-modal-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .logout-modal-actions .btn {
            min-width: 110px;
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .logout-modal-actions .btn-danger {
            background: var(--accent-danger);
            color: #fff;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .logout-modal-actions .btn-danger:hover {
            filter: brightness(1.15);
            transform: translateY(-1px);
        }
        .logout-modal-actions .btn-secondary:hover {
            transform: translateY(-1px);
        }
    </style>
    
    <div class="page-content">
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" id="flashAlert">
                <i class="fas fa-<?= $flash['type'] === 'error' ? 'exclamation-circle' : ($flash['type'] === 'success' ? 'check-circle' : 'info-circle') ?>"></i>
                <span><?= e($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <div class="toast-container" id="toastContainer"></div>