<div class="page-header">
    <div>
        <h1>Student Status</h1>
        <p><?= $activeEvent ? e($activeEvent['event_type']) . ' — ' . date('M d, Y g:i A', strtotime($activeEvent['event_datetime'])) : '' ?></p>
    </div>
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
        <div class="stat-label">Absent</div>
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
        <div class="status-dropdown" id="statusDropdown">
            <button class="status-dropdown-toggle btn btn-sm" id="statusDropdownBtn" type="button">
                <i class="fas fa-users"></i>
                <span>All Students</span>
                <i class="fas fa-chevron-down dropdown-arrow"></i>
            </button>
            <div class="status-dropdown-menu" id="statusDropdownMenu">
                <button class="status-dropdown-item active" data-filter="all" data-icon="fa-users" data-color="var(--accent-green, #16a34a)">
                    <i class="fas fa-users"></i> All Students
                </button>
                <button class="status-dropdown-item" data-filter="Safe" data-icon="fa-shield-alt" data-color="var(--accent-green, #16a34a)">
                    <i class="fas fa-shield-alt"></i> Safe
                </button>
                <div class="status-dropdown-divider"></div>
                <button class="status-dropdown-item" data-filter="Not Yet Scanned" data-icon="fa-clock" data-color="var(--accent-orange, #d97706)">
                    <i class="fas fa-clock"></i> Not Yet Scanned
                </button>
                <button class="status-dropdown-item" data-filter="Missing" data-icon="fa-exclamation-triangle" data-color="var(--accent-red, #dc2626)">
                    <i class="fas fa-exclamation-triangle"></i> Missing
                </button>
                <button class="status-dropdown-item" data-filter="Absent" data-icon="fa-user-slash" data-color="var(--text-muted, #888)">
                    <i class="fas fa-user-slash"></i> Absent
                </button>
            </div>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Student</th><th>Section</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody id="statusBody">
                    <?php if (empty($students)): ?>
                        <tr><td colspan="4" class="text-center text-muted" style="padding:60px;">No student statuses available</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                        <?php
                            $status   = $s['status'];
                            $badgeCls = $status === 'Safe'
                                ? 'success'
                                : ($status === 'Not Yet Scanned'
                                    ? 'danger'
                                    : ($status === 'Absent' ? 'warning' : 'info'));
                        ?>
                        <tr class="status-row"
                            data-status="<?= e($status) ?>"
                            data-name="<?= e(strtolower($s['last_name'] . ' ' . $s['first_name'])) ?>"
                            data-student-id="<?= (int)$s['student_id'] ?>">
                            <td>
                                <div class="d-flex align-center gap-1">
                                    <div class="avatar avatar-sm" style="background:hsl(<?= $s['student_id'] * 37 % 360 ?>,60%,50%);overflow:hidden;">
                                        <?php if (!empty($s['profile_image'])): ?>
                                            <img src="<?= e(publicUrl('img/profiles/' . $s['profile_image'])) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                                        <?php else: ?>
                                            <?= strtoupper(substr($s['first_name'],0,1) . substr($s['last_name'],0,1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <strong><?= e($s['last_name'] . ', ' . $s['first_name']) ?></strong>
                                </div>
                            </td>
                            <td><span class="badge badge-info"><?= e($s['section_name']) ?></span></td>
                            <td><span class="badge badge-<?= $badgeCls ?>"><?= e($status) ?></span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <?php if ($activeEvent): ?>
                                        <?php if (!empty($s['phone'])): ?>
                                            <a href="tel:<?= e($s['phone']) ?>" class="dial-btn" title="Call <?= e($s['first_name']) ?>">
                                                <i class="fas fa-phone-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if ($status !== 'Absent'): ?>
                                            <button class="btn btn-sm btn-danger btn-icon mark-absent"
                                                    data-student-id="<?= (int)$s['student_id'] ?>"
                                                    title="Mark Absent">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* ── Status filter dropdown ──────────────────────────────── */
.status-dropdown {
    position: relative;
    display: inline-block;
}

.status-dropdown-toggle {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid var(--border-color, #333);
    background: var(--bg-card, #1a1a2e);
    color: var(--text-primary, #eee);
    transition: border-color 0.2s, box-shadow 0.2s;
    white-space: nowrap;
    user-select: none;
}

.status-dropdown-toggle:hover {
    border-color: var(--border-hover, #555);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.status-dropdown-toggle .dropdown-arrow {
    font-size: 10px;
    opacity: 0.6;
    transition: transform 0.2s;
}

.status-dropdown.open .dropdown-arrow {
    transform: rotate(180deg);
}

.status-dropdown-toggle .fa-users,
.status-dropdown-toggle .fa-shield-alt,
.status-dropdown-toggle .fa-exclamation-triangle,
.status-dropdown-toggle .fa-clock,
.status-dropdown-toggle .fa-user-slash {
    font-size: 12px;
}

.status-dropdown-menu {
    display: none;
    position: fixed;
    min-width: 200px;
    padding: 6px 0;
    border-radius: 10px;
    border: 1px solid var(--border-color, #333);
    background: var(--bg-card, #1a1a2e);
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    z-index: 9999;
    animation: dropdownSlide 0.15s ease-out;
}

@keyframes dropdownSlide {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.status-dropdown.open .status-dropdown-menu {
    display: block;
}

.status-dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 9px 16px;
    border: none;
    background: transparent;
    color: var(--text-secondary, #aaa);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.12s, color 0.12s;
    text-align: left;
    white-space: nowrap;
}

.status-dropdown-item:hover {
    background: var(--bg-card-hover, rgba(255,255,255,0.06));
    color: var(--text-primary, #eee);
}

.status-dropdown-item.active {
    color: var(--text-primary, #eee);
    font-weight: 700;
    background: var(--bg-card-hover, rgba(255,255,255,0.06));
}

.status-dropdown-item.active::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    font-size: 11px;
    margin-left: auto;
    opacity: 0.7;
}

.status-dropdown-item i {
    width: 16px;
    text-align: center;
    font-size: 12px;
}

.status-dropdown-divider {
    height: 1px;
    margin: 4px 12px;
    background: var(--border-color, #333);
}
/* ── Inline dial button ─────────────────────────────────── */
.dial-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(16, 185, 129, 0.12);
    color: var(--accent-green, #10b981);
    font-size: 12px;
    text-decoration: none;
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    flex-shrink: 0;
}
.dial-btn:hover {
    background: rgba(16, 185, 129, 0.25);
    transform: scale(1.12);
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.25);
    color: #34d399;
}
.dial-btn:active {
    transform: scale(0.95);
}
/* ── Mobile responsive ─────────────────────────────────── */
@media (max-width: 768px) {
    .card-header .d-flex.align-center.gap-2 {
        flex-direction: column;
        align-items: stretch;
        width: 100%;
    }
    .card-header .d-flex.align-center.gap-2 h3 {
        margin-bottom: 4px;
    }
    .status-dropdown {
        width: 100%;
    }
    .status-dropdown-toggle {
        width: 100%;
        justify-content: center;
    }
    .table-wrapper {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table-wrapper table {
        min-width: 0;
    }
}
</style>

<script>
(function () {
    let hasActiveEvent = <?= $activeEvent ? 'true' : 'false' ?>;

    // No alias map needed — Missing and Not Yet Scanned are distinct statuses
    const STATUS_MAP = {};

    /* ── Dropdown logic ─────────────────────────────────────── */
    const dropdown     = document.getElementById('statusDropdown');
    const toggleBtn    = document.getElementById('statusDropdownBtn');
    const menu         = document.getElementById('statusDropdownMenu');
    let currentFilter  = 'all';

    function positionMenu() {
        const rect = toggleBtn.getBoundingClientRect();
        menu.style.top  = (rect.bottom + 6) + 'px';
        menu.style.left = '';
        menu.style.right = '';
        // Align right edge of menu with right edge of button
        const menuWidth = menu.offsetWidth || 200;
        let left = rect.right - menuWidth;
        if (left < 8) left = 8; // keep on screen
        menu.style.left = left + 'px';
    }

    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const willOpen = !dropdown.classList.contains('open');
        dropdown.classList.toggle('open');
        if (willOpen) positionMenu();
    });

    // Close on outside click
    document.addEventListener('click', () => dropdown.classList.remove('open'));
    menu.addEventListener('click', (e) => e.stopPropagation());

    menu.querySelectorAll('.status-dropdown-item').forEach(item => {
        item.addEventListener('click', () => {
            // Update active state
            menu.querySelectorAll('.status-dropdown-item').forEach(i => i.classList.remove('active'));
            item.classList.add('active');

            // Update toggle button text & icon
            const icon  = item.dataset.icon;
            const color = item.dataset.color;
            const label = item.textContent.trim();
            currentFilter = item.dataset.filter;

            toggleBtn.innerHTML = `<i class="fas ${icon}" style="color:${color}"></i> <span>${label}</span> <i class="fas fa-chevron-down dropdown-arrow"></i>`;

            dropdown.classList.remove('open');
            applyFilters();
        });
    });

    /* ── Search & filter ────────────────────────────────────── */
    document.getElementById('statusSearch').addEventListener('input', App.debounce(applyFilters, 150));

    function applyFilters() {
        const query = document.getElementById('statusSearch').value.trim().toLowerCase();
        const resolvedStatus = STATUS_MAP[currentFilter] || currentFilter;

        document.querySelectorAll('#statusBody .status-row').forEach(row => {
            const nameMatch   = !query || row.dataset.name.includes(query);
            const statusMatch = resolvedStatus === 'all' || row.dataset.status === resolvedStatus;
            row.style.display = (nameMatch && statusMatch) ? '' : 'none';
        });
    }

    applyFilters();

    /* ── Stat cards ─────────────────────────────────────────── */
    function updateStatCards(summary) {
        const safeEl     = document.getElementById('safeVal');
        const missingEl  = document.getElementById('missingVal');
        const notInClsEl = document.getElementById('notInClassVal');
        if (safeEl)     safeEl.textContent     = summary.safe_count     ?? 0;
        if (missingEl)  missingEl.textContent  = summary.missing_count  ?? 0;
        if (notInClsEl) notInClsEl.textContent = summary.not_in_class_count ?? 0;
    }

    /* ── Dynamic row rendering ──────────────────────────────── */
    function renderRows(students) {
        const tbody = document.getElementById('statusBody');
        if (!students || !students.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted" style="padding:60px;">No student statuses available</td></tr>';
            return;
        }

        const badgeClass = s => s === 'Safe'
            ? 'success'
            : (s === 'Not Yet Scanned'
                ? 'danger'
                : (s === 'Absent' ? 'warning' : 'info'));

        tbody.innerHTML = students.map(s => {
            const initials = (s.last_name[0] + s.first_name[0]).toUpperCase();
            const hue      = (s.student_id * 37) % 360;
            const avatarInner = s.profile_image
                ? `<img src="${BASE_URL}/public/img/profiles/${encodeURIComponent(s.profile_image)}" alt="" style="width:100%;height:100%;object-fit:cover;">`
                : initials;
            const phone = s.phone ? App.escapeHtml(s.phone) : '';

            let actionHtml = '';
            if (hasActiveEvent) {
                // During event: show phone call button
                if (phone) {
                    actionHtml = `<a href="tel:${phone}" class="dial-btn" title="Call"><i class="fas fa-phone-alt"></i></a>`;
                }
            } else {
                // No event: show mark absent button
                if (s.status !== 'Absent') {
                    actionHtml = `<button class="btn btn-sm btn-danger btn-icon mark-absent" data-student-id="${s.student_id}" title="Mark Absent"><i class="fas fa-user-slash"></i></button>`;
                }
            }

            return `<tr class="status-row"
                        data-status="${App.escapeHtml(s.status)}"
                        data-name="${App.escapeHtml((s.last_name + ' ' + s.first_name).toLowerCase())}"
                        data-student-id="${s.student_id}">
                <td>
                    <div class="d-flex align-center gap-1">
                        <div class="avatar avatar-sm" style="background:hsl(${hue},60%,50%);overflow:hidden;">${avatarInner}</div>
                        <strong>${App.escapeHtml(s.last_name + ', ' + s.first_name)}</strong>
                    </div>
                </td>
                <td><span class="badge badge-info">${App.escapeHtml(s.section_name)}</span></td>
                <td><span class="badge badge-${badgeClass(s.status)}">${App.escapeHtml(s.status)}</span></td>
                <td><div class="d-flex gap-1">${actionHtml}</div></td>
            </tr>`;
        }).join('');

        applyFilters();
    }

    /* ── Auto-refresh ───────────────────────────────────────── */
    window.refreshStudentList = async function () {
        try {
            const data = await App.get('/scan/students');
            if (data.success) {
                if (typeof data.active_event !== 'undefined') {
                    hasActiveEvent = !!data.active_event;
                }
                updateStatCards(data.summary);
                renderRows(data.students);
            }
        } catch (e) {
            App.toast('Failed to refresh', 'error');
        }
    };

    /* ── Mark absent ────────────────────────────────────────── */
    document.getElementById('statusBody').addEventListener('click', async (e) => {
        const btn = e.target.closest('.mark-absent');
        if (!btn) return;

        const studentId = parseInt(btn.dataset.studentId);
        if (!studentId) return;

        const ok = await App.confirm('Mark this student as absent?');
        if (!ok) return;

        btn.disabled = true;
        try {
            const data = await App.post('/scan/absent', { student_id: studentId });
            if (data.success) {
                App.toast(data.message, 'success');
                refreshStudentList();
            } else {
                App.toast(data.message || 'Failed to update status.', 'error');
            }
        } catch (err) {
            App.toast(err.message || 'Failed to update status.', 'error');
        } finally {
            btn.disabled = false;
        }
    });

    setInterval(window.refreshStudentList, 10000);
})();
</script>