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
        <div class="d-flex align-center gap-2">
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
        <span class="badge badge-primary" id="totalBadge"><?= $totalStudents ?> students</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Section</th>
                        <th>QR Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody">
                    <?php if (empty($students)): ?>
                        <tr id="emptyRow"><td colspan="6" class="text-center text-muted" style="padding:60px;">No students found</td></tr>
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
                            <td><?= e($s['email'] ?? '—') ?></td>
                            <td><?= e($s['course']) ?></td>
                            <td><span class="badge badge-info"><?= e($s['section_name']) ?></span></td>
                            <td><code style="font-size:11px;color:var(--text-muted)"><?= e($s['qr_code_value'] ?? 'None') ?></code></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= baseUrl('student/edit/' . $s['student_id']) ?>" class="btn btn-secondary btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button class="btn btn-danger btn-sm" title="Delete" onclick="deleteStudent(<?= $s['student_id'] ?>, '<?= e($s['first_name'] . ' ' . $s['last_name']) ?>')">
                                        <i class="fas fa-trash"></i> Delete
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

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="card-footer pagination-wrapper" id="paginationWrapper">
        <div class="pagination-info">
            Showing <?= (($page - 1) * $perPage) + 1 ?>–<?= min($page * $perPage, $totalStudents) ?> of <?= $totalStudents ?> students
        </div>
        <div class="pagination" id="paginationControls">
            <?php if ($page > 1): ?>
                <a href="<?= baseUrl('student?page=' . ($page - 1) . ($search ? '&search=' . urlencode($search) : '') . ($classId ? '&class_id=' . $classId : '')) ?>" class="pagination-btn" data-page="<?= $page - 1 ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php else: ?>
                <span class="pagination-btn disabled"><i class="fas fa-chevron-left"></i></span>
            <?php endif; ?>

            <?php
            $startPage = max(1, $page - 2);
            $endPage   = min($totalPages, $page + 2);
            if ($startPage > 1): ?>
                <a href="<?= baseUrl('student?page=1' . ($search ? '&search=' . urlencode($search) : '') . ($classId ? '&class_id=' . $classId : '')) ?>" class="pagination-btn" data-page="1">1</a>
                <?php if ($startPage > 2): ?><span class="pagination-ellipsis">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="<?= baseUrl('student?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($classId ? '&class_id=' . $classId : '')) ?>"
                   class="pagination-btn <?= $i === $page ? 'active' : '' ?>" data-page="<?= $i ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($endPage < $totalPages): ?>
                <?php if ($endPage < $totalPages - 1): ?><span class="pagination-ellipsis">…</span><?php endif; ?>
                <a href="<?= baseUrl('student?page=' . $totalPages . ($search ? '&search=' . urlencode($search) : '') . ($classId ? '&class_id=' . $classId : '')) ?>" class="pagination-btn" data-page="<?= $totalPages ?>"><?= $totalPages ?></a>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
                <a href="<?= baseUrl('student?page=' . ($page + 1) . ($search ? '&search=' . urlencode($search) : '') . ($classId ? '&class_id=' . $classId : '')) ?>" class="pagination-btn" data-page="<?= $page + 1 ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="pagination-btn disabled"><i class="fas fa-chevron-right"></i></span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

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
                BASE_URL + '/student?page=' + page + '&search=' + encodeURIComponent(search) + '&class_id=' + encodeURIComponent(classId),
                { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN } }
            );
            const data = await res.json();
            if (data.success) {
                currentPage = data.page;
                renderStudents(data.students);
                document.getElementById('totalBadge').textContent = data.total + ' students';
                renderPagination(data.page, data.total_pages, data.total, data.per_page);
            }
        } catch (e) {
            console.error('Student search failed:', e);
        }
    }

    const debouncedFetch = App.debounce(function() { fetchStudents(1); }, 300);

    searchInput.addEventListener('input', debouncedFetch);
    classFilter.addEventListener('change', function() { fetchStudents(1); });

    function renderStudents(students) {
        const tbody = document.getElementById('studentTableBody');
        if (!students || !students.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted" style="padding:60px;">No students found</td></tr>';
            return;
        }
        tbody.innerHTML = students.map(s => {
            const initials = (s.first_name[0] + s.last_name[0]).toUpperCase();
            const hue      = (s.student_id * 37) % 360;
            return `
            <tr id="student-row-${s.student_id}">
                <td>
                    <div class="d-flex align-center gap-1">
                        <div class="avatar avatar-sm" style="background:hsl(${hue},60%,50%)">${initials}</div>
                        <div><strong>${App.escapeHtml(s.first_name + ' ' + s.last_name)}</strong></div>
                    </div>
                </td>
                <td>${App.escapeHtml(s.email || '—')}</td>
                <td>${App.escapeHtml(s.course)}</td>
                <td><span class="badge badge-info">${App.escapeHtml(s.section_name)}</span></td>
                <td><code style="font-size:11px;color:var(--text-muted)">${App.escapeHtml(s.qr_code_value || 'None')}</code></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="${BASE_URL}/student/edit/${s.student_id}" class="btn btn-secondary btn-sm" title="Edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button class="btn btn-danger btn-sm" title="Delete"
                            onclick="deleteStudent(${s.student_id}, '${App.escapeHtml(s.first_name + ' ' + s.last_name)}')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    function renderPagination(page, totalPages, total, perPage) {
        let wrapper = document.getElementById('paginationWrapper');
        if (totalPages <= 1) {
            if (wrapper) wrapper.style.display = 'none';
            return;
        }

        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.id = 'paginationWrapper';
            wrapper.className = 'card-footer pagination-wrapper';
            document.querySelector('.card .card-body').parentElement.appendChild(wrapper);
        }
        wrapper.style.display = '';

        const from = ((page - 1) * perPage) + 1;
        const to   = Math.min(page * perPage, total);

        let startPage = Math.max(1, page - 2);
        let endPage   = Math.min(totalPages, page + 2);

        let html = `<div class="pagination-info">Showing ${from}–${to} of ${total} students</div>`;
        html += '<div class="pagination">';

        // Prev
        if (page > 1) {
            html += `<a href="#" class="pagination-btn" data-page="${page-1}"><i class="fas fa-chevron-left"></i></a>`;
        } else {
            html += '<span class="pagination-btn disabled"><i class="fas fa-chevron-left"></i></span>';
        }

        // First page
        if (startPage > 1) {
            html += `<a href="#" class="pagination-btn" data-page="1">1</a>`;
            if (startPage > 2) html += '<span class="pagination-ellipsis">…</span>';
        }

        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            html += `<a href="#" class="pagination-btn ${i === page ? 'active' : ''}" data-page="${i}">${i}</a>`;
        }

        // Last page
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += '<span class="pagination-ellipsis">…</span>';
            html += `<a href="#" class="pagination-btn" data-page="${totalPages}">${totalPages}</a>`;
        }

        // Next
        if (page < totalPages) {
            html += `<a href="#" class="pagination-btn" data-page="${page+1}"><i class="fas fa-chevron-right"></i></a>`;
        } else {
            html += '<span class="pagination-btn disabled"><i class="fas fa-chevron-right"></i></span>';
        }

        html += '</div>';
        wrapper.innerHTML = html;

        // Bind click events
        wrapper.querySelectorAll('.pagination-btn[data-page]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                fetchStudents(parseInt(this.dataset.page));
            });
        });
    }

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
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-20px)';
                    setTimeout(() => row.remove(), 300);
                }
                App.toast(data.message, 'success');
                // Refresh current page to update pagination
                setTimeout(() => fetchStudents(currentPage), 400);
            }
        } catch (e) {
            App.toast('Failed to delete student.', 'error');
        }
    };
})();
</script>