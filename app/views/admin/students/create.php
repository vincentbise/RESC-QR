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
                        <option value="BSIT">BS Information Technology</option>
                        <option value="BSCS">BS Computer Science</option>
                        <option value="BSIS">BS Information Systems</option>
                        <option value="BSEd">BS Education</option>
                        <option value="BSBA">BS Business Administration</option>
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
                <select class="form-control" id="class_id" name="class_id" required>
                    <option value="">Select Class/Section</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= e($c['class_id']) ?>"><?= e($c['section_name'] . ' — ' . $c['program']) ?></option>
                    <?php endforeach; ?>
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