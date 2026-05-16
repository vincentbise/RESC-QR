<div class="page-header">
    <div>
        <h1>Student Status</h1>
        <p><?= $activeEvent ? e($activeEvent['event_type']) . ' — ' . date('M d, Y g:i A', strtotime($activeEvent['event_datetime'])) : 'No active event' ?></p>
    </div>
    <button class="btn btn-secondary btn-sm" onclick="refreshStudentList()">
        <i class="fas fa-sync-alt"></i> Refresh
    </button>
</div>

<?php if ($activeEvent): ?>
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
    <div class="stat-card success">
        <div class="stat-header"><div class="stat-icon"><i class="fas fa-shield-alt"></i></div></div>
        <div class="stat-value" id="safeVal"><?= (int)($summary['safe_count'] ?? 0) ?></div>
        <div class="stat-label">Safe</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-header"><div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div></div>
        <div class="stat-value" id="missingVal"><?= (int)($summary['missing_count'] ?? 0) ?></div>
        <div class="stat-label">Missing</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-header"><div class="stat-icon"><i class="fas fa-user-slash"></i></div></div>
        <div class="stat-value" id="notInClassVal"><?= (int)($summary['not_in_class_count'] ?? 0) ?></div>
        <div class="stat-label">Not in Class</div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="d-flex align-center gap-2">
            <h3><i class="fas fa-users"></i> Students</h3>
            <div class="search-bar" style="min-width:200px;">
                <i class="fas fa-search"></i>
                <input type="text" id="statusSearch" placeholder="Search name...">
            </div>
        </div>
        <div class="d-flex gap-1">
            <button class="btn btn-sm btn-success status-filter active" data-filter="all">All</button>
            <button class="btn btn-sm btn-secondary status-filter" data-filter="Safe">Safe</button>
            <button class="btn btn-sm btn-secondary status-filter" data-filter="Missing">Missing</button>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Student</th><th>Section</th><th>Phone</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody id="statusBody">
                    <?php if (empty($students)): ?>
                        <tr><td colspan="5" class="text-center text-muted" style="padding:60px;">No student statuses available</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                        <tr class="status-row" data-status="<?= e($s['status']) ?>">
                            <td>
                                <div class="d-flex align-center gap-1">
                                    <div class="avatar avatar-sm" style="background:hsl(<?= $s['student_id'] * 37 % 360 ?>,60%,50%)">
                                        <?= strtoupper(substr($s['first_name'],0,1) . substr($s['last_name'],0,1)) ?>
                                    </div>
                                    <strong><?= e($s['first_name'] . ' ' . $s['last_name']) ?></strong>
                                </div>
                            </td>
                            <td><span class="badge badge-info"><?= e($s['section_name']) ?></span></td>
                            <td><?= e($s['phone'] ?? '—') ?></td>
                            <td>
                                <span class="badge badge-<?= $s['status'] === 'Safe' ? 'success' : ($s['status'] === 'Missing' ? 'danger' : 'warning') ?>">
                                    <?= e($s['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($s['status'] === 'Missing' && !empty($s['phone'])): ?>
                                    <a href="tel:<?= e($s['phone']) ?>" class="btn btn-sm btn-warning btn-icon" title="Call">
                                        <i class="fas fa-phone"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.status-filter').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.status-filter').forEach(b => { b.classList.remove('active'); b.classList.replace('btn-success','btn-secondary'); });
        btn.classList.add('active');
        btn.classList.replace('btn-secondary','btn-success');
        const filter = btn.dataset.filter;
        document.querySelectorAll('.status-row').forEach(row => {
            row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
        });
    });
});

document.getElementById('statusSearch').addEventListener('input', App.debounce(function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.status-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}, 200));

async function refreshStudentList() {
    try {
        const data = await App.get('/scan/students');
        if (data.success) {
            document.getElementById('safeVal').textContent = data.summary.safe_count;
            document.getElementById('missingVal').textContent = data.summary.missing_count;
            document.getElementById('notInClassVal').textContent = data.summary.not_in_class_count;
            App.toast('Student statuses refreshed', 'success');
        }
    } catch (e) {
        App.toast('Failed to refresh', 'error');
    }
}

setInterval(refreshStudentList, 10000);
</script>
