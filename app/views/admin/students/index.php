<div class="page-header">
    <div>
        <h1>Students</h1>
        <p>Manage student records and QR code assignments</p>
    </div>
    <a href="<?= baseUrl('student/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Student
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div class="student-header-left">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search students..." value="<?= e($search) ?>">
            </div>
            <select class="form-control" id="classFilter" style="width:200px;padding:10px 12px;">
                <option value="">All Classes</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= e($c['class_id']) ?>" <?= $classId == $c['class_id'] ? 'selected' : '' ?>><?= e($c['section_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="student-header-right">
            <span class="badge badge-primary" id="totalBadge"><?= $totalStudents ?> students</span>
            <div class="pagination-inline" id="paginationTop">
                <?php if ($totalPages > 1): ?>
                <?php if ($page > 1): ?>
                    <a href="<?= baseUrl('student?page=' . ($page - 1) . ($search ? '&search=' . urlencode($search) : '') . ($classId ? '&class_id=' . $classId : '')) ?>" class="page-btn" data-page="<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
                <?php else: ?>
                    <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
                <?php endif; ?>

                <?php
                $startPage = max(1, $page - 2);
                $endPage   = min($totalPages, $page + 2);
                if ($startPage > 1): ?>
                    <a href="<?= baseUrl('student?page=1' . ($search ? '&search=' . urlencode($search) : '') . ($classId ? '&class_id=' . $classId : '')) ?>" class="page-btn" data-page="1">1</a>
                    <?php if ($startPage > 2): ?><span class="page-btn ellipsis">…</span><?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="<?= baseUrl('student?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($classId ? '&class_id=' . $classId : '')) ?>"
                       class="page-btn <?= $i === $page ? 'active' : '' ?>" data-page="<?= $i ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?><span class="page-btn ellipsis">…</span><?php endif; ?>
                    <a href="<?= baseUrl('student?page=' . $totalPages . ($search ? '&search=' . urlencode($search) : '') . ($classId ? '&class_id=' . $classId : '')) ?>" class="page-btn" data-page="<?= $totalPages ?>"><?= $totalPages ?></a>
                <?php endif; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?= baseUrl('student?page=' . ($page + 1) . ($search ? '&search=' . urlencode($search) : '') . ($classId ? '&class_id=' . $classId : '')) ?>" class="page-btn" data-page="<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
                <?php else: ?>
                    <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th class="col-email">Email</th>
                        <th class="col-course">Course</th>
                        <th>Section</th>
                        <th class="col-qr">QR Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody">
                    <?php if (empty($students)): ?>
                        <tr id="emptyRow">
                            <td colspan="6" class="text-center text-muted" style="padding:60px;">No students found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                        <tr id="student-row-<?= $s['student_id'] ?>">
                            <td>
                                <div class="d-flex align-center gap-1">
                                    <div class="avatar avatar-sm" style="background:hsl(<?= $s['student_id'] * 37 % 360 ?>,60%,50%)">
                                        <?= strtoupper(substr($s['first_name'],0,1) . substr($s['last_name'],0,1)) ?>
                                    </div>
                                    <div>
                                        <strong><?= e($s['first_name'] . ' ' . $s['last_name']) ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td class="col-email"><?= e($s['email'] ?? '—') ?></td>
                            <td class="col-course"><?= e($s['course']) ?></td>
                            <td><span class="badge badge-info"><?= e($s['section_name']) ?></span></td>
                            <td class="col-qr"><code style="font-size:11px;color:var(--text-muted)"><?= e($s['qr_code_value'] ?? 'None') ?></code></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= baseUrl('student/edit/' . $s['student_id']) ?>" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button class="btn btn-danger btn-sm btn-icon" title="Delete"
                                        onclick="deleteStudent(<?= $s['student_id'] ?>, '<?= e($s['first_name'] . ' ' . $s['last_name']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
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
/* ── Card header layout ──────────────────────────────────── */
.card-header {
    flex-wrap: wrap;
    gap: 10px;
    row-gap: 10px;
}

.student-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
    flex-wrap: wrap;
}

.student-header-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

/* ── Inline pagination ───────────────────────────────────── */
.pagination-inline {
    display: flex;
    align-items: center;
    gap: 2px;
}

.page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    padding: 0 6px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid transparent;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
    user-select: none;
    color: var(--text-secondary);
    text-decoration: none;
    background: transparent;
    line-height: 1;
}

.page-btn:not(.disabled):not(.active):not(.ellipsis):hover {
    background: var(--bg-card-hover);
    border-color: var(--border-hover);
    color: var(--text-primary);
}

.page-btn.active {
    background: var(--accent-red);
    color: #fff;
    font-weight: 700;
    cursor: default;
    border-color: var(--accent-red);
}

.page-btn.disabled {
    opacity: 0.3;
    cursor: not-allowed;
    pointer-events: none;
}

.page-btn.ellipsis {
    cursor: default;
    border: none;
    pointer-events: none;
    color: var(--text-muted);
}

/* ── Responsive column hiding ────────────────────────────── */
@media (max-width: 900px) {
    .col-email { display: none; }
}

