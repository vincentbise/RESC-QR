<?php $csrf = generateCSRFToken(); ?>

<!-- Breadcrumb -->
<nav class="cr-breadcrumb" id="crBreadcrumb" aria-label="Classroom navigation"></nav>

<!-- ================= VIEW 1: COURSES ================= -->
<section id="viewCourses" class="cr-view card" hidden>
    <div class="cr-toolbar">
        <div class="cr-toolbar-title">
            <h3><i class="fas fa-graduation-cap"></i> Courses</h3>
            <span class="badge badge-primary" id="courseCount"></span>
        </div>
        <div class="cr-toolbar-actions">
            <div class="search-bar cr-search">
                <i class="fas fa-search"></i>
                <input type="text" id="courseSearch" placeholder="Search course or major...">
            </div>
            <button type="button" class="btn btn-primary btn-sm cr-add-toggle" data-panel="addCoursePanel">
                <i class="fas fa-plus"></i> <span>Add Course</span>
            </button>
        </div>
    </div>

    <div class="cr-add-panel" id="addCoursePanel" hidden>
        <form id="addCourseForm" class="cr-inline-form">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div class="form-group cr-fg-code">
                <label for="course_code">Course Code *</label>
                <input type="text" class="form-control" id="course_code" name="course_code"
                       placeholder="e.g. BSIT" maxlength="30" required autocomplete="off">
            </div>
            <div class="form-group cr-grow">
                <label for="course_major">Course Major</label>
                <input type="text" class="form-control" id="course_major" name="course_major"
                       placeholder="e.g. Information Security" maxlength="150" autocomplete="off">
            </div>
            <div class="cr-form-actions">
                <button type="button" class="btn btn-secondary cr-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Course</button>
            </div>
        </form>
    </div>

    <div class="cr-body">
        <div class="cr-tile-grid" id="courseGrid"></div>
    </div>
</section>

<!-- ================= VIEW 2: SECTIONS ================= -->
<section id="viewSections" class="cr-view card" hidden>
    <div class="cr-toolbar">
        <div class="cr-toolbar-title">
            <h3><i class="fas fa-layer-group"></i> <span id="sectionsTitle">Sections</span></h3>
            <span class="badge badge-primary" id="sectionCount"></span>
        </div>
        <div class="cr-toolbar-actions">
            <button type="button" class="btn btn-primary btn-sm cr-add-toggle" data-panel="addSectionPanel">
                <i class="fas fa-plus"></i> <span>Add Section</span>
            </button>
        </div>
    </div>

    <div class="cr-add-panel" id="addSectionPanel" hidden>
        <form id="addClassForm" class="cr-inline-form">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="course_id" id="classCourseId">
            <div class="form-group cr-fg-year">
                <label for="classYearLevel">Year Level *</label>
                <select class="form-control" id="classYearLevel" name="year_level" required>
                    <option value="">Select Year</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>
            <div class="form-group cr-grow">
                <label for="section_name">Section Name *</label>
                <input type="text" class="form-control" id="section_name" name="section_name"
                       placeholder="e.g. BSIT 1A" maxlength="100" required autocomplete="off">
            </div>
            <div class="cr-form-actions">
                <button type="button" class="btn btn-secondary cr-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Section</button>
            </div>
        </form>
    </div>

    <div class="cr-body" id="sectionGroups"></div>
</section>

<!-- ================= VIEW 3: CLASSROOM STUDENTS ================= -->
<section id="viewStudents" class="cr-view card" hidden>
    <div class="cr-section-head">
        <div class="cr-section-id">
            <div class="cr-summary-title" id="sumSection"></div>
            <div class="cr-summary-sub" id="sumCourse"></div>
        </div>
        <div class="cr-chips">
            <span class="cr-chip"><i class="fas fa-user-graduate"></i> <strong id="sumCount">0</strong>&nbsp;students</span>
            <span class="cr-chip cr-chip-mayor" id="sumMayorChip"><i class="fas fa-user-tie"></i> <span id="sumMayor">Unassigned</span></span>
        </div>
    </div>

    <div class="cr-toolbar cr-toolbar-sub">
        <div class="cr-toolbar-title">
            <h3><i class="fas fa-users"></i> Classroom Students</h3>
        </div>
        <div class="cr-toolbar-actions">
            <div class="search-bar cr-search">
                <i class="fas fa-search"></i>
                <input type="text" id="studentSearch" placeholder="Search student...">
            </div>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="cr-students">
            <thead>
                <tr>
                    <th class="cr-col-num">#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th class="cr-actions">Action</th>
                </tr>
            </thead>
            <tbody id="studentTableBody"></tbody>
        </table>
    </div>
