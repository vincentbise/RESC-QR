<div class="page-header">
    <div>
        <h1><i class="fas fa-phone-alt text-danger"></i> Emergency Hotlines</h1>
        <p>Quick-dial emergency numbers for immediate assistance</p>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));">
    <?php foreach ($hotlines as $h): ?>
    <div class="card" style="transition:all 0.3s ease;">
        <div class="card-body" style="display:flex;align-items:center;gap:20px;padding:24px;">
            <div style="width:56px;height:56px;border-radius:50%;background:rgba(239,68,68,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-phone-alt" style="font-size:22px;color:var(--accent-danger);"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:16px;margin-bottom:4px;"><?= e($h['name']) ?></div>
                <div style="font-size:20px;font-weight:800;color:var(--accent-primary);letter-spacing:0.5px;"><?= e($h['number']) ?></div>
            </div>
            <a href="tel:<?= e($h['number']) ?>" class="btn btn-danger" style="flex-shrink:0;">
                <i class="fas fa-phone"></i> Call
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card" style="margin-top:20px;">
    <div class="card-header">
        <h3><i class="fas fa-info-circle"></i> Important Reminders</h3>
    </div>
    <div class="card-body">
        <ul style="list-style:none;padding:0;display:grid;gap:12px;">
            <li class="d-flex align-center gap-1">
                <i class="fas fa-check-circle text-success"></i>
                <span>Stay calm and follow the evacuation plan</span>
            </li>
            <li class="d-flex align-center gap-1">
                <i class="fas fa-check-circle text-success"></i>
                <span>Proceed to the nearest designated evacuation area</span>
            </li>
            <li class="d-flex align-center gap-1">
                <i class="fas fa-check-circle text-success"></i>
                <span>Scan all students in your class using the QR Scanner</span>
            </li>
            <li class="d-flex align-center gap-1">
                <i class="fas fa-check-circle text-success"></i>
                <span>Report any missing or injured students immediately</span>
            </li>
            <li class="d-flex align-center gap-1">
                <i class="fas fa-check-circle text-success"></i>
                <span>Do not re-enter buildings until declared safe</span>
            </li>
        </ul>
    </div>
</div>