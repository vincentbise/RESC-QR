

<div class="page-header">
    <div>
        <h1>Welcome, <?= e($student['first_name'] ?? '') ?></h1>
        <p>Your emergency status dashboard</p>
    </div>
</div>

<?php if ($status): ?>
<div class="card" style="margin-bottom:24px;border-color:<?= $status['status'] === 'Safe' ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.3)' ?>;">
    <div class="card-body" style="text-align:center;padding:40px;">
        <div style="width:80px;height:80px;border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;font-size:36px;
            background:<?= $status['status'] === 'Safe' ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.12)' ?>;
            color:<?= $status['status'] === 'Safe' ? 'var(--accent-success)' : 'var(--accent-danger)' ?>;">
            <i class="fas fa-<?= $status['status'] === 'Safe' ? 'shield-alt' : 'exclamation-triangle' ?>"></i>
        </div>
        <div style="font-size:32px;font-weight:800;margin-bottom:8px;color:<?= $status['status'] === 'Safe' ? 'var(--accent-success)' : 'var(--accent-danger)' ?>;">
            <?= e($status['status']) ?>
        </div>
        <div class="text-muted">
            <?= e($status['event_type']) ?> — <?= date('M d, Y g:i A', strtotime($status['event_datetime'])) ?>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <span>No active emergency event. Your status will appear here during emergencies.</span>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-user"></i> My Info</h3></div>
        <div class="card-body">
            <table style="width:100%;">
                <tr><td class="text-muted" style="padding:8px 0;width:120px;">Name</td><td style="padding:8px 0;"><strong><?= e(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></strong></td></tr>
                <tr><td class="text-muted" style="padding:8px 0;">Course</td><td style="padding:8px 0;"><?= e($student['course'] ?? '') ?></td></tr>
                <tr><td class="text-muted" style="padding:8px 0;">Year</td><td style="padding:8px 0;"><?= e($student['year_level'] ?? '') ?></td></tr>
                <tr><td class="text-muted" style="padding:8px 0;">Section</td><td style="padding:8px 0;"><span class="badge badge-info"><?= e($student['section_name'] ?? '') ?></span></td></tr>
                <tr><td class="text-muted" style="padding:8px 0;">Email</td><td style="padding:8px 0;"><?= e($student['email'] ?? '—') ?></td></tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3><i class="fas fa-qrcode"></i> My QR Code</h3></div>
        <div class="card-body" style="text-align:center;">
            <img src="<?= generateQRCodeUrl($student['qr_code_value'] ?? 'NONE', 180) ?>"
                 alt="My QR Code"
                 style="width:180px;height:180px;border-radius:12px;background:#fff;padding:8px;margin-bottom:12px;">
            <div><code style="color:var(--accent-primary);font-size:12px;"><?= e($student['qr_code_value'] ?? '') ?></code></div>
            <p class="text-muted mt-1" style="font-size:12px;">Show this to your class mayor during evacuation</p>
        </div>
    </div>
</div>
