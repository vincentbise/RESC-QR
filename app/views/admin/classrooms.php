<div class="classroom-grid">

    <!-- Assign Class Mayor -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-user-tie"></i> Assign Class Mayor</h3>
        </div>
        <div class="card-body">
            <form id="assignMayorForm">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <div class="form-group">
                    <label for="mayorCourse">Course *</label>
                    <select class="form-control" id="mayorCourse" required>
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $co): ?>
                            <option value="<?= (int)$co['course_id'] ?>">
                                <?= e($co['course_code'] . ' — ' . $co['course_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="mayorClass">Section *</label>
                    <select class="form-control" id="mayorClass" name="class_id" required disabled>
                        <option value="">Select Course first</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="mayorStudent">Student in Section</label>
                    <select class="form-control" id="mayorStudent" name="student_id" disabled>
                        <option value="">Select Section first</option>
                    </select>
                    <small class="text-muted" id="mayorCurrentHint" style="display:block;margin-top:6px;"></small>
                </div>

                <button type="submit" class="btn btn-primary" id="assignMayorBtn" disabled>
                    <i class="fas fa-check"></i> Save Mayor Assignment
                </button>
            </form>
        </div>
    </div>

    <!-- Add Course & Add Section -->
    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-book"></i> Add Course</h3>
            </div>
            <div class="card-body">
                <form id="addCourseForm">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="course_code">Course Code *</label>
                            <input type="text" class="form-control" id="course_code" name="course_code" placeholder="e.g. BSCE" required>
                        </div>
                        <div class="form-group">
                            <label for="course_name">Course Name *</label>
                            <input type="text" class="form-control" id="course_name" name="course_name" placeholder="e.g. BS Civil Engineering" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Course
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chalkboard"></i> Add Class Section</h3>
            </div>
            <div class="card-body">
                <form id="addClassForm">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="classCourseId">Course *</label>
                            <select class="form-control" id="classCourseId" name="course_id" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $co): ?>
                                    <option value="<?= (int)$co['course_id'] ?>" data-code="<?= e($co['course_code']) ?>">
                                        <?= e($co['course_code'] . ' — ' . $co['course_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="classYearLevel">Year Level *</label>
                            <select class="form-control" id="classYearLevel" name="year_level" required>
                                <option value="">Select Year</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="section_name">Section Name *</label>
                        <input type="text" class="form-control" id="section_name" name="section_name" placeholder="e.g. BSIT 1C" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Section
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- Courses Table -->
<div class="card" style="margin-top:20px;">
    <div class="card-header">
        <h3><i class="fas fa-graduation-cap"></i> Courses</h3>
        <span class="badge badge-primary"><?= count($courses) ?> courses</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Course Name</th>
                        <th>Sections</th>
                        <th>Students</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courses)): ?>
                        <tr><td colspan="5" class="text-center text-muted" style="padding:40px;">No courses added yet</td></tr>
                    <?php else: ?>
                        <?php foreach ($courses as $co): ?>
                        <tr>
                            <td><span class="badge badge-info"><?= e($co['course_code']) ?></span></td>
                            <td><strong><?= e($co['course_name']) ?></strong></td>
                            <td><?= (int)$co['class_count'] ?></td>
                            <td><?= (int)$co['student_count'] ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger btn-icon delete-course-btn"
                                        data-id="<?= (int)$co['course_id'] ?>"
                                        data-name="<?= e($co['course_name']) ?>"
                                        title="Delete Course">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Sections & Mayors Table -->
<div class="card" style="margin-top:20px;">
    <div class="card-header" style="flex-wrap:wrap;gap:10px;">
        <h3><i class="fas fa-chalkboard-teacher"></i> Classes &amp; Class Mayors</h3>
        <div class="d-flex gap-1 align-center" style="flex-wrap:wrap;">
            <select class="form-control" id="filterCourseTable" style="width:auto;min-width:180px;">
                <option value="">All Courses</option>
                <?php foreach ($courses as $co): ?>
                    <option value="<?= (int)$co['course_id'] ?>"><?= e($co['course_code'] . ' — ' . $co['course_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="search-bar" style="min-width:180px;">
                <i class="fas fa-search"></i>
                <input type="text" id="filterSectionSearch" placeholder="Search section...">
            </div>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Course</th>
                        <th>Year Level</th>
                        <th>Students</th>
                        <th>Class Mayor</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="classTableBody">
                    <?php if (empty($classes)): ?>
                        <tr><td colspan="6" class="text-center text-muted" style="padding:40px;">No class sections found</td></tr>
                    <?php else: ?>
                        <?php foreach ($classes as $cl): ?>
                        <tr class="class-row"
                            data-course-id="<?= (int)($cl['course_id'] ?? 0) ?>"
                            data-section="<?= e(strtolower($cl['section_name'])) ?>">
                            <td><strong><?= e($cl['section_name']) ?></strong></td>
                            <td><?= e($cl['program']) ?></td>
                            <td><?= e($cl['year_level']) ?></td>
                            <td><span class="badge badge-primary"><?= (int)$cl['student_count'] ?></span></td>
                            <td>
                                <?php if (!empty($cl['mayor_names'])): ?>
                                    <span class="badge badge-success"><i class="fas fa-user-tie"></i> <?= e($cl['mayor_names']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-secondary pick-section-btn"
                                            data-course-id="<?= (int)($cl['course_id'] ?? 0) ?>"
                                            data-class-id="<?= (int)$cl['class_id'] ?>"
                                            title="Assign Mayor">
                                        <i class="fas fa-user-tie"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-icon delete-class-btn"
                                            data-id="<?= (int)$cl['class_id'] ?>"
                                            data-name="<?= e($cl['section_name']) ?>"
                                            title="Delete Section">
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
.classroom-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    align-items: start;
}
@media (max-width: 900px) {
    .classroom-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
(function () {
    const CSRF = '<?= generateCSRFToken() ?>';
    const allClasses  = <?= json_encode($classes) ?>;
    const allStudents = <?= json_encode($students) ?>;

    // ---- Cascading Mayor Assignment Dropdowns ----
    const mayorCourseEl  = document.getElementById('mayorCourse');
    const mayorClassEl   = document.getElementById('mayorClass');
    const mayorStudentEl = document.getElementById('mayorStudent');
    const mayorHintEl    = document.getElementById('mayorCurrentHint');
    const assignBtn      = document.getElementById('assignMayorBtn');

    function populateSections(selectedClassId) {
        const courseId = mayorCourseEl.value;
        mayorClassEl.innerHTML = '';
        mayorStudentEl.innerHTML = '<option value="">Select Section first</option>';
        mayorStudentEl.disabled = true;
        mayorHintEl.textContent = '';
        assignBtn.disabled = true;

        if (!courseId) {
            mayorClassEl.innerHTML = '<option value="">Select Course first</option>';
            mayorClassEl.disabled = true;
            return;
        }

        const matchedClasses = allClasses.filter(c => String(c.course_id) === String(courseId));
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = matchedClasses.length ? 'Select Section' : 'No sections in this course';
        mayorClassEl.appendChild(placeholder);

        matchedClasses.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.class_id;
            opt.textContent = c.section_name + ' (' + c.year_level + ')';
            if (selectedClassId && String(c.class_id) === String(selectedClassId)) {
                opt.selected = true;
            }
            mayorClassEl.appendChild(opt);
        });

        mayorClassEl.disabled = matchedClasses.length === 0;
        if (selectedClassId) {
            populateStudents();
        }
    }

    function populateStudents() {
        const classId = mayorClassEl.value;
        mayorStudentEl.innerHTML = '';
        mayorHintEl.textContent = '';

        if (!classId) {
            mayorStudentEl.innerHTML = '<option value="">Select Section first</option>';
            mayorStudentEl.disabled = true;
            assignBtn.disabled = true;
            return;
        }

        const sectionStudents = allStudents.filter(s => String(s.class_id) === String(classId));

        const noneOpt = document.createElement('option');
        noneOpt.value = '';
        noneOpt.textContent = '-- None (Unassign Mayor) --';
        mayorStudentEl.appendChild(noneOpt);

        let currentMayorName = '';
        sectionStudents.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.student_id;
            const fullName = s.last_name + ', ' + s.first_name;
            opt.textContent = fullName + (Number(s.is_current_mayor) === 1 ? ' (Current Mayor)' : '');
            if (Number(s.is_current_mayor) === 1) {
                opt.selected = true;
                currentMayorName = s.first_name + ' ' + s.last_name;
            }
            mayorStudentEl.appendChild(opt);
        });

        mayorStudentEl.disabled = false;
        assignBtn.disabled = false;

        if (!sectionStudents.length) {
            mayorHintEl.textContent = 'No active students enrolled in this section yet.';
        } else if (currentMayorName) {
            mayorHintEl.textContent = 'Current Mayor: ' + currentMayorName;
        } else {
            mayorHintEl.textContent = 'No mayor currently assigned to this section.';
        }
    }

    mayorCourseEl.addEventListener('change', () => populateSections());
    mayorClassEl.addEventListener('change', populateStudents);

    // Quick-select from table row button
    document.querySelectorAll('.pick-section-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const cid = btn.dataset.courseId;
            const secId = btn.dataset.classId;
            if (cid) {
                mayorCourseEl.value = cid;
                populateSections(secId);
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    // ---- Auto-prefix section name when adding a class ----
    const classCourseEl = document.getElementById('classCourseId');
    const classYearEl   = document.getElementById('classYearLevel');
    const sectionInput  = document.getElementById('section_name');
    let sectionManuallyEdited = false;

    sectionInput.addEventListener('input', () => {
        sectionManuallyEdited = sectionInput.value.trim() !== '';
    });

    function suggestSectionPrefix() {
        if (sectionManuallyEdited) return;
        const selectedOpt = classCourseEl.options[classCourseEl.selectedIndex];
        const code = selectedOpt ? (selectedOpt.dataset.code || '') : '';
        const yearNum = classYearEl.value ? classYearEl.value.charAt(0) : '';
        if (code && yearNum) {
            sectionInput.value = code + ' ' + yearNum;
        }
    }
    classCourseEl.addEventListener('change', suggestSectionPrefix);
    classYearEl.addEventListener('change', suggestSectionPrefix);

    // ---- Form submissions ----
    document.getElementById('assignMayorForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        assignBtn.disabled = true;
        try {
            const data = await App.ajax('/classroom/assignMayor', {
                method: 'POST',
                body: new FormData(e.target)
            });
            if (data.success) {
                App.toast(data.message, 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                App.toast(data.message || 'Failed to assign mayor.', 'error');
                assignBtn.disabled = false;
            }
        } catch (err) {
            App.toast(err.message || 'An error occurred.', 'error');
            assignBtn.disabled = false;
        }
    });

    document.getElementById('addCourseForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const data = await App.ajax('/classroom/storeCourse', {
                method: 'POST',
                body: new FormData(e.target)
            });
            if (data.success) {
                App.toast(data.message, 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                App.toast(data.message || 'Failed to add course.', 'error');
            }
        } catch (err) {
            App.toast(err.message || 'An error occurred.', 'error');
        }
    });

    document.getElementById('addClassForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const data = await App.ajax('/classroom/storeClass', {
                method: 'POST',
                body: new FormData(e.target)
            });
            if (data.success) {
                App.toast(data.message, 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                App.toast(data.message || 'Failed to add section.', 'error');
            }
        } catch (err) {
            App.toast(err.message || 'An error occurred.', 'error');
        }
    });

    // ---- Delete actions ----
    document.querySelectorAll('.delete-course-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const ok = await App.confirm('Delete course "' + btn.dataset.name + '" and any empty sections under it?');
            if (!ok) return;
            const fd = new FormData();
            fd.append('csrf_token', CSRF);
            try {
                const data = await App.ajax('/classroom/deleteCourse/' + btn.dataset.id, { method: 'POST', body: fd });
                if (data.success) {
                    App.toast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    App.toast(data.message || 'Failed to delete course.', 'error');
                }
            } catch (err) {
                App.toast(err.message || 'An error occurred.', 'error');
            }
        });
    });

    document.querySelectorAll('.delete-class-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const ok = await App.confirm('Delete section "' + btn.dataset.name + '"?');
            if (!ok) return;
            const fd = new FormData();
            fd.append('csrf_token', CSRF);
            try {
                const data = await App.ajax('/classroom/deleteClass/' + btn.dataset.id, { method: 'POST', body: fd });
                if (data.success) {
                    App.toast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    App.toast(data.message || 'Failed to delete section.', 'error');
                }
            } catch (err) {
                App.toast(err.message || 'An error occurred.', 'error');
            }
        });
    });

    // ---- Filter classes table ----
    const filterCourseEl  = document.getElementById('filterCourseTable');
    const filterSearchEl  = document.getElementById('filterSectionSearch');

    function filterClassTable() {
        const cid = filterCourseEl.value;
        const q   = filterSearchEl.value.trim().toLowerCase();
        document.querySelectorAll('#classTableBody .class-row').forEach(row => {
            const matchCourse = !cid || row.dataset.courseId === cid;
            const matchSearch = !q || row.dataset.section.includes(q);
            row.style.display = (matchCourse && matchSearch) ? '' : 'none';
        });
    }

    filterCourseEl.addEventListener('change', filterClassTable);
    filterSearchEl.addEventListener('input', filterClassTable);
})();
</script>