@media (max-width: 700px) {
    .col-qr { display: none; }
    .student-header-left .search-bar { min-width: 120px; }
    #classFilter { width: 130px !important; }
}

@media (max-width: 520px) {
    .col-course { display: none; }
    .student-header-right .badge { display: none; }
}
</style>

<script>
(function () {
    const searchInput = document.getElementById('searchInput');
    const classFilter = document.getElementById('classFilter');
    let currentPage = <?= $page ?>;

    async function fetchStudents(page) {
        page = page || 1;
        const search  = searchInput.value.trim();
        const classId = classFilter.value;

        try {
            const res = await fetch(
                BASE_URL + '/student?page=' + page
                    + '&search='   + encodeURIComponent(search)
                    + '&class_id=' + encodeURIComponent(classId),
                { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN } }
            );
            const data = await res.json();
            if (data.success) {
                currentPage = data.page;
                renderStudents(data.students);
                document.getElementById('totalBadge').textContent = data.total + ' students';
                renderPagination(data.page, data.total_pages);
            }
        } catch (e) {
            console.error('Student fetch failed:', e);
        }
    }

    const debouncedFetch = App.debounce(() => fetchStudents(1), 300);
    searchInput.addEventListener('input', debouncedFetch);
    classFilter.addEventListener('change', () => fetchStudents(1));

    function renderStudents(students) {
        const tbody = document.getElementById('studentTableBody');
        if (!students || !students.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted" style="padding:60px;">No students found</td></tr>';
            return;
        }
        tbody.innerHTML = students.map(s => {
            const initials = (s.first_name[0] + s.last_name[0]).toUpperCase();
            const hue      = (s.student_id * 37) % 360;
            return `<tr id="student-row-${s.student_id}">
                <td>
                    <div class="d-flex align-center gap-1">
                        <div class="avatar avatar-sm" style="background:hsl(${hue},60%,50%)">${initials}</div>
                        <div><strong>${App.escapeHtml(s.first_name + ' ' + s.last_name)}</strong></div>
                    </div>
                </td>
                <td class="col-email">${App.escapeHtml(s.email || '—')}</td>
                <td class="col-course">${App.escapeHtml(s.course)}</td>
                <td><span class="badge badge-info">${App.escapeHtml(s.section_name)}</span></td>
                <td class="col-qr"><code style="font-size:11px;color:var(--text-muted)">${App.escapeHtml(s.qr_code_value || 'None')}</code></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="${BASE_URL}/student/edit/${s.student_id}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button class="btn btn-danger btn-sm btn-icon" title="Delete"
                            onclick="deleteStudent(${s.student_id}, '${App.escapeHtml(s.first_name + ' ' + s.last_name)}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    function buildPageBtn(page, label, isActive, isDisabled, isEllipsis) {
        if (isEllipsis) return `<span class="page-btn ellipsis">${label}</span>`;
        if (isDisabled) return `<span class="page-btn disabled">${label}</span>`;
        return `<a href="#" class="page-btn${isActive ? ' active' : ''}" data-page="${page}">${label}</a>`;
    }

    function renderPagination(page, totalPages) {
        const container = document.getElementById('paginationTop');
        if (!container) return;

        if (totalPages <= 1) { container.innerHTML = ''; return; }

        const startPage = Math.max(1, page - 2);
        const endPage   = Math.min(totalPages, page + 2);
        let html = '';

        html += buildPageBtn(page - 1, '<i class="fas fa-chevron-left"></i>', false, page <= 1, false);

        if (startPage > 1) {
            html += buildPageBtn(1, '1', false, false, false);
            if (startPage > 2) html += buildPageBtn(null, '…', false, false, true);
        }

        for (let i = startPage; i <= endPage; i++) {
            html += buildPageBtn(i, i, i === page, false, false);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += buildPageBtn(null, '…', false, false, true);
            html += buildPageBtn(totalPages, totalPages, false, false, false);
        }

        html += buildPageBtn(page + 1, '<i class="fas fa-chevron-right"></i>', false, page >= totalPages, false);

        container.innerHTML = html;
        container.querySelectorAll('.page-btn[data-page]').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                fetchStudents(parseInt(btn.dataset.page));
            });
        });
    }

    // Bind server-rendered pagination buttons
    document.querySelectorAll('#paginationTop .page-btn[data-page]').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            fetchStudents(parseInt(btn.dataset.page));
        });
    });

    window.deleteStudent = async function(id, name) {
        const confirmed = await App.confirm(`Are you sure you want to remove <strong>${name}</strong>?`);
        if (!confirmed) return;

        try {
            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN);
            const data = await App.ajax(`/student/delete/${id}`, { method: 'POST', body: formData });
            if (data.success) {
                const row = document.getElementById(`student-row-${id}`);
                if (row) {
                    row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    row.style.opacity    = '0';
                    row.style.transform  = 'translateX(-16px)';
                    setTimeout(() => row.remove(), 300);
                }
                App.toast(data.message, 'success');
                setTimeout(() => fetchStudents(currentPage), 400);
            }
        } catch (e) {
            App.toast('Failed to delete student.', 'error');
        }
    };
})();
</script>