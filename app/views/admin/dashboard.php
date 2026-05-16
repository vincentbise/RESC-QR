<div class="page-header">
    <div>
        <h1>Emergency Dashboard</h1>
        <p>Real-time student accountability overview</p>
    </div>
    <div class="d-flex gap-1">
        <button class="btn btn-secondary btn-sm" onclick="refreshDashboard()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>
</div>

<div class="stats-grid" id="statsGrid">
    <div class="stat-card primary">
        <div class="stat-header">
            <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
        </div>
        <div class="stat-value" id="statTotal"><?= e($totalStudents) ?></div>
        <div class="stat-label">Total Students</div>
    </div>
    <div class="stat-card success">
        <div class="stat-header">
            <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
        </div>
        <div class="stat-value" id="statSafe"><?= e($safeCount) ?></div>
        <div class="stat-label">Safe</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-header">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
        <div class="stat-value" id="statMissing"><?= e($missingCount) ?></div>
        <div class="stat-label">Missing</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-header">
            <div class="stat-icon"><i class="fas fa-bolt"></i></div>
        </div>
        <div class="stat-value" id="statEvents"><?= e($activeEvents) ?></div>
        <div class="stat-label">Active Events</div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-history"></i> Recent Scans</h3>
            <span class="badge badge-info" id="scanCountBadge"><?= count($recentScans) ?> latest</span>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Section</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="recentScansBody">
                        <?php if (empty($recentScans)): ?>
                            <tr><td colspan="4" class="text-center text-muted" style="padding:40px;">No scan activity yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentScans as $scan): ?>
                            <tr>
                                <td><strong><?= e($scan['first_name'] . ' ' . $scan['last_name']) ?></strong></td>
                                <td><?= e($scan['section_name']) ?></td>
                                <td><?= date('M d, g:i A', strtotime($scan['scan_time'])) ?></td>
                                <td><span class="badge badge-<?= $scan['scan_result'] === 'Valid' ? 'success' : 'danger' ?>"><?= e($scan['scan_result']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-building"></i> Classes Overview</h3>
            <span class="badge badge-primary"><?= count($classes) ?> classes</span>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Program</th>
                            <th>Students</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><strong><?= e($class['section_name']) ?></strong></td>
                            <td><?= e($class['program']) ?></td>
                            <td><span class="badge badge-primary"><?= e($class['student_count']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let dashboardInterval;

async function refreshDashboard() {
    try {
        const data = await App.get('/dashboard/stats');
        if (data.success) {
            document.getElementById('statTotal').textContent = data.totalStudents;
            document.getElementById('statSafe').textContent = data.safeCount;
            document.getElementById('statMissing').textContent = data.missingCount;
            document.getElementById('statEvents').textContent = data.activeEvents;
        }
    } catch (e) {
        console.error('Dashboard refresh failed:', e);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    dashboardInterval = setInterval(refreshDashboard, 5000);
});
</script>
