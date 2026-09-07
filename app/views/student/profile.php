<div class="page-header">
    <div>
        <h1>My Profile</h1>
        <p>View and update your personal and academic information</p>
    </div>
</div>

<div style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;">

<div class="card" style="flex:1 1 420px;max-width:700px;">
    <div class="card-body">

        <form id="editProfileForm" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

        <?php $hasPhoto = !empty($student['profile_image']); ?>
        <div style="text-align:center;margin-bottom:28px;">
            <div style="position:relative;width:80px;height:80px;margin:0 auto 12px;">
                <div class="avatar" id="profileAvatar" style="width:80px;height:80px;font-size:28px;background:linear-gradient(135deg,var(--accent-primary),var(--accent-secondary));overflow:hidden;">
                    <img id="profileAvatarImg" src="<?= $hasPhoto ? e(publicUrl('img/profiles/' . $student['profile_image'])) : '' ?>"
                         alt="Profile photo" style="width:100%;height:100%;object-fit:cover;display:<?= $hasPhoto ? 'block' : 'none' ?>;">
                    <span id="profileAvatarInitials" style="display:<?= $hasPhoto ? 'none' : 'inline' ?>;">
                        <?= strtoupper(substr($student['first_name'] ?? '',0,1) . substr($student['last_name'] ?? '',0,1)) ?>
                    </span>
                </div>
                <label for="profileImageInput" title="Change photo"
                       style="position:absolute;bottom:-2px;right:-2px;width:28px;height:28px;border-radius:50%;background:var(--accent-primary);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;border:2px solid var(--bg-primary);font-size:12px;">
                    <i class="fas fa-camera"></i>
                </label>
            </div>

            <input type="file" id="profileImageInput" name="profile_image" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none;">
            <input type="hidden" id="removeProfileImageFlag" name="remove_profile_image" value="0">

            <div id="photoActions" style="margin-bottom:10px;<?= $hasPhoto ? '' : 'display:none;' ?>">
                <button type="button" class="btn-link" id="removePhotoBtn" style="background:none;border:none;color:var(--accent-danger);font-size:14px;cursor:pointer;padding:0;">
                    Remove photo
                </button>
            </div>

            <div style="font-size:20px;font-weight:800;" id="profileFullName"><?= e(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></div>
            <div class="text-muted" id="profileCourseYear"><?= e($student['course'] ?? '') ?> — <?= e($student['year_level'] ?? '') ?></div>
        </div>

        <div style="display:flex;gap:16px;margin-bottom:24px;">
            <div style="flex:1;background:var(--bg-secondary);border-radius:8px;padding:12px 16px;">
                <div class="text-muted" style="font-size:12px;margin-bottom:4px;">Registered</div>
                <div style="font-size:13px;font-weight:600;"><?= date('M d, Y', strtotime($student['created_at'] ?? 'now')) ?></div>
            </div>
        </div>

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

            <div class="d-flex gap-1" id="profileActionBar" style="margin-top:28px;overflow:hidden;max-height:0;opacity:0;transition:max-height 0.3s ease, opacity 0.3s ease;display:none;">
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

<div class="card" style="flex:1 1 340px;max-width:420px;">
    <div class="card-body">
        <h3 style="margin:0 0 4px;font-size:16px;font-weight:800;">Change Password</h3>
        <p class="text-muted" style="margin:0 0 20px;font-size:13px;">Update the password you used to sign in.</p>

        <form id="changePasswordForm">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

            <div class="form-group">
                <label for="current_password">Current Password *</label>
                <div style="position:relative;">
                    <input type="password" class="form-control" id="current_password" name="current_password"
                           autocomplete="current-password" required style="padding-right:40px;">
                    <button type="button" class="pwd-toggle-btn" data-target="current_password"
                            style="position:absolute;top:50%;right:10px;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;padding:4px;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="new_password">New Password *</label>
                <div style="position:relative;">
                    <input type="password" class="form-control" id="new_password" name="new_password"
                           autocomplete="new-password" required style="padding-right:40px;">
                    <button type="button" class="pwd-toggle-btn" data-target="new_password"
                            style="position:absolute;top:50%;right:10px;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;padding:4px;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="text-muted" id="newPasswordHint" style="display:none;font-size:12px;margin-top:6px;">
                    At least 8 characters, with an uppercase letter, a lowercase letter, a number, and a symbol.
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password *</label>
                <div style="position:relative;">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                           autocomplete="new-password" required style="padding-right:40px;">
                    <button type="button" class="pwd-toggle-btn" data-target="confirm_password"
                            style="position:absolute;top:50%;right:10px;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;padding:4px;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div id="passwordFormError" class="text-muted" style="display:none;color:var(--accent-danger);font-size:12px;margin-bottom:13px;"></div>

            <div class="d-flex gap-1" id="passwordActionBar" style="margin-top:8px;display:none;overflow:hidden;transition:max-height 0.3s ease,opacity 0.3s ease;max-height:0;opacity:0;">
                <button type="submit" class="btn btn-primary" id="changePasswordBtn">
                    <i class="fas fa-key"></i>
                    <span class="btn-text"> Update Password</span>
                    <span class="spinner" style="display:none;"><i class="fas fa-circle-notch fa-spin"></i></span>
                </button>
            </div>
        </form>
    </div>
</div>

</div>

<script>

const originalValues = {};
document.querySelectorAll('#editProfileForm input:not([type="file"]):not([disabled]), #editProfileForm select').forEach(el => {
    originalValues[el.name] = el.value;
});

const profileActionBar = document.getElementById('profileActionBar');
let photoChanged = false;

function checkProfileDirty() {
    let dirty = photoChanged;
    if (!dirty) {
        document.querySelectorAll('#editProfileForm input:not([type="file"]):not([disabled]), #editProfileForm select').forEach(el => {
            if (originalValues[el.name] !== undefined && el.value !== originalValues[el.name]) dirty = true;
        });
    }
    if (dirty) {
        profileActionBar.style.display = 'flex';
        requestAnimationFrame(() => {
            profileActionBar.style.maxHeight = '80px';
            profileActionBar.style.opacity   = '1';
        });
    } else {
        profileActionBar.style.maxHeight = '0';
        profileActionBar.style.opacity   = '0';
        setTimeout(() => { profileActionBar.style.display = 'none'; }, 300);
    }
}

document.querySelectorAll('#editProfileForm input:not([type="file"]):not([disabled]), #editProfileForm select').forEach(el => {
    el.addEventListener('input', checkProfileDirty);
    el.addEventListener('change', checkProfileDirty);
});

const avatarImg      = document.getElementById('profileAvatarImg');
const avatarInitials = document.getElementById('profileAvatarInitials');
const fileInput       = document.getElementById('profileImageInput');
const removeFlag      = document.getElementById('removeProfileImageFlag');
const photoActions    = document.getElementById('photoActions');
let originalAvatarSrc = avatarImg.src;
let hadPhotoInitially  = avatarImg.style.display !== 'none';

const MAX_IMAGE_BYTES = 3 * 1024 * 1024;
const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

function showAvatarImage(src) {
    avatarImg.src = src;
    avatarImg.style.display = 'block';
    avatarInitials.style.display = 'none';
    photoActions.style.display = 'block';
}

function showAvatarInitials() {
    avatarImg.style.display = 'none';
    avatarImg.removeAttribute('src');
    avatarInitials.style.display = 'inline';
    photoActions.style.display = 'none';
}

fileInput.addEventListener('change', () => {
    const file = fileInput.files[0];
    if (!file) return;

    if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
        App.toast('Please choose a JPG, PNG, WEBP, or GIF image.', 'error');
        fileInput.value = '';
        return;
    }
    if (file.size > MAX_IMAGE_BYTES) {
        App.toast('Image is too large. Maximum size is 3MB.', 'error');
        fileInput.value = '';
        return;
    }

    removeFlag.value = '0';
    photoChanged = true;
    checkProfileDirty();
    const reader = new FileReader();
    reader.onload = (e) => showAvatarImage(e.target.result);
    reader.readAsDataURL(file);
});

