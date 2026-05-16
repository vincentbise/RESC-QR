

<div class="page-header">
    <div>
        <h1>QR Codes</h1>
        <p>View and manage student QR codes for emergency scanning</p>
    </div>
    <div class="d-flex gap-1">
        <select class="form-control" id="qrClassFilter" style="width:200px;padding:10px 12px;">
            <option value="">All Classes</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?= e($c['class_id']) ?>" <?= $classId == $c['class_id'] ? 'selected' : '' ?>><?= e($c['section_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Print All
        </button>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));" id="qrGrid">
    <?php if (empty($students)): ?>
        <div class="empty-state" style="grid-column:1/-1;">
            <i class="fas fa-qrcode"></i>
            <h3>No Students Found</h3>
            <p>Register students first to generate QR codes.</p>
        </div>
    <?php else: ?>
        <?php foreach ($students as $s): ?>
        <div class="card qr-card" style="text-align:center;">
            <div class="card-body" style="padding:20px;">
                <img src="<?= generateQRCodeUrl($s['qr_code_value'] ?? 'NONE', 160) ?>"
                     alt="QR Code for <?= e($s['first_name']) ?>"
                     style="width:160px;height:160px;border-radius:8px;background:#fff;padding:8px;margin-bottom:12px;"
                     loading="lazy">
                <div style="font-weight:700;font-size:14px;margin-bottom:2px;">
                    <?= e($s['first_name'] . ' ' . $s['last_name']) ?>
                </div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">
                    <?= e($s['section_name']) ?>
                </div>
                <code style="font-size:10px;color:var(--accent-primary);"><?= e($s['qr_code_value'] ?? '') ?></code>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
document.getElementById('qrClassFilter').addEventListener('change', function() {
    const classId = this.value;
    window.location.href = BASE_URL + '/qr' + (classId ? '?class_id=' + classId : '');
});
</script>

<style>
@media print {
    .sidebar, .topbar, .page-header, #qrClassFilter { display:none !important; }
    .main-content { margin-left:0 !important; }
    .qr-card { break-inside:avoid; border:1px solid #ccc !important; }
    body { background:#fff; color:#000; }
    .card-body code { color:#333 !important; }
}
</style>
