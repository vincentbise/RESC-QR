<div class="page-header">
    <div>
        <h1>Edit Student</h1>
        <p>Update student information</p>
    </div>
    <a href="<?= baseUrl('student') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="card" style="max-width:700px;">
    <div class="card-body">
        <form id="editStudentForm">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?= e($student['first_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?= e($student['last_name']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= e($student['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= e($student['phone'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="course">Course *</label>
                    <select class="form-control" id="course" name="course" required>
                        <?php foreach (['BSIT','BSCS','BSIS','BSEd','BSBA'] as $c): ?>
                            <option value="<?= $c ?>" <?= $student['course'] === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="year_level">Year Level *</label>
                    <select class="form-control" id="year_level" name="year_level" required>
                        <?php foreach (['1st Year','2nd Year','3rd Year','4th Year'] as $y): ?>
                            <option value="<?= $y ?>" <?= $student['year_level'] === $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="class_id">Class / Section *</label>
                <select class="form-control" id="class_id" name="class_id" required>
                    <?php foreach ($classes as $cls): ?>
                        <option value="<?= e($cls['class_id']) ?>" <?= $student['class_id'] == $cls['class_id'] ? 'selected' : '' ?>>
                            <?= e($cls['section_name'] . ' — ' . $cls['program']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>QR Code Value</label>
                <input type="text" class="form-control" value="<?= e($student['qr_code_value'] ?? 'Not assigned') ?>" disabled style="opacity:0.6;">
            </div>

            <div class="d-flex gap-1" id="editActionBar"
                 style="margin-top:28px;overflow:hidden;transition:max-height 0.3s ease,opacity 0.3s ease;max-height:0;opacity:0;display:none;">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> <span class="btn-text">Save Changes</span>
                    <span class="spinner" style="display:none;"><i class="fas fa-circle-notch fa-spin"></i></span>
                </button>
                <button type="button" class="btn btn-secondary" id="cancelEditBtn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const form       = document.getElementById('editStudentForm');
    const actionBar  = document.getElementById('editActionBar');
    const cancelBtn  = document.getElementById('cancelEditBtn');

    // Snapshot original values on page load
    const originalValues = {};
    form.querySelectorAll('input:not([type="hidden"]):not([disabled]), select').forEach(el => {
        originalValues[el.name] = el.value;
    });

    function checkDirty() {
        let dirty = false;
        form.querySelectorAll('input:not([type="hidden"]):not([disabled]), select').forEach(el => {
            if (originalValues[el.name] !== undefined && el.value !== originalValues[el.name]) dirty = true;
        });

        if (dirty) {
            actionBar.style.display = 'flex';
            requestAnimationFrame(() => {
                actionBar.style.maxHeight = '80px';
                actionBar.style.opacity   = '1';
            });
        } else {
            actionBar.style.maxHeight = '0';
            actionBar.style.opacity   = '0';
            setTimeout(() => { actionBar.style.display = 'none'; }, 300);
        }
    }

    // Watch all editable fields
    form.querySelectorAll('input:not([type="hidden"]):not([disabled]), select').forEach(el => {
        el.addEventListener('input',  checkDirty);
        el.addEventListener('change', checkDirty);
    });

    // Cancel — restore original values and hide action bar
    cancelBtn.addEventListener('click', () => {
        form.querySelectorAll('input:not([type="hidden"]):not([disabled]), select').forEach(el => {
            if (originalValues[el.name] !== undefined) el.value = originalValues[el.name];
        });
        checkDirty();
        App.toast('Changes discarded.', 'info');
    });

    // Submit
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        btn.querySelector('.btn-text').style.display = 'none';
        btn.querySelector('.spinner').style.display = 'inline';
        btn.disabled = true;

        const formData = new FormData(form);

        try {
            const data = await App.ajax('/student/update/<?= $student['student_id'] ?>', { method: 'POST', body: formData });
            if (data.success) {
                App.toast(data.message, 'success');
                setTimeout(() => window.location.href = BASE_URL + '/student', 800);
            } else {
                App.toast(data.message || 'Update failed.', 'error');
            }
        } catch (err) {
            App.toast(err.message || 'An error occurred.', 'error');
        }

        btn.querySelector('.btn-text').style.display = 'inline';
        btn.querySelector('.spinner').style.display = 'none';
        btn.disabled = false;
    });
})();
</script>