document.getElementById('removePhotoBtn').addEventListener('click', () => {
    fileInput.value = '';
    removeFlag.value = '1';
    photoChanged = true;
    checkProfileDirty();
    showAvatarInitials();
});

document.getElementById('cancelBtn').addEventListener('click', () => {
    document.querySelectorAll('#editProfileForm input:not([type="file"]):not([disabled]), #editProfileForm select').forEach(el => {
        el.value = originalValues[el.name];
    });
    fileInput.value = '';
    removeFlag.value = '0';
    if (hadPhotoInitially) {
        showAvatarImage(originalAvatarSrc);
    } else {
        showAvatarInitials();
    }
    photoChanged = false;
    checkProfileDirty();
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

            document.querySelectorAll('#editProfileForm input:not([type="file"]):not([disabled]), #editProfileForm select').forEach(el => {
                originalValues[el.name] = el.value;
            });
            removeFlag.value = '0';
            fileInput.value = '';
            photoChanged = false;
            checkProfileDirty();

            const firstName = formData.get('first_name').trim();
            const lastName  = formData.get('last_name').trim();
            const course    = formData.get('course');
            const yearLevel = formData.get('year_level');
            const initials  = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();

            avatarInitials.textContent = initials;
            document.getElementById('profileFullName').textContent  = firstName + ' ' + lastName;
            document.getElementById('profileCourseYear').textContent = course + ' — ' + yearLevel;

            const sidebarName = document.getElementById('sidebarUserName');
            if (sidebarName) sidebarName.textContent = firstName + ' ' + lastName;

            const sidebarAvatar = document.getElementById('sidebarUserAvatar');
            if (data.student && data.student.profile_image) {
                const newAvatarSrc = BASE_URL + '/public/img/profiles/' + data.student.profile_image;
                showAvatarImage(newAvatarSrc);
                originalAvatarSrc = newAvatarSrc;
                hadPhotoInitially = true;
                if (sidebarAvatar) {
                    sidebarAvatar.innerHTML = '<img src="' + newAvatarSrc + '" alt="Profile photo" style="width:100%;height:100%;object-fit:cover;">';
                }
            } else {
                showAvatarInitials();
                originalAvatarSrc = '';
                hadPhotoInitially = false;
                if (sidebarAvatar) {
                    sidebarAvatar.textContent = initials;
                }
            }

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

const currentPasswordInput = document.getElementById('current_password');
const newPasswordInput     = document.getElementById('new_password');
const confirmPasswordInput = document.getElementById('confirm_password');
const changePasswordBtn    = document.getElementById('changePasswordBtn');
const passwordActionBar    = document.getElementById('passwordActionBar');
const passwordFormError    = document.getElementById('passwordFormError');
const changePasswordForm   = document.getElementById('changePasswordForm');
const newPasswordHint      = document.getElementById('newPasswordHint');

document.querySelectorAll('.pwd-toggle-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        const icon  = btn.querySelector('i');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        icon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
    });
});

