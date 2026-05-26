/* ============================================
   RESC-QR Scanner — iOS + Android compatible
   jsQR loaded via <script> tag in header.
   getUserMedia + canvas — no wrapper library.
   ============================================ */

var videoStream  = null;
var scanLoop     = null;
var scanCount    = 0;
var lastScanTime = 0;
var isScanning   = false;

function isIOS() {
    return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
}

function buildViewfinder() {
    var container = document.getElementById('qr-reader');
    container.innerHTML = '';

    var video = document.createElement('video');
    video.id = 'qr-video';
    video.setAttribute('playsinline', '');
    video.setAttribute('autoplay', '');
    video.setAttribute('muted', '');
    video.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block;';
    container.appendChild(video);

    var canvas = document.createElement('canvas');
    canvas.id = 'qr-canvas';
    canvas.style.display = 'none';
    container.appendChild(canvas);

    return { video: video, canvas: canvas };
}

async function startScanner() {
    if (isScanning) return;

    /* jsQR must be loaded — it comes from the <script> tag in the page */
    if (typeof jsQR === 'undefined') {
        App.toast('QR library not loaded. Please refresh the page.', 'error');
        return;
    }

    var placeholder = document.getElementById('scannerPlaceholder');
    if (placeholder) placeholder.style.display = 'none';

    var els    = buildViewfinder();
    var video  = els.video;
    var canvas = els.canvas;

    var constraints = {
        video: {
            facingMode: isIOS() ? { exact: 'environment' } : 'environment',
            width:  { ideal: 1280 },
            height: { ideal: 720 }
        },
        audio: false
    };

    try {
        videoStream = await navigator.mediaDevices.getUserMedia(constraints);
    } catch(err) {
        /* Retry without exact — handles front-camera-only or permission edge cases */
        try {
            videoStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' },
                audio: false
            });
        } catch(err2) {
            App.toast('Camera access denied. Please allow camera in your browser settings.', 'error');
            if (placeholder) placeholder.style.display = '';
            return;
        }
    }

    video.srcObject = videoStream;
    try { await video.play(); } catch(e) {}

    isScanning = true;

    var btn = document.getElementById('startScanBtn');
    btn.innerHTML = '<i class="fas fa-stop"></i><span>Stop Scanning</span>';
    btn.onclick = stopScanner;
    btn.classList.add('is-active');

    tickScan(video, canvas);
}

function tickScan(video, canvas) {
    if (!isScanning) return;

    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        var code = jsQR(imageData.data, imageData.width, imageData.height, {
            inversionAttempts: 'dontInvert'
        });

        if (code && code.data) {
            var now = Date.now();
            if (now - lastScanTime > 2000) {
                lastScanTime = now;
                stopScanner();
                processScan(code.data);
                return;
            }
        }
    }

    scanLoop = requestAnimationFrame(function() { tickScan(video, canvas); });
}

function stopScanner() {
    isScanning = false;

    if (scanLoop) { cancelAnimationFrame(scanLoop); scanLoop = null; }

    if (videoStream) {
        videoStream.getTracks().forEach(function(t) { t.stop(); });
        videoStream = null;
    }

    var v = document.getElementById('qr-video');
    if (v) { v.srcObject = null; v.remove(); }

    var c = document.getElementById('qr-canvas');
    if (c) c.remove();

    var btn = document.getElementById('startScanBtn');
    if (btn) {
        btn.innerHTML = '<i class="fas fa-play"></i><span>Start Scanning</span>';
        btn.onclick = startScanner;
        btn.classList.remove('is-active');
    }

    var placeholder = document.getElementById('scannerPlaceholder');
    if (placeholder) placeholder.style.display = '';
}

function manualScan() {
    var input = document.getElementById('manualQR');
    var qr = input.value.trim();
    if (!qr) { App.toast('Please enter a QR code value.', 'error'); return; }
    processScan(qr);
    input.value = '';
}

async function processScan(qrCode) {
    var resultDiv = document.getElementById('scanResult');
    resultDiv.innerHTML =
        '<div style="text-align:center;padding:40px;">' +
            '<i class="fas fa-circle-notch fa-spin" style="font-size:32px;color:var(--accent-primary);"></i>' +
            '<p style="margin-top:12px;color:var(--text-muted);">Processing...</p>' +
        '</div>';

    var formData = new FormData();
    formData.append('qr_code', qrCode);
    formData.append('csrf_token', CSRF_TOKEN);

    try {
        var data = await App.ajax('/scan/process', { method: 'POST', body: formData });

        if (data.success) {
            resultDiv.innerHTML =
                '<div class="scan-success">' +
                    '<i class="fas fa-check-circle" style="display:block;"></i>' +
                    '<div style="font-size:20px;font-weight:800;margin-bottom:4px;">' + App.escapeHtml(data.student.name) + '</div>' +
                    '<div class="text-muted">' + App.escapeHtml(data.student.section) + '</div>' +
                    '<span class="badge badge-success" style="margin-top:8px;font-size:14px;padding:6px 16px;">&#10003; SAFE</span>' +
                '</div>';
            addToHistory(data.student.name, 'Valid');

        } else if (data.already_safe) {
            resultDiv.innerHTML =
                '<div class="scan-duplicate">' +
                    '<i class="fas fa-shield-alt" style="display:block;"></i>' +
                    '<div style="font-size:20px;font-weight:800;margin-bottom:4px;">' + App.escapeHtml(data.student.name) + '</div>' +
                    '<div class="text-muted">' + App.escapeHtml(data.student.section) + '</div>' +
                    '<span class="badge badge-warning" style="margin-top:8px;font-size:14px;padding:6px 16px;">Already Safe</span>' +
                '</div>';
            addToHistory(data.student.name, 'Duplicate');

        } else {
            resultDiv.innerHTML =
                '<div class="scan-fail">' +
                    '<i class="fas fa-times-circle" style="display:block;"></i>' +
                    '<div style="font-size:16px;font-weight:700;margin-bottom:4px;">Scan Failed</div>' +
                    '<div class="text-muted">' + App.escapeHtml(data.message) + '</div>' +
                '</div>';
            addToHistory(qrCode, 'Invalid');
        }

    } catch(e) {
        resultDiv.innerHTML =
            '<div class="scan-fail">' +
                '<i class="fas fa-wifi" style="display:block;"></i>' +
                '<div style="font-size:16px;font-weight:700;">Connection Error</div>' +
                '<div class="text-muted">Failed to process scan. Check your network.</div>' +
            '</div>';
    }
}

function addToHistory(name, result) {
    scanCount++;
    var time = new Date().toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    var tbody = document.getElementById('scanHistory');
    var badgeMap = { 'Valid': 'success', 'Duplicate': 'warning', 'Invalid': 'danger' };
    var badge = badgeMap[result] || 'danger';

    tbody.insertAdjacentHTML('afterbegin',
        '<tr>' +
            '<td><strong>' + App.escapeHtml(name) + '</strong></td>' +
            '<td class="text-muted">' + time + '</td>' +
            '<td><span class="badge badge-' + badge + '">' + result + '</span></td>' +
        '</tr>'
    );

    document.getElementById('historyCount').textContent = scanCount + ' scans';
}

document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('historyToggle');
    if (!toggle) return;
    toggle.addEventListener('click', function() {
        var body    = document.getElementById('historyBody');
        var chevron = this.querySelector('.scanner-history__chevron');
        var expanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', String(!expanded));
        body.style.display = expanded ? 'none' : '';
        if (chevron) chevron.style.transform = expanded ? 'rotate(-90deg)' : '';
    });
});