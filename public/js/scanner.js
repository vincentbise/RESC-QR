let html5QrCode = null;
let scanCount = 0;
const scanHistory = [];

function loadHtml5QrCode() {
    return new Promise((resolve, reject) => {
        if (window.Html5Qrcode) { resolve(); return; }
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

async function startScanner() {
    const btn = document.getElementById('startScanBtn');

    try {
        await loadHtml5QrCode();
    } catch (e) {
        App.toast('Failed to load QR scanner library.', 'error');
        return;
    }

    const placeholder = document.getElementById('scannerPlaceholder');
    if (placeholder) placeholder.style.display = 'none';

    if (html5QrCode) {
        try { await html5QrCode.stop(); } catch(e) {}
    }

    html5QrCode = new Html5Qrcode('qr-reader');

    btn.innerHTML = '<i class="fas fa-stop"></i> Stop';
    btn.onclick = stopScanner;
    btn.classList.replace('btn-primary', 'btn-danger');

    try {
        await html5QrCode.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            () => {}
        );
    } catch (err) {
        App.toast('Camera access denied or unavailable. Use manual input.', 'error');
        btn.innerHTML = '<i class="fas fa-play"></i> Start';
        btn.onclick = startScanner;
        btn.classList.replace('btn-danger', 'btn-primary');
        if (placeholder) placeholder.style.display = 'block';
    }
}

async function stopScanner() {
    const btn = document.getElementById('startScanBtn');
    if (html5QrCode) {
        try { await html5QrCode.stop(); } catch(e) {}
        html5QrCode = null;
    }
    btn.innerHTML = '<i class="fas fa-play"></i> Start';
    btn.onclick = startScanner;
    btn.classList.replace('btn-danger', 'btn-primary');
}

let lastScanTime = 0;
async function onScanSuccess(decodedText) {
    const now = Date.now();
    if (now - lastScanTime < 2000) return;
    lastScanTime = now;

    await processScan(decodedText);
}

async function manualScan() {
    const input = document.getElementById('manualQR');
    const qr = input.value.trim();
    if (!qr) { App.toast('Please enter a QR code value.', 'error'); return; }
    await processScan(qr);
    input.value = '';
}

async function processScan(qrCode) {
    const resultDiv = document.getElementById('scanResult');
    resultDiv.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-circle-notch fa-spin" style="font-size:32px;color:var(--accent-primary);"></i><p class="mt-1 text-muted">Processing scan...</p></div>';

    const formData = new FormData();
    formData.append('qr_code', qrCode);
    formData.append('csrf_token', CSRF_TOKEN);

    try {
        const data = await App.ajax('/scan/process', { method: 'POST', body: formData });

        if (data.success) {
            resultDiv.innerHTML = `
                <div class="scan-success">
                    <i class="fas fa-check-circle" style="display:block;"></i>
                    <div style="font-size:20px;font-weight:800;margin-bottom:4px;">${data.student.name}</div>
                    <div class="text-muted">${data.student.section}</div>
                    <span class="badge badge-success mt-1" style="font-size:14px;padding:6px 16px;">✓ SAFE</span>
                </div>`;
            addToHistory(data.student.name, 'Valid');
        } else {
            resultDiv.innerHTML = `
                <div class="scan-fail">
                    <i class="fas fa-times-circle" style="display:block;"></i>
                    <div style="font-size:16px;font-weight:700;margin-bottom:4px;">Scan Failed</div>
                    <div class="text-muted">${data.message}</div>
                </div>`;
            addToHistory(qrCode, 'Invalid');
        }
    } catch (e) {
        resultDiv.innerHTML = `
            <div class="scan-fail">
                <i class="fas fa-wifi" style="display:block;"></i>
                <div style="font-size:16px;font-weight:700;">Connection Error</div>
                <div class="text-muted">Failed to process scan. Check your network.</div>
            </div>`;
    }
}

function addToHistory(name, result) {
    scanCount++;
    const time = new Date().toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    const tbody = document.getElementById('scanHistory');
    const badge = result === 'Valid' ? 'success' : 'danger';

    tbody.insertAdjacentHTML('afterbegin', `
        <tr style="animation:slideDown 0.3s ease;">
            <td><strong>${name}</strong></td>
            <td class="text-muted">${time}</td>
            <td><span class="badge badge-${badge}">${result}</span></td>
        </tr>`);

    document.getElementById('historyCount').textContent = scanCount + ' scans';
}