function showPasswordError(message) {
    passwordFormError.textContent = message;
    passwordFormError.style.display = message ? 'block' : 'none';
}

function checkPasswordDirty() {
    const dirty = currentPasswordInput.value !== '' ||
                  newPasswordInput.value !== '' ||
                  confirmPasswordInput.value !== '';

    if (dirty) {
        passwordActionBar.style.display = 'flex';
        requestAnimationFrame(() => {
            passwordActionBar.style.maxHeight = '80px';
            passwordActionBar.style.opacity   = '1';
        });
    } else {
        passwordActionBar.style.maxHeight = '0';
        passwordActionBar.style.opacity   = '0';
        setTimeout(() => { passwordActionBar.style.display = 'none'; }, 300);
    }
}

newPasswordInput.addEventListener('input', () => {
    newPasswordHint.style.display = newPasswordInput.value !== '' ? 'block' : 'none';
});

[currentPasswordInput, newPasswordInput, confirmPasswordInput].forEach(el => {
    el.addEventListener('input', () => {
        showPasswordError('');
        checkPasswordDirty();
    });
});

const NEW_PASSWORD_PATTERN = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{6,}$/;

changePasswordForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    showPasswordError('');

    const currentPassword = currentPasswordInput.value;
    const newPassword     = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    if (currentPassword === '' || newPassword === '' || confirmPassword === '') {
        showPasswordError('Please fill in all fields.');
        return;
    }

    if (newPassword !== confirmPassword) {
        showPasswordError('New password does not match.');
        return;
    }

    if (!NEW_PASSWORD_PATTERN.test(newPassword)) {
        showPasswordError('New password must be at least 8 characters and include an uppercase letter, a lowercase letter, a number, and a symbol.');
        return;
    }

    const btn = changePasswordBtn;
    btn.querySelector('.btn-text').style.display = 'none';
    btn.querySelector('.spinner').style.display = 'inline';
    btn.disabled = false;

    try {
        const data = await App.ajax('/studentview/changePassword', {
            method: 'POST',
            body: {
                current_password: currentPassword,
                new_password: newPassword,
                confirm_password: confirmPassword
            }
        });

        if (data.success) {
            App.toast(data.message, 'success');
            changePasswordForm.reset();
            newPasswordHint.style.display = 'none';
            checkPasswordDirty();
        } else {
            showPasswordError(data.message || 'Unable to change password.');
        }
    } catch (err) {
        showPasswordError(err.message || 'An error occurred.');
    }

    btn.querySelector('.btn-text').style.display = 'inline';
    btn.querySelector('.spinner').style.display = 'none';
    checkPasswordDirty();
});
</script>