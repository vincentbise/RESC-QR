<?php require_once ROOT_PATH . '/app/helpers/qr_helper.php'; ?>

<div class="page-header">
    <div>
        <h1>My QR Code</h1>
        <p>Present this QR code to your class mayor during emergency evacuations</p>
    </div>
</div>

<div class="card" style="max-width:500px;margin:0 auto;">
    <div class="card-body" style="text-align:center;padding:48px 40px;">
        <img id="qrImage"
             src="<?= generateQRCodeUrl($student['qr_code_value'] ?? 'NONE', 280) ?>"
             alt="QR Code"
             crossorigin="anonymous"
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

        <div style="display:flex;gap:10px;margin-top:16px;">
            <button class="btn btn-primary" onclick="toggleFullscreen()" style="flex:1;">
                <i class="fas fa-expand"></i> Fullscreen
            </button>
            <button class="btn btn-success" onclick="downloadQR(this)" style="flex:1;">
                <i class="fas fa-download"></i> Download
            </button>
            <button class="btn btn-secondary" onclick="printQR()" style="flex:1;">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
</div>

<div id="fullscreenQR" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:#fff;z-index:9999;justify-content:center;align-items:center;flex-direction:column;cursor:pointer;" onclick="toggleFullscreen()">
    <img src="<?= generateQRCodeUrl($student['qr_code_value'] ?? 'NONE', 400) ?>" style="width:80vmin;height:80vmin;max-width:400px;max-height:400px;">
    <div style="color:#333;font-size:20px;font-weight:700;margin-top:20px;"><?= e(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></div>
    <div style="color:#666;font-size:14px;margin-top:8px;">Tap anywhere to close</div>
</div>

<!-- Hidden print area -->
<div id="printArea" style="display:none;">
    <div style="text-align:center;font-family:sans-serif;padding:40px;">
        <h2 style="margin-bottom:4px;"><?= e(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></h2>
        <p style="color:#666;font-size:13px;margin-bottom:20px;"><?= e($student['qr_code_value'] ?? '') ?></p>
        <img src="<?= generateQRCodeUrl($student['qr_code_value'] ?? 'NONE', 300) ?>" style="width:300px;height:300px;">
        <p style="color:#888;font-size:12px;margin-top:20px;">RESC-QR Emergency Monitor — Present during evacuations</p>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    #printArea, #printArea * { visibility: visible; }
    #printArea { display: block !important; position: fixed; top: 0; left: 0; width: 100%; }
}
</style>

<script>
const studentName = "<?= e(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?>";
const qrUrl = "<?= generateQRCodeUrl($student['qr_code_value'] ?? 'NONE', 600) ?>";

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

function downloadQR(btn) {
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
    btn.disabled = true;

    fetch(qrUrl)
        .then(res => res.blob())
        .then(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'RESC-QR-' + studentName.replace(/\s+/g, '-') + '.png';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        })
        .catch(() => alert('Download failed. Please try again.'))
        .finally(() => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        });
}

function printQR() {
    window.print();
}
</script>
