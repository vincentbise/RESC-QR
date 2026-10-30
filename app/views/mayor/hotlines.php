<style>
    .hotline-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    .hotline-card { height: 100%; }
    .hotline-card .card-body {
        display: flex;
        flex-direction: column;
        gap: 18px;
        height: 100%;
        padding: 22px 20px;
    }
    .hotline-top {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
    }
    .hotline-icon {
        width: 50px;
        height: 50px;
        flex-shrink: 0;
        border-radius: 50%;
        background: rgba(239, 68, 68, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hotline-icon i { font-size: 20px; color: var(--accent-danger); }
    .fa-phone-alt, .fa-phone { transform: scaleX(-1); }
    .hotline-info { min-width: 0; text-align: left; }
    .hotline-name {
        font-weight: 700;
        font-size: 15px;
        line-height: 1.3;
        margin-bottom: 4px;
        color: var(--text-primary);
    }
    .hotline-number {
        font-size: 18px;
        font-weight: 800;
        letter-spacing: 0.3px;
        line-height: 1.2;
        color: var(--accent-primary);
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }
    @media (max-width: 1100px) { .hotline-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px)  { .hotline-grid { grid-template-columns: 1fr; } }
    .hotline-card .btn {
        width: 100%;
        justify-content: center;
        white-space: nowrap;
    }
</style>

<div class="page-header">
    <div>
        <h1>Emergency Hotlines</h1>
        <p>Quick-dial emergency numbers for immediate assistance</p>
    </div>
</div>

<div class="hotline-grid">
    <?php foreach ($hotlines as $h): ?>
    <div class="card hotline-card">
        <div class="card-body">
            <div class="hotline-top">
                <div class="hotline-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <div class="hotline-info">
                    <div class="hotline-name"><?= e($h['name']) ?></div>
                    <div class="hotline-number"><?= e($h['number']) ?></div>
                </div>
            </div>
            <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $h['number'])) ?>" class="btn btn-danger">
                <i></i> Call
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