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
        <div class="stat-label">Not Yet Scanned</div>
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
            <button class="btn btn-sm btn-secondary status-filter" data-filter="Not Yet Scanned">Not Yet Scanned</button>
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
                        <?php
                            $status   = $s['status'];
                            $badgeCls = $status === 'Safe' ? 'success' : ($status === 'Not Yet Scanned' ? 'danger' : 'warning');
                        ?>
                        <tr class="status-row"
                            data-status="<?= e($status) ?>"
                            data-name="<?= e(strtolower($s['first_name'] . ' ' . $s['last_name'])) ?>">
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
                            <td><span class="badge badge-<?= $badgeCls ?>"><?= e($status) ?></span></td>
                            <td>
                                <?php if ($status === 'Not Yet Scanned' && !empty($s['phone'])): ?>
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
(function () {
    // ── Filter buttons ────────────────────────────────────────────
    function setActiveFilter(btn) {
        document.querySelectorAll('.status-filter').forEach(b => {
            b.classList.remove('active', 'btn-success');
            b.classList.add('btn-secondary');
        });
        btn.classList.remove('btn-secondary');
        btn.classList.add('active', 'btn-success');
    }

    document.querySelectorAll('.status-filter').forEach(btn => {
        btn.addEventListener('click', () => {
            setActiveFilter(btn);
            applyFilters();
        });
    });

    // ── Live search ───────────────────────────────────────────────
    document.getElementById('statusSearch').addEventListener('input', App.debounce(applyFilters, 150));

    function applyFilters() {
        const query     = document.getElementById('statusSearch').value.trim().toLowerCase();
        const activeBtn = document.querySelector('.status-filter.active');
        const filterVal = activeBtn ? activeBtn.dataset.filter : 'all';

        document.querySelectorAll('#statusBody .status-row').forEach(row => {
            const nameMatch   = !query || row.dataset.name.includes(query);
            const statusMatch = filterVal === 'all' || row.dataset.status === filterVal;
            row.style.display = (nameMatch && statusMatch) ? '' : 'none';
        });
    }

    // Run filters immediately on page load so the active "All" button
    // state is applied and rows are correctly visible from the start
    applyFilters();

    // ── Update stat cards safely (they only exist when event is active) ──
    function updateStatCards(summary) {
        const safeEl     = document.getElementById('safeVal');
        const missingEl  = document.getElementById('missingVal');
        const notInClsEl = document.getElementById('notInClassVal');
        if (safeEl)     safeEl.textContent     = summary.safe_count     ?? 0;
        if (missingEl)  missingEl.textContent  = summary.missing_count  ?? 0;
        if (notInClsEl) notInClsEl.textContent = summary.not_in_class_count ?? 0;
    }

    // ── Render rows (called after refresh) ───────────────────────
    function renderRows(students) {
        const tbody = document.getElementById('statusBody');
        if (!students || !students.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted" style="padding:60px;">No student statuses available</td></tr>';
            return;
        }

        const badgeClass = s => s === 'Safe' ? 'success' : (s === 'Not Yet Scanned' ? 'danger' : 'warning');

        tbody.innerHTML = students.map(s => {
            const initials = (s.first_name[0] + s.last_name[0]).toUpperCase();
            const hue      = (s.student_id * 37) % 360;
            const phone    = s.phone ? App.escapeHtml(s.phone) : '';
            const callBtn  = (s.status === 'Not Yet Scanned' && phone)
                ? `<a href="tel:${phone}" class="btn btn-sm btn-warning btn-icon" title="Call"><i class="fas fa-phone"></i></a>`
                : '';
            return `<tr class="status-row"
                        data-status="${App.escapeHtml(s.status)}"
                        data-name="${App.escapeHtml((s.first_name + ' ' + s.last_name).toLowerCase())}">
                <td>
                    <div class="d-flex align-center gap-1">
                        <div class="avatar avatar-sm" style="background:hsl(${hue},60%,50%)">${initials}</div>
                        <strong>${App.escapeHtml(s.first_name + ' ' + s.last_name)}</strong>
                    </div>
                </td>
                <td><span class="badge badge-info">${App.escapeHtml(s.section_name)}</span></td>
                <td>${phone || '—'}</td>
                <td><span class="badge badge-${badgeClass(s.status)}">${App.escapeHtml(s.status)}</span></td>
                <td>${callBtn}</td>
            </tr>`;
        }).join('');

        // Re-apply current search/filter after re-render
        applyFilters();
    }

    // ── Live refresh ──────────────────────────────────────────────
    window.refreshStudentList = async function () {
        try {
            const data = await App.get('/scan/students');
            if (data.success) {
                updateStatCards(data.summary);
                renderRows(data.students);
            }
        } catch (e) {
            App.toast('Failed to refresh', 'error');
        }
    };

    setInterval(window.refreshStudentList, 10000);
})();
</script>