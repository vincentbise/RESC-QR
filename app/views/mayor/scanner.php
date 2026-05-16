<?php if (!$activeEvent): ?>
<div class="alert alert-warning">
    <i class="fas fa-info-circle"></i>
    <span><strong>No active emergency event.</strong> The scanner requires an active event to log scans. Ask an administrator to declare an emergency.</span>
</div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-camera"></i> QR Scanner</h1>
        <p>Scan student QR codes to mark them as Safe</p>
    </div>
    <?php if ($activeEvent): ?>
        <span class="badge badge-danger" style="font-size:14px;padding:8px 16px;animation:pulse 2s infinite;">
            ● <?= e($activeEvent['event_type']) ?> ACTIVE
        </span>
    <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-camera"></i> Camera Scanner</h3>
            <button class="btn btn-sm btn-primary" id="startScanBtn" onclick="startScanner()">
                <i class="fas fa-play"></i> Start
            </button>
        </div>
        <div class="card-body" style="text-align:center;">
            <div id="qr-reader" style="width:100%;max-width:400px;margin:0 auto;border-radius:12px;overflow:hidden;"></div>
            <div id="scannerPlaceholder" style="padding:60px 20px;color:var(--text-muted);">
                <i class="fas fa-camera" style="font-size:48px;opacity:0.3;margin-bottom:16px;display:block;"></i>
                <p>Click "Start" to activate camera</p>
            </div>

            <div style="margin-top:20px;">
                <div class="form-group" style="text-align:left;">
                    <label>Or enter QR code manually:</label>
                    <div class="d-flex gap-1">
                        <input type="text" class="form-control" id="manualQR" placeholder="RESC-STU-XXXXX">
                        <button class="btn btn-primary" onclick="manualScan()">
                            <i class="fas fa-search"></i> Scan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <h3><i class="fas fa-check-circle text-success"></i> Last Scan Result</h3>
            </div>
            <div class="card-body" id="scanResult">
                <div class="empty-state" style="padding:40px;">
                    <i class="fas fa-qrcode" style="font-size:36px;"></i>
                    <p>Waiting for scan...</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Scan History</h3>
                <span class="badge badge-info" id="historyCount">0 scans</span>
            </div>
            <div class="card-body" style="padding:0;max-height:300px;overflow-y:auto;">
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
.scan-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: 12px; padding: 24px; text-align: center; }
.scan-success i { font-size: 48px; color: var(--accent-success); margin-bottom: 12px; }
.scan-fail { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 12px; padding: 24px; text-align: center; }
.scan-fail i { font-size: 48px; color: var(--accent-danger); margin-bottom: 12px; }
</style>
