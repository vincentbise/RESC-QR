<div class="page-header">
    <div>
        <h1>My Profile</h1>
        <p>View and update your personal and academic information</p>
    </div>
</div>

<div class="card" style="max-width:700px;">
    <div class="card-body">

        <div style="text-align:center;margin-bottom:28px;">
            <div class="avatar" id="profileAvatar" style="width:80px;height:80px;font-size:28px;margin:0 auto 12px;background:linear-gradient(135deg,var(--accent-primary),var(--accent-secondary));">
                <?= strtoupper(substr($student['first_name'] ?? '',0,1) . substr($student['last_name'] ?? '',0,1)) ?>
            </div>
            <div style="font-size:20px;font-weight:800;" id="profileFullName"><?= e(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></div>
            <div class="text-muted" id="profileCourseYear"><?= e($student['course'] ?? '') ?> — <?= e($student['year_level'] ?? '') ?></div>
        </div>

        <div style="display:flex;gap:16px;margin-bottom:24px;">
            <div style="flex:1;background:var(--bg-secondary);border-radius:8px;padding:12px 16px;">
                <div class="text-muted" style="font-size:12px;margin-bottom:4px;">Student ID</div>
                <div style="font-weight:700;font-size:15px;">#<?= e($student['student_id'] ?? '') ?></div>
            </div>
            <div style="flex:1;background:var(--bg-secondary);border-radius:8px;padding:12px 16px;">
                <div class="text-muted" style="font-size:12px;margin-bottom:4px;">Status</div>
                <div><span class="badge badge-<?= ($student['profile_status'] ?? '') === 'Active' ? 'success' : 'danger' ?>"><?= e($student['profile_status'] ?? '') ?></span></div>
            </div>
            <div style="flex:1;background:var(--bg-secondary);border-radius:8px;padding:12px 16px;">
                <div class="text-muted" style="font-size:12px;margin-bottom:4px;">Registered</div>
                <div style="font-size:13px;font-weight:600;"><?= date('M d, Y', strtotime($student['created_at'] ?? 'now')) ?></div>
            </div>
        </div>

        <form id="editProfileForm">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" class="form-control" id="first_name" name="first_name"
                           value="<?= e($student['first_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" class="form-control" id="last_name" name="last_name"
                           value="<?= e($student['last_name']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= e($student['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone"
                           value="<?= e($student['phone'] ?? '') ?>">
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
                <label>QR Code</label>
                <input type="text" class="form-control" value="<?= e($student['qr_code_value'] ?? 'Not assigned') ?>"
                       disabled style="opacity:0.6;">
            </div>

            <div class="d-flex gap-1" style="margin-top:28px;">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i>
                    <span class="btn-text"> Save Changes</span>
                    <span class="spinner" style="display:none;"><i class="fas fa-circle-notch fa-spin"></i></span>
                </button>
                <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
            </div>
        </form>

    </div>
</div>

<script>

const originalValues = {};
document.querySelectorAll('#editProfileForm input:not([disabled]), #editProfileForm select').forEach(el => {
    originalValues[el.name] = el.value;
});

document.getElementById('cancelBtn').addEventListener('click', () => {
    document.querySelectorAll('#editProfileForm input:not([disabled]), #editProfileForm select').forEach(el => {
        el.value = originalValues[el.name];
    });
    App.toast('Changes discarded.', 'info');
});

document.getElementById('editProfileForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.querySelector('.btn-text').style.display = 'none';
    btn.querySelector('.spinner').style.display = 'inline';
    btn.disabled = true;

    const formData = new FormData(e.target);

    try {
        const data = await App.ajax('/studentview/updateProfile', { method: 'POST', body: formData });
        if (data.success) {
            App.toast(data.message, 'success');

            // Sync originalValues so cancel reflects the newly saved state
            document.querySelectorAll('#editProfileForm input:not([disabled]), #editProfileForm select').forEach(el => {
                originalValues[el.name] = el.value;
            });

            // Update avatar & header live
            const firstName = formData.get('first_name').trim();
            const lastName  = formData.get('last_name').trim();
            const course    = formData.get('course');
            const yearLevel = formData.get('year_level');
            const initials  = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();

            document.getElementById('profileAvatar').textContent   = initials;
            document.getElementById('profileFullName').textContent  = firstName + ' ' + lastName;
            document.getElementById('profileCourseYear').textContent = course + ' — ' + yearLevel;
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
</script>