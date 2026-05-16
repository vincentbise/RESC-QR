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
        <span class="badge badge-primary" id="totalBadge"><?= count($students) ?> students</span>
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
                                    <a href="<?= baseUrl('student/edit/' . $s['student_id']) ?>" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm btn-icon" title="Delete" onclick="deleteStudent(<?= $s['student_id'] ?>, '<?= e($s['first_name'] . ' ' . $s['last_name']) ?>')">
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

<script>
const searchInput = document.getElementById('searchInput');
const classFilter = document.getElementById('classFilter');

const searchStudents = App.debounce(async () => {
    const search = searchInput.value;
    const classId = classFilter.value;
    try {
        const data = await App.get(`/student?search=${encodeURIComponent(search)}&class_id=${classId}`);
        if (data.success) {
            renderStudents(data.students);
            document.getElementById('totalBadge').textContent = data.total + ' students';
        }
    } catch (e) {
        console.error('Search failed:', e);
    }
}, 300);

searchInput.addEventListener('input', searchStudents);
classFilter.addEventListener('change', searchStudents);

function renderStudents(students) {
    const tbody = document.getElementById('studentTableBody');
    if (!students.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted" style="padding:60px;">No students found</td></tr>';
        return;
    }
    tbody.innerHTML = students.map(s => `
        <tr id="student-row-${s.student_id}">
            <td>
                <div class="d-flex align-center gap-1">
                    <div class="avatar avatar-sm" style="background:hsl(${s.student_id * 37 % 360},60%,50%)">
                        ${(s.first_name[0] + s.last_name[0]).toUpperCase()}
                    </div>
                    <div><strong>${App.escapeHtml(s.first_name + ' ' + s.last_name)}</strong></div>
                </div>
            </td>
            <td>${App.escapeHtml(s.email || '—')}</td>
            <td>${App.escapeHtml(s.course)}</td>
            <td><span class="badge badge-info">${App.escapeHtml(s.section_name)}</span></td>
            <td><code style="font-size:11px;color:var(--text-muted)">${App.escapeHtml(s.qr_code_value || 'None')}</code></td>
            <td>
                <div class="d-flex gap-1">
                    <a href="${BASE_URL}/student/edit/${s.student_id}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button class="btn btn-danger btn-sm btn-icon" title="Delete" onclick="deleteStudent(${s.student_id}, '${App.escapeHtml(s.first_name + ' ' + s.last_name)}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

App.escapeHtml = (str) => {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
};

async function deleteStudent(id, name) {
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
            const badge = document.getElementById('totalBadge');
            const current = parseInt(badge.textContent);
            badge.textContent = (current - 1) + ' students';
        }
    } catch (e) {
        App.toast('Failed to delete student.', 'error');
    }
}
</script>
