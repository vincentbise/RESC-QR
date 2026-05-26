<?php if (!$activeEvent): ?>
<div class="alert alert-warning">
    <i class="fas fa-info-circle"></i>
    <span><strong>No active emergency event.</strong> The scanner requires an active event to log scans. Ask an administrator to declare an emergency.</span>
</div>
<?php endif; ?>

<?php if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'on' || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'https')): ?>
<?php /* Only show on iOS — handled by JS below */ ?>
<?php endif; ?>

<!-- jsQR loaded early so it is ready before startScanner() is called -->
<script src="<?= publicUrl('js/jsqr.min.js') ?>"></script>

<div class="scanner-page">
    <div class="scanner-top-bar">
        <div class="scanner-top-bar__left">
            <h1><i class="fas fa-camera"></i> QR Scanner</h1>
            <p class="scanner-subtitle">Scan student QR codes to mark Safe</p>
        </div>
        <?php if ($activeEvent): ?>
            <span class="event-badge-live">
                <span class="event-badge-live__dot"></span>
                <?= e($activeEvent['event_type']) ?> ACTIVE
            </span>
        <?php endif; ?>
    </div>

    <!-- iOS HTTPS notice -->
    <div id="httpsNotice" class="alert alert-warning" style="display:none;">
        <i class="fas fa-exclamation-triangle"></i>
        <span><strong>Camera requires HTTPS on iOS.</strong> Open this page over HTTPS for the camera to work. Use manual entry below as a fallback.</span>
    </div>

    <!-- Camera Scanner -->
    <div class="scanner-hero">
        <div class="scanner-hero__card card">
            <div class="scanner-hero__viewfinder">
                <div id="qr-reader"></div>
                <div id="scannerPlaceholder" class="scanner-hero__placeholder">
                    <div class="scanner-hero__placeholder-inner">
                        <div class="scanner-hero__corners">
                            <span class="corner corner--tl"></span>
                            <span class="corner corner--tr"></span>
                            <span class="corner corner--bl"></span>
                            <span class="corner corner--br"></span>
                        </div>
                        <i class="fas fa-qrcode"></i>
                        <p>Tap the button below to activate the camera</p>
                    </div>
                </div>
            </div>
            <div class="scanner-hero__controls">
                <button class="btn-scan-toggle" id="startScanBtn" onclick="startScanner()">
                    <i class="fas fa-play"></i>
                    <span>Start Scanning</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Scan Result -->
    <div class="scan-result-panel" id="scanResultPanel">
        <div class="card">
            <div class="card-header scan-result-panel__header">
                <h3><i class="fas fa-check-circle text-success"></i> Last Scan Result</h3>
            </div>
            <div class="card-body" id="scanResult">
                <div class="empty-state scan-result-panel__empty">
                    <i class="fas fa-qrcode"></i>
                    <p>Waiting for scan&hellip;</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Input & History -->
    <div class="scanner-secondary">
        <div class="card scanner-manual">
            <div class="card-header">
                <h3><i class="fas fa-keyboard"></i> Manual Entry</h3>
            </div>
            <div class="card-body">
                <div class="scanner-manual__row">
                    <input type="text" class="form-control" id="manualQR"
                           placeholder="RESC-STU-XXXXX"
                           aria-label="Manual QR code input"
                           autocomplete="off"
                           autocorrect="off"
                           autocapitalize="characters">
                    <button class="btn btn-primary" onclick="manualScan()">
                        <i class="fas fa-search"></i> Scan
                    </button>
                </div>
            </div>
        </div>

        <div class="card scanner-history">
            <div class="card-header scanner-history__header" id="historyToggle" role="button" tabindex="0" aria-expanded="true">
                <h3><i class="fas fa-list"></i> Scan History</h3>
                <div class="scanner-history__meta">
                    <span class="badge badge-info" id="historyCount">0 scans</span>
                    <i class="fas fa-chevron-down scanner-history__chevron"></i>
                </div>
            </div>
            <div class="card-body scanner-history__body" id="historyBody" style="padding:0;max-height:320px;overflow-y:auto;">
                <table>
                    <thead>
                        <tr><th>Student</th><th>Time</th><th>Status</th></tr>
                    </thead>
                    <tbody id="scanHistory"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.5;} }
</style>

<script>
(function() {
    var ios = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    if (ios && location.protocol !== 'https:') {
        document.getElementById('httpsNotice').style.display = 'flex';
    }
})();
</script>