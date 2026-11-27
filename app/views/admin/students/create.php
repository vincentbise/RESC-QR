<div class="page-header">
    <div>
        <h1>Register Student</h1>
        <p>Add a new student to the system</p>
    </div>
    <a href="<?= baseUrl('student') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="card" style="max-width:700px;">
    <div class="card-body">
        <form id="createStudentForm">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="student@usep.edu.ph">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" placeholder="09XXXXXXXXX">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="course">Course *</label>
                    <select class="form-control" id="course" name="course" required>
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $co): ?>
                            <option value="<?= e($co['course_code']) ?>" data-course-id="<?= (int)$co['course_id'] ?>">
                                <?= e($co['course_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="year_level">Year Level *</label>
                    <select class="form-control" id="year_level" name="year_level" required>
                        <option value="">Select Year</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="class_id">Assign to Class *</label>
                <select class="form-control" id="class_id" name="class_id" required disabled>
                    <option value="">Select Course and Year Level first</option>
                </select>
            </div>

            <div class="d-flex gap-1" style="margin-top:28px;">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-plus"></i> <span class="btn-text">Register Student</span>
                    <span class="spinner" style="display:none;"><i class="fas fa-circle-notch fa-spin"></i></span>
                </button>
                <a href="<?= baseUrl('student') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const allClasses = <?= json_encode($classes) ?>;

    const courseEl = document.getElementById('course');
    const yearEl   = document.getElementById('year_level');
    const classEl  = document.getElementById('class_id');

    function updateClassOptions() {
        const selectedOpt = courseEl.options[courseEl.selectedIndex];
        const courseId = selectedOpt ? (selectedOpt.dataset.courseId || '') : '';
        const program  = selectedOpt ? selectedOpt.textContent.trim() : '';
        const year     = yearEl.value;

        classEl.innerHTML = '';

        if (!courseEl.value || !year) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'Select Course and Year Level first';
            classEl.appendChild(opt);
            classEl.disabled = true;
            return;
        }

        const matched = allClasses.filter(c =>
            (courseId ? String(c.course_id) === String(courseId) : c.program === program)
            && c.year_level === year
        );

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = matched.length ? 'Select Class/Section' : 'No classes available for this selection';
        classEl.appendChild(placeholder);

        matched.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.class_id;
            opt.textContent = c.section_name + ' — ' + c.program;
            classEl.appendChild(opt);
        });

        classEl.disabled = matched.length === 0;
    }

    courseEl.addEventListener('change', updateClassOptions);
    yearEl.addEventListener('change', updateClassOptions);
    updateClassOptions();
})();

document.getElementById('createStudentForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.querySelector('.btn-text').style.display = 'none';
    btn.querySelector('.spinner').style.display = 'inline';
    btn.disabled = true;

    const formData = new FormData(e.target);

    try {
        const data = await App.ajax('/student/store', { method: 'POST', body: formData });
        if (data.success) {
            App.toast(data.message, 'success');
            setTimeout(() => window.location.href = BASE_URL + '/student', 800);
        } else {
            App.toast(data.message || 'Validation failed.', 'error');
            btn.querySelector('.btn-text').style.display = 'inline';
            btn.querySelector('.spinner').style.display = 'none';
            btn.disabled = false;
        }
    } catch (err) {
        App.toast(err.message || 'An error occurred.', 'error');
        btn.querySelector('.btn-text').style.display = 'inline';
        btn.querySelector('.spinner').style.display = 'none';
        btn.disabled = false;
    }
});
</script>