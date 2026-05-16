

<div class="page-header">
    <div>
        <h1>My QR Code</h1>
        <p>Present this QR code to your class mayor during emergency evacuations</p>
    </div>
</div>

<div class="card" style="max-width:500px;margin:0 auto;">
    <div class="card-body" style="text-align:center;padding:48px 40px;">
        <img src="<?= generateQRCodeUrl($student['qr_code_value'] ?? 'NONE', 280) ?>"
             alt="QR Code"
             style="width:280px;height:280px;border-radius:16px;background:#fff;padding:12px;margin-bottom:20px;box-shadow:0 8px 32px rgba(0,0,0,0.3);">

        <div style="font-size:22px;font-weight:800;margin-bottom:4px;">
            <?= e(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?>
        </div>

        <div style="margin-top:8px;">
            <code style="font-size:14px;color:var(--accent-primary);background:rgba(99,102,241,0.1);padding:6px 16px;border-radius:8px;">
                <?= e($student['qr_code_value'] ?? '') ?>
            </code>
        </div>

        <div class="alert alert-info" style="margin-top:24px;text-align:left;">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>How to use:</strong><br>
                During an earthquake emergency, show this QR code to your class mayor at the evacuation area. They will scan it to mark you as <strong>Safe</strong>.
            </div>
        </div>

        <button class="btn btn-primary mt-2" onclick="toggleFullscreen()" style="width:100%;">
            <i class="fas fa-expand"></i> Fullscreen QR Code
        </button>
    </div>
</div>

<div id="fullscreenQR" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:#fff;z-index:9999;justify-content:center;align-items:center;flex-direction:column;cursor:pointer;" onclick="toggleFullscreen()">
    <img src="<?= generateQRCodeUrl($student['qr_code_value'] ?? 'NONE', 400) ?>" style="width:80vmin;height:80vmin;max-width:400px;max-height:400px;">
    <div style="color:#333;font-size:20px;font-weight:700;margin-top:20px;"><?= e(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></div>
    <div style="color:#666;font-size:14px;margin-top:8px;">Tap anywhere to close</div>
</div>

<script>
function toggleFullscreen() {
    const el = document.getElementById('fullscreenQR');
    if (el.style.display === 'none') {
        el.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    } else {
        el.style.display = 'none';
        document.body.style.overflow = '';
    }
}
</script>