</section>

<style>
/* ---------- Breadcrumb ---------- */
.cr-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px 8px;
    margin-bottom: 14px;
    font-size: 14px;
    color: var(--text-muted);
    min-width: 0;
}
.cr-breadcrumb a {
    color: var(--text-secondary);
    text-decoration: none;
    font-weight: 600;
}
.cr-breadcrumb a:hover { color: var(--accent-primary); }
.cr-breadcrumb .cr-current { color: var(--text-primary); font-weight: 700; }
.cr-breadcrumb .fa-chevron-right { font-size: 10px; opacity: 0.7; }
.cr-breadcrumb .cr-crumb {
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ---------- Toolbar (title + actions) ---------- */
.cr-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border-color);
    background: var(--bg-secondary);
}
.cr-toolbar-title {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
.cr-toolbar-title h3 {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cr-toolbar-title h3 i { color: var(--accent-primary); margin-right: 4px; }
.cr-toolbar-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.cr-search { width: 240px; }
.cr-add-toggle { white-space: nowrap; }
.cr-add-toggle.is-open { background: var(--bg-input); border-color: var(--border-color); color: var(--text-primary); }

/* ---------- Add panel ---------- */
.cr-add-panel {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
    background: var(--bg-primary);
}
.cr-inline-form {
    display: flex;
    flex-wrap: wrap;
    gap: 12px 14px;
    align-items: flex-end;
}
.cr-inline-form .form-group { margin-bottom: 0; }
.cr-fg-code { flex: 0 1 180px; }
.cr-fg-year { flex: 0 1 180px; }
.cr-grow { flex: 1 1 240px; }
.cr-form-actions { display: flex; gap: 8px; }

/* ---------- Body / tiles ---------- */
.cr-body { padding: 20px; }

.cr-tile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 12px;
}
.cr-tile {
    position: relative;
    display: block;
    min-width: 0;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 14px 16px;
    color: var(--text-primary);
    cursor: pointer;
    transition: var(--transition);
}
.cr-tile:hover,
.cr-tile:focus-visible {
    border-color: var(--accent-primary);
    background: var(--bg-card-hover);
    outline: none;
}
.cr-tile-code {
    font-size: 18px;
    font-weight: 800;
    letter-spacing: 0.3px;
    padding-right: 28px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cr-tile-sub {
    font-size: 13px;
    color: var(--text-secondary);
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cr-tile-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 4px 12px;
    margin-top: 10px;
    font-size: 12px;
    color: var(--text-muted);
}
.cr-tile-meta > span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
.cr-tile-meta i { margin-right: 4px; }
.cr-tile-mayor { color: var(--accent-success); }
.cr-tile-del {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 30px;
    height: 30px;
    border-radius: var(--radius-sm);
    border: none;
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    opacity: 0.7;
}
.cr-tile-del:hover { color: var(--accent-danger); background: rgba(239, 68, 68, 0.12); opacity: 1; }

.cr-year-group + .cr-year-group { margin-top: 20px; }
.cr-year-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-muted);
    margin-bottom: 8px;
}

.cr-empty {
    text-align: center;
    color: var(--text-muted);
    padding: 32px 12px;
}
.cr-empty i { font-size: 26px; display: block; margin-bottom: 10px; opacity: 0.6; }

/* ---------- Section header (students view) ---------- */
.cr-section-head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px 20px;
    padding: 18px 20px;
    border-bottom: 1px solid var(--border-color);
}
.cr-section-id { min-width: 0; }
.cr-summary-title { font-size: 22px; font-weight: 800; line-height: 1.2; }
.cr-summary-sub { font-size: 13px; color: var(--text-muted); margin-top: 2px; }
.cr-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.cr-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 13px;
    background: var(--bg-input);
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    max-width: 100%;
}
.cr-chip strong { color: var(--text-primary); }
.cr-chip-mayor { color: var(--accent-success); border-color: rgba(16, 185, 129, 0.35); background: rgba(16, 185, 129, 0.08); font-weight: 600; }
.cr-chip-mayor.is-empty { color: var(--text-muted); border-color: var(--border-color); background: var(--bg-input); font-weight: 500; }
.cr-toolbar-sub { background: transparent; }

/* ---------- Students table ---------- */
.cr-students .cr-col-num { width: 48px; }
.cr-students tr.cr-is-mayor td { background: rgba(16, 185, 129, 0.06); }
.cr-actions { text-align: right; white-space: nowrap; }
.cr-student-name { font-weight: 600; }
.cr-btn-assign i { color: var(--accent-primary); }
.cr-btn-assign:hover { background: var(--accent-primary); border-color: var(--accent-primary); color: #fff; }
.cr-btn-assign:hover i { color: #fff; }

/* =============================================
   Tablet & phone
   ============================================= */
@media (max-width: 768px) {
    .cr-toolbar {
        flex-wrap: nowrap;
        padding: 12px 14px;
    }
    .cr-toolbar:has(.cr-search) { flex-wrap: wrap; }
    .cr-toolbar-actions { flex: 0 0 auto; }
    .cr-toolbar:has(.cr-search) .cr-toolbar-actions { flex: 1 1 100%; flex-wrap: nowrap; }
    .cr-search { flex: 1 1 auto; width: auto; min-width: 0 !important; }

    .cr-add-panel { padding: 14px; }
    .cr-inline-form .form-group,
    .cr-fg-code, .cr-fg-year, .cr-grow { flex: 1 1 100%; }
    .cr-form-actions { flex: 1 1 100%; }
    .cr-form-actions .btn { flex: 1; justify-content: center; }

    .cr-body { padding: 14px; }
    .cr-section-head { padding: 14px; }
    .cr-summary-title { font-size: 20px; }

    /* Students: table rows become cards */
    .cr-students thead { display: none; }
    .cr-students,
    .cr-students tbody { display: block; }
    .cr-students tbody { padding: 12px; }
    .cr-students tbody tr {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 4px 10px;
        padding: 12px 14px;
        margin-bottom: 10px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
    }
    .cr-students tbody tr:last-child { margin-bottom: 0; }
    .cr-students tbody tr.cr-is-mayor { border-color: rgba(16, 185, 129, 0.45); }
    .cr-students tbody tr.cr-is-mayor td { background: transparent; }
    .cr-students tbody td {
        display: block;
        padding: 0;
        border: none;
        font-size: 13px;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .cr-students td.cr-col-num { display: none; }
    .cr-students td.cr-td-name { grid-column: 1; grid-row: 1; font-size: 14px; }
    .cr-students td.cr-td-role { grid-column: 2; grid-row: 1; text-align: right; }
    .cr-students td.cr-td-email,
    .cr-students td.cr-td-phone { grid-column: 1 / -1; color: var(--text-secondary); }
    .cr-students td.cr-td-email::before { content: "\f0e0"; font-family: "Font Awesome 6 Free"; font-weight: 900; margin-right: 8px; color: var(--text-muted); font-size: 11px; }
    .cr-students td.cr-td-phone::before { content: "\f095"; font-family: "Font Awesome 6 Free"; font-weight: 900; margin-right: 8px; color: var(--text-muted); font-size: 11px; }
    .cr-students td.cr-td-empty { display: none; }
    .cr-students td.cr-actions { grid-column: 1 / -1; margin-top: 8px; text-align: left; }
    .cr-students td.cr-actions .btn { width: 100%; justify-content: center; }
    .cr-students tbody tr.cr-empty-row { display: block; }
    .cr-students tbody tr.cr-empty-row td { white-space: normal; }
}

@media (max-width: 480px) {
    .cr-add-toggle span { display: none; }
    .cr-add-toggle { padding-left: 12px; padding-right: 12px; }

    /* Two compact section tiles per row on phones */
    #sectionGroups .cr-tile-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    #sectionGroups .cr-tile { padding: 12px; }
    #sectionGroups .cr-tile-code { font-size: 16px; }
    #sectionGroups .cr-tile-meta { flex-direction: column; gap: 2px; margin-top: 8px; }

    .cr-tile-grid { grid-template-columns: minmax(0, 1fr); }
    .cr-breadcrumb { font-size: 13px; }
}
</style>

<script>
(function () {
    const CSRF     = '<?= $csrf ?>';
    const courses  = <?= json_encode($courses) ?>;
    const classes  = <?= json_encode($classes) ?>;
    const students = <?= json_encode($students) ?>;
    const YEARS    = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

    const $ = (id) => document.getElementById(id);
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, ch => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[ch]));

    const views = { courses: $('viewCourses'), sections: $('viewSections'), students: $('viewStudents') };

    // ---------- Helpers ----------
    const findCourse  = (id) => courses.find(c => String(c.course_id) === String(id));
    const findClass   = (id) => classes.find(c => String(c.class_id) === String(id));
    const classesOf   = (courseId) => classes.filter(c => String(c.course_id) === String(courseId));
    const studentsOf  = (classId) => students.filter(s => String(s.class_id) === String(classId));
    const courseSub   = (co) => co.course_major || (co.course_name !== co.course_code ? co.course_name : '');
    const courseLabel = (co) => courseSub(co) ? co.course_code + ' — ' + courseSub(co) : co.course_code;
    const mayorOf     = (classId) => studentsOf(classId).find(s => Number(s.is_current_mayor) === 1);

    function parseHash() {
        const p = new URLSearchParams(location.hash.slice(1));
        return { course: p.get('course'), section: p.get('section') };
    }
    function go(courseId, sectionId) {
        const p = new URLSearchParams();
        if (courseId) p.set('course', courseId);
        if (sectionId) p.set('section', sectionId);
        const h = p.toString();
        if (h) { location.hash = h; } else { history.pushState('', document.title, location.pathname + location.search); onRoute(); }
    }

    async function post(url, fd) {
        try {
            const data = await App.ajax(url, { method: 'POST', body: fd });
            if (data.success) { App.toast(data.message, 'success'); }
            else { App.toast(data.message || 'Request failed.', 'error'); }
            return data;
        } catch (err) {
            App.toast(err.message || 'An error occurred.', 'error');
            return { success: false };
        }
    }

    // ---------- Add panels ----------
    function setPanel(panelId, open) {
        const panel = $(panelId);
        const toggle = document.querySelector('.cr-add-toggle[data-panel="' + panelId + '"]');
        panel.hidden = !open;
        if (toggle) {
            toggle.classList.toggle('is-open', open);
            toggle.querySelector('i').className = open ? 'fas fa-times' : 'fas fa-plus';
            const label = toggle.querySelector('span');
            if (!label.dataset.text) label.dataset.text = label.textContent;
            label.textContent = open ? 'Close' : label.dataset.text;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        if (open) {
            const first = panel.querySelector('input:not([type=hidden]), select');
            if (first) first.focus();
        } else {
            panel.querySelector('form').reset();
        }
    }
    document.querySelectorAll('.cr-add-toggle').forEach(btn => {
        btn.addEventListener('click', () => setPanel(btn.dataset.panel, $(btn.dataset.panel).hidden));
    });
    document.querySelectorAll('.cr-add-panel .cr-cancel').forEach(btn => {
        btn.addEventListener('click', () => setPanel(btn.closest('.cr-add-panel').id, false));
    });

    // ---------- Breadcrumb ----------
    function renderBreadcrumb(course, section) {
        const parts = [];
        const sep = '<i class="fas fa-chevron-right"></i>';
        $('crBreadcrumb').hidden = !course;
        if (course) {
            parts.push('<a href="#" data-nav=""><i class="fas fa-graduation-cap"></i> Courses</a>');
            if (!section) {
                parts.push(sep, '<span class="cr-current cr-crumb">' + esc(courseLabel(course)) + '</span>');
            } else {
                parts.push(sep, '<a class="cr-crumb" href="#course=' + course.course_id + '">' + esc(courseLabel(course)) + '</a>');
                parts.push(sep, '<span class="cr-current cr-crumb">' + esc(section.section_name) + '</span>');
            }
        }
        $('crBreadcrumb').innerHTML = parts.join(' ');
        const home = $('crBreadcrumb').querySelector('[data-nav=""]');
        if (home) home.addEventListener('click', (e) => { e.preventDefault(); go(); });
    }

    // ---------- View 1: Courses ----------
    function renderCourses() {
        const q = $('courseSearch').value.trim().toLowerCase();
        const list = courses.filter(co =>
            !q || (co.course_code + ' ' + co.course_major + ' ' + co.course_name).toLowerCase().includes(q)
        );
        $('courseCount').textContent = courses.length;

        if (!list.length) {
            $('courseGrid').innerHTML = '<div class="cr-empty" style="grid-column:1/-1;"><i class="fas fa-book-open"></i>' +
                (courses.length ? 'No course matches your search.' : 'No courses yet. Use “Add Course” to create one.') + '</div>';
            return;
        }

        $('courseGrid').innerHTML = list.map(co => `
            <div class="cr-tile" role="button" tabindex="0" data-course="${co.course_id}" title="${esc(courseLabel(co))}">
                <button type="button" class="cr-tile-del" data-del-course="${co.course_id}" title="Delete course" aria-label="Delete ${esc(courseLabel(co))}">
                    <i class="fas fa-trash"></i>
                </button>
                <div class="cr-tile-code">${esc(co.course_code)}</div>
                <div class="cr-tile-sub">${esc(courseSub(co)) || '<span class="text-muted">No major</span>'}</div>
                <div class="cr-tile-meta">
                    <span><i class="fas fa-layer-group"></i>${classesOf(co.course_id).length} sections</span>
                    <span><i class="fas fa-user-graduate"></i>${Number(co.student_count) || 0} students</span>
                </div>
            </div>`).join('');
    }

    $('courseGrid').addEventListener('click', async (e) => {
        const del = e.target.closest('[data-del-course]');
        if (del) {
            e.stopPropagation();
            const co = findCourse(del.dataset.delCourse);
            const ok = await App.confirm('Delete course "' + courseLabel(co) + '" and any empty sections under it?');
            if (!ok) return;
            const fd = new FormData(); fd.append('csrf_token', CSRF);
            const res = await post('/classroom/deleteCourse/' + co.course_id, fd);
            if (res.success) setTimeout(() => location.reload(), 600);
            return;
        }
        const tile = e.target.closest('[data-course]');
        if (tile) go(tile.dataset.course);
    });
    $('courseSearch').addEventListener('input', renderCourses);

    $('course_code').addEventListener('input', (e) => {
        const pos = e.target.selectionStart;
        e.target.value = e.target.value.toUpperCase();
        e.target.setSelectionRange(pos, pos);
    });

    $('addCourseForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await post('/classroom/storeCourse', new FormData(e.target));
        if (res.success) {
            if (res.course_id) location.hash = 'course=' + res.course_id;
            setTimeout(() => location.reload(), 600);
        }
    });

    // ---------- View 2: Sections ----------
    let sectionEdited = false;

    function renderSections(course) {
        const list = classesOf(course.course_id);
        $('sectionsTitle').textContent = 'Sections';
        $('classCourseId').value = course.course_id;
        $('sectionCount').textContent = list.length;

        if (!list.length) {
            $('sectionGroups').innerHTML = '<div class="cr-empty"><i class="fas fa-chalkboard"></i>No sections in this course yet. Use “Add Section” to create one.</div>';
            return;
        }

        const years = YEARS.concat([...new Set(list.map(c => c.year_level))].filter(y => !YEARS.includes(y)));
        $('sectionGroups').innerHTML = years.map(y => {
            const inYear = list.filter(c => c.year_level === y);
            if (!inYear.length) return '';
            return `<div class="cr-year-group">
                <div class="cr-year-title">${esc(y)}</div>
                <div class="cr-tile-grid">${inYear.map(cl => {
                    const mayor = mayorOf(cl.class_id);
                    const count = studentsOf(cl.class_id).length;
                    return `<div class="cr-tile" role="button" tabindex="0" data-section="${cl.class_id}">
                        <button type="button" class="cr-tile-del" data-del-section="${cl.class_id}" title="Delete section" aria-label="Delete ${esc(cl.section_name)}">
                            <i class="fas fa-trash"></i>
                        </button>
                        <div class="cr-tile-code">${esc(cl.section_name)}</div>
                        <div class="cr-tile-meta">
                            <span><i class="fas fa-user-graduate"></i>${count} students</span>
                            ${mayor
                                ? `<span class="cr-tile-mayor"><i class="fas fa-user-tie"></i>${esc(mayor.first_name + ' ' + mayor.last_name)}</span>`
                                : `<span><i class="fas fa-user-tie"></i>No mayor</span>`}
                        </div>
                    </div>`;
                }).join('')}</div>
            </div>`;
        }).join('');
    }

    $('sectionGroups').addEventListener('click', async (e) => {
        const del = e.target.closest('[data-del-section]');
        if (del) {
            e.stopPropagation();
            const cl = findClass(del.dataset.delSection);
            const ok = await App.confirm('Delete section "' + cl.section_name + '"?');
            if (!ok) return;
            const fd = new FormData(); fd.append('csrf_token', CSRF);
            const res = await post('/classroom/deleteClass/' + cl.class_id, fd);
            if (res.success) setTimeout(() => location.reload(), 600);
            return;
        }
        const tile = e.target.closest('[data-section]');
        if (tile) go(parseHash().course, tile.dataset.section);
    });

    $('section_name').addEventListener('input', () => { sectionEdited = $('section_name').value.trim() !== ''; });
    $('classYearLevel').addEventListener('change', () => {
        const course = findCourse($('classCourseId').value);
        const y = $('classYearLevel').value;
        if (!sectionEdited && course && y) $('section_name').value = course.course_code + ' ' + y.charAt(0);
    });

    $('addClassForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await post('/classroom/storeClass', new FormData(e.target));
        if (res.success) setTimeout(() => location.reload(), 600);
    });

    // Keyboard support for tiles
    document.addEventListener('keydown', (e) => {
        if ((e.key === 'Enter' || e.key === ' ') && e.target.classList && e.target.classList.contains('cr-tile')) {
            e.preventDefault();
            e.target.click();
        }
    });

    // ---------- View 3: Classroom students ----------
    function renderStudents(course, section) {
        const list  = studentsOf(section.class_id);
        const mayor = mayorOf(section.class_id);
        const q     = $('studentSearch').value.trim().toLowerCase();

        $('sumSection').textContent = section.section_name;
        $('sumCourse').textContent  = courseLabel(course) + ' · ' + section.year_level;
        $('sumCount').textContent   = list.length;
        $('sumMayor').textContent   = mayor ? 'Mayor: ' + mayor.first_name + ' ' + mayor.last_name : 'No mayor assigned';
        $('sumMayorChip').classList.toggle('is-empty', !mayor);

        const shown = list.filter(s =>
            !q || (s.first_name + ' ' + s.last_name + ' ' + (s.email || '')).toLowerCase().includes(q)
        );

        if (!shown.length) {
            $('studentTableBody').innerHTML = '<tr class="cr-empty-row"><td colspan="6" class="cr-empty">' +
                (list.length ? 'No student matches your search.' : 'No active students enrolled in this section yet.') + '</td></tr>';
            return;
        }

        const dash = '<span class="text-muted">—</span>';
        $('studentTableBody').innerHTML = shown.map((s, i) => {
            const isMayor = Number(s.is_current_mayor) === 1;
            return `<tr class="${isMayor ? 'cr-is-mayor' : ''}">
                <td class="cr-col-num text-muted">${i + 1}</td>
                <td class="cr-td-name"><span class="cr-student-name">${esc(s.last_name + ', ' + s.first_name)}</span></td>
                <td class="cr-td-email${s.email ? '' : ' cr-td-empty'}">${esc(s.email) || dash}</td>
                <td class="cr-td-phone${s.phone ? '' : ' cr-td-empty'}">${esc(s.phone) || dash}</td>
                <td class="cr-td-role">${isMayor
                    ? '<span class="badge badge-success"><i class="fas fa-user-tie"></i> Mayor</span>'
                    : '<span class="badge badge-info">Student</span>'}</td>
                <td class="cr-actions">${isMayor
                    ? `<button type="button" class="btn btn-sm btn-secondary" data-unassign="1"><i class="fas fa-user-minus"></i> Remove Mayor</button>`
                    : `<button type="button" class="btn btn-sm btn-secondary cr-btn-assign" data-assign="${s.student_id}"><i class="fas fa-user-tie"></i> Assign as Mayor</button>`}
                </td>
            </tr>`;
        }).join('');
    }

    $('studentSearch').addEventListener('input', render);

    $('studentTableBody').addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-assign],[data-unassign]');
        if (!btn) return;
        const { section } = parseHash();
        const cl = findClass(section);
        const current = mayorOf(cl.class_id);
        const studentId = btn.dataset.assign || '';
        const target = studentId ? students.find(s => String(s.student_id) === studentId) : null;

        const msg = target
            ? 'Assign ' + target.first_name + ' ' + target.last_name + ' as Class Mayor of ' + cl.section_name + '?' +
              (current ? ' ' + current.first_name + ' ' + current.last_name + ' will be removed as mayor.' : '')
            : 'Remove ' + current.first_name + ' ' + current.last_name + ' as Class Mayor of ' + cl.section_name + '?';
        if (!(await App.confirm(msg))) return;

        btn.disabled = true;
        const fd = new FormData();
        fd.append('csrf_token', CSRF);
        fd.append('class_id', cl.class_id);
        fd.append('student_id', studentId);
        const res = await post('/classroom/assignMayor', fd);
        if (res.success) {
            studentsOf(cl.class_id).forEach(s => { s.is_current_mayor = String(s.student_id) === studentId ? 1 : 0; });
            render();
        } else {
            btn.disabled = false;
        }
    });

    // ---------- Router ----------
    function show(name) {
        Object.entries(views).forEach(([k, el]) => { el.hidden = k !== name; });
    }

    function render() {
        const h = parseHash();
        const course  = h.course ? findCourse(h.course) : null;
        let section   = h.section ? findClass(h.section) : null;
        if (section && (!course || String(section.course_id) !== String(course.course_id))) section = null;

        renderBreadcrumb(course, section);
        if (course && section) { show('students'); renderStudents(course, section); }
        else if (course)       { show('sections'); renderSections(course); }
        else                   { show('courses');  renderCourses(); }
    }

    // Reset transient UI (search, open add forms) whenever the route changes
    function onRoute() {
        $('studentSearch').value = '';
        sectionEdited = false;
        setPanel('addCoursePanel', false);
        setPanel('addSectionPanel', false);
        render();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    window.addEventListener('hashchange', onRoute);
    window.addEventListener('popstate', render);

    render();
    // Open the add form straight away when there's nothing to click into yet
    const h0 = parseHash();
    if (!h0.course && !courses.length) setPanel('addCoursePanel', true);
    else if (h0.course && !h0.section && findCourse(h0.course) && !classesOf(h0.course).length) setPanel('addSectionPanel', true);
})();
</script>
