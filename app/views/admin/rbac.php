<!-- Page Title -->
<div class="rbac-page-title">
    <i class="fas fa-user-shield"></i> Access Control
</div>

<!-- Filter Bar -->
<div class="rbac-filters card">
    <div class="card-body" style="padding: 14px 20px;">
        <div class="filter-row">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="rbacSearch" placeholder="Search users..." autocomplete="off">
            </div>
            <div class="filter-tabs" id="filterTabs">
                <button class="filter-tab active" data-role="">All</button>
                <button class="filter-tab" data-role="admin">Admin</button>
                <button class="filter-tab" data-role="mayor">Mayor</button>
                <button class="filter-tab" data-role="student">Student</button>
            </div>
            <div class="filter-right">
                <div class="rbac-pagination" id="rbacPagination"></div>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card rbac-card">
    <div class="card-body" style="padding:0;">
            <table class="rbac-table">
                <thead>
                    <tr>
                        <th class="col-user">User</th>
                        <th class="col-role">Role</th>
                        <th class="col-mayor">Mayor</th>
                        <?php foreach ($permissions as $key => $label): ?>
                            <th class="col-perm" title="<?= e($label) ?>">
                                <span class="perm-header"><?= e($label) ?></span>
                            </th>
                        <?php endforeach; ?>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="rbacTableBody">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="<?= 4 + count($permissions) ?>" class="text-center text-muted" style="padding:40px;">No users found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user):
                            $isAdmin = $user['user_type'] === 'admin';
                            $isMayor = $user['user_type'] === 'mayor';
                            $hasRow  = !empty($user['permission_id']);

                            if ($isAdmin) {
                                $mayorChecked = false;
                            } elseif ($isMayor) {
                                $mayorChecked = $hasRow ? !empty($user['is_mayor']) : true;
                            } else {
                                $mayorChecked = !empty($user['is_mayor']);
                            }

                            if ($isAdmin) {
                                $effectiveRole = 'admin';
                            } elseif ($isMayor && !$mayorChecked) {
                                $effectiveRole = 'student';
                            } elseif (!$isMayor && $mayorChecked) {
                                $effectiveRole = 'mayor';
                            } else {
                                $effectiveRole = $user['user_type'];
                            }

                            $mayorDefaults = UserPermission::MAYOR_DEFAULTS;
                        ?>
                            <tr class="user-row" 
                                data-user-type="<?= e($user['user_type']) ?>"
                                data-user-id="<?= e($user['user_id']) ?>"
                                data-name="<?= e(strtolower($user['name'])) ?>"
                                data-email="<?= e(strtolower($user['email'] ?? '')) ?>">
                                
                                <!-- User Info -->
                                <td class="col-user" data-label="User">
                                    <div class="user-cell">
                                        <div class="user-avatar-sm <?= e($user['user_type']) ?>">
                                            <?= strtoupper(substr($user['name'], 0, 2)) ?>
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name"><?= e($user['name']) ?></div>
                                            <div class="user-email"><?= e($user['email'] ?? 'N/A') ?></div>
                                            <?php if ($user['section_name']): ?>
                                                <div class="user-section"><?= e($user['section_name']) ?> — <?= e($user['program']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <!-- Effective Role Badge -->
                                <td class="col-role" data-label="Role">
                                    <span class="role-badge role-<?= e($effectiveRole) ?>" data-original="<?= e($user['user_type']) ?>">
                                        <?= ucfirst(e($effectiveRole)) ?>
                                    </span>
                                    <?php if ($effectiveRole !== $user['user_type']): ?>
                                        <div class="role-override"><?= $mayorChecked ? 'Promoted' : 'Demoted' ?></div>
                                    <?php endif; ?>
                                </td>

                                <!-- Is Mayor Toggle -->
                                <td class="col-mayor" data-label="Mayor">
                                    <?php if ($isAdmin): ?>
                                        <span class="mayor-na">—</span>
                                    <?php else: ?>
                                        <label class="toggle-switch">
                                            <input type="checkbox" class="mayor-toggle"
                                                   data-user-type="<?= e($user['user_type']) ?>"
                                                   data-user-id="<?= e($user['user_id']) ?>"
                                                   <?= $mayorChecked ? 'checked' : '' ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    <?php endif; ?>
                                </td>

                                <!-- Permission Checkboxes -->
                                <?php foreach ($permissions as $key => $label):
                                    if ($hasRow) {
                                        $isChecked = !empty($user[$key]);
                                    } elseif ($isMayor) {
                                        $isChecked = !empty($mayorDefaults[$key]);
                                    } else {
                                        $isChecked = false;
                                    }
                                ?>
                                    <td class="col-perm" data-label="<?= e($label) ?>">
                                        <label class="perm-checkbox">
                                            <input type="checkbox" class="perm-check"
                                                   name="<?= $key ?>"
                                                   data-user-type="<?= e($user['user_type']) ?>"
                                                   data-user-id="<?= e($user['user_id']) ?>"
                                                   <?= $isChecked ? 'checked' : '' ?>>
                                            <span class="checkmark"></span>
                                        </label>
                                    </td>
                                <?php endforeach; ?>

                                <!-- Save Button -->
                                <td class="col-actions" data-label="">
                                    <button class="btn-save-perm" 
                                            data-user-type="<?= e($user['user_type']) ?>"
                                            data-user-id="<?= e($user['user_id']) ?>"
                                            title="Save permissions">
                                        <i class="fas fa-save"></i> <span class="save-label">Save</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>
</div>

<!-- Toast Notification -->
<div id="rbacToast" class="rbac-toast" style="display:none;">
    <i class="fas fa-check-circle"></i>
    <span id="rbacToastMsg"></span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const BASE = '<?= BASE_URL ?>';
    const CSRF = '<?= generateCSRFToken() ?>';
    const ROWS_PER_PAGE = 10;
    let currentPage = 1;

    const mayorDefaults = <?= json_encode(\UserPermission::MAYOR_DEFAULTS) ?>;

    // ---- Store original state for each row ----
    const originalStates = {};

    document.querySelectorAll('.user-row').forEach(function(row) {
        const key = row.dataset.userType + '-' + row.dataset.userId;
        const state = {};
        const mt = row.querySelector('.mayor-toggle');
        state.is_mayor = mt ? mt.checked : false;
        row.querySelectorAll('.perm-check').forEach(function(cb) {
            state[cb.name] = cb.checked;
        });
        originalStates[key] = state;
    });

    function isRowDirty(row) {
        const key = row.dataset.userType + '-' + row.dataset.userId;
        const orig = originalStates[key];
        if (!orig) return true;

        const mt = row.querySelector('.mayor-toggle');
        if (mt && mt.checked !== orig.is_mayor) return true;

        var dirty = false;
        row.querySelectorAll('.perm-check').forEach(function(cb) {
            if (cb.checked !== orig[cb.name]) dirty = true;
        });
        return dirty;
    }

    function updateRowDirty(row) {
        const btn = row.querySelector('.btn-save-perm');
        if (isRowDirty(row)) {
            btn.classList.add('dirty');
        } else {
            btn.classList.remove('dirty');
        }
    }

    function snapshotRow(row) {
        const key = row.dataset.userType + '-' + row.dataset.userId;
        const state = {};
        const mt = row.querySelector('.mayor-toggle');
        state.is_mayor = mt ? mt.checked : false;
        row.querySelectorAll('.perm-check').forEach(function(cb) {
            state[cb.name] = cb.checked;
        });
        originalStates[key] = state;
    }

    // ---- Mayor toggle handler ----
    document.querySelectorAll('.mayor-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const row = this.closest('tr');
            const isChecked = this.checked;
            const userType = this.dataset.userType;

            if (isChecked) {
                Object.keys(mayorDefaults).forEach(function(key) {
                    const cb = row.querySelector('.perm-check[name="' + key + '"]');
                    if (cb) cb.checked = !!mayorDefaults[key];
                });
            } else {
                row.querySelectorAll('.perm-check').forEach(function(cb) {
                    cb.checked = false;
                });
            }

            var badge = row.querySelector('.role-badge');
            var overrideEl = row.querySelector('.role-override');
            var effectiveRole;

            if (userType === 'mayor' && !isChecked) {
                effectiveRole = 'student';
            } else if (userType === 'student' && isChecked) {
                effectiveRole = 'mayor';
            } else {
                effectiveRole = userType;
            }

            badge.className = 'role-badge role-' + effectiveRole;
            badge.textContent = effectiveRole.charAt(0).toUpperCase() + effectiveRole.slice(1);

            if (effectiveRole !== userType) {
                if (!overrideEl) {
                    overrideEl = document.createElement('div');
                    overrideEl.className = 'role-override';
                    badge.parentNode.appendChild(overrideEl);
                }
                overrideEl.textContent = isChecked ? 'Promoted' : 'Demoted';
            } else if (overrideEl) {
                overrideEl.remove();
            }

            updateRowDirty(row);
        });
    });

    // Permission checkbox change
    document.querySelectorAll('.perm-check').forEach(function(cb) {
        cb.addEventListener('change', function() {
            updateRowDirty(this.closest('tr'));
        });
    });

    // ---- Confirmation modal (same style as logout) ----
    function showConfirmModal(userName, roleMsg) {
        return new Promise(function(resolve) {
            var overlay = document.createElement('div');
            overlay.className = 'logout-modal-overlay active';
            overlay.innerHTML =
                '<div class="logout-modal">' +
                    '<div class="logout-modal-icon" style="background:rgba(204,27,43,0.12);color:var(--accent-primary);">' +
                        '<i class="fas fa-shield-alt"></i>' +
                    '</div>' +
                    '<h3 class="logout-modal-title">Confirm Changes</h3>' +
                    '<p class="logout-modal-text">Save permission changes for <strong>' + userName + '</strong>?' +
                        (roleMsg ? '<br>' + roleMsg : '') +
                    '</p>' +
                    '<div class="logout-modal-actions">' +
                        '<button type="button" class="btn btn-secondary" id="rbacConfirmNo">Cancel</button>' +
                        '<button type="button" class="btn btn-danger" id="rbacConfirmYes">' +
                            '<i class="fas fa-save"></i> Save' +
                        '</button>' +
                    '</div>' +
                '</div>';

            document.body.appendChild(overlay);

            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) { overlay.remove(); resolve(false); }
            });
            overlay.querySelector('#rbacConfirmYes').onclick = function() { overlay.remove(); resolve(true); };
            overlay.querySelector('#rbacConfirmNo').onclick = function() { overlay.remove(); resolve(false); };
        });
    }

    // ---- Save button handler ----
    document.querySelectorAll('.btn-save-perm').forEach(function(btn) {
        btn.addEventListener('click', async function() {
            const userType = this.dataset.userType;
            const userId   = this.dataset.userId;
            const row      = this.closest('tr');
            const saveBtn  = this;
            const userName = row.querySelector('.user-name').textContent;

            var roleMsg = '';
            const mayorToggle = row.querySelector('.mayor-toggle');
            if (mayorToggle) {
                var original = row.dataset.userType;
                if (original === 'student' && mayorToggle.checked) {
                    roleMsg = 'This will <strong>promote</strong> this student to Mayor on next login.';
                } else if (original === 'mayor' && !mayorToggle.checked) {
                    roleMsg = 'This will <strong>demote</strong> this mayor to Student on next login.';
                }
            }

            const confirmed = await showConfirmModal(userName, roleMsg);
            if (!confirmed) return;

            const data = {
                user_type: userType,
                user_id: parseInt(userId),
                is_mayor: mayorToggle ? (mayorToggle.checked ? 1 : 0) : 0,
            };

            row.querySelectorAll('.perm-check').forEach(function(cb) {
                data[cb.name] = cb.checked ? 1 : 0;
            });

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(BASE + '/rbac/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify(data)
            })
            .then(function(res) { return res.json(); })
            .then(function(result) {
                if (result.success) {
                    showToast(result.message, 'success');
                    snapshotRow(row);
                    updateRowDirty(row);
                } else {
                    showToast(result.message || 'Failed to save.', 'error');
                }
            })
            .catch(function() {
                showToast('Network error. Please try again.', 'error');
            })
            .finally(function() {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> <span class="save-label">Save</span>';
            });
        });
    });

    // ---- Filter tabs ----
    const filterTabs = document.querySelectorAll('.filter-tab');
    const searchInput = document.getElementById('rbacSearch');
    let activeRole = '';

    filterTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            filterTabs.forEach(function(t) { t.classList.remove('active'); });
            this.classList.add('active');
            activeRole = this.dataset.role;
            currentPage = 1;
            applyFilters();
        });
    });

    function applyFilters() {
        const query = searchInput.value.toLowerCase().trim();
        let visible = 0;
        const allRows = document.querySelectorAll('.user-row');
        const matchedRows = [];

        allRows.forEach(function(row) {
            const name  = row.dataset.name || '';
            const email = row.dataset.email || '';
            const type  = row.dataset.userType;

            const matchSearch = !query || name.includes(query) || email.includes(query);
            const matchRole   = !activeRole || type === activeRole;

            if (matchSearch && matchRole) {
                matchedRows.push(row);
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        // Pagination
        const totalPages = Math.max(1, Math.ceil(visible / ROWS_PER_PAGE));
        if (currentPage > totalPages) currentPage = totalPages;

        const startIdx = (currentPage - 1) * ROWS_PER_PAGE;
        const endIdx   = startIdx + ROWS_PER_PAGE;

        matchedRows.forEach(function(row, i) {
            row.style.display = (i >= startIdx && i < endIdx) ? '' : 'none';
        });

        renderPagination(totalPages, visible);
    }

    function renderPagination(totalPages, totalItems) {
        const container = document.getElementById('rbacPagination');
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<button class="page-btn" data-page="' + (currentPage - 1) + '" ' + (currentPage <= 1 ? 'disabled' : '') + '><i class="fas fa-chevron-left"></i></button>';

        var startP = Math.max(1, currentPage - 2);
        var endP = Math.min(totalPages, startP + 4);
        if (endP - startP < 4) startP = Math.max(1, endP - 4);

        for (var p = startP; p <= endP; p++) {
            html += '<button class="page-btn' + (p === currentPage ? ' active' : '') + '" data-page="' + p + '">' + p + '</button>';
        }

        html += '<button class="page-btn" data-page="' + (currentPage + 1) + '" ' + (currentPage >= totalPages ? 'disabled' : '') + '><i class="fas fa-chevron-right"></i></button>';

        container.innerHTML = html;

        container.querySelectorAll('.page-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (this.disabled) return;
                currentPage = parseInt(this.dataset.page);
                applyFilters();
            });
        });
    }

    searchInput.addEventListener('input', function() {
        currentPage = 1;
        applyFilters();
    });

    // Initial pagination
    applyFilters();

    // Toast helper
    function showToast(message, type) {
        const toast = document.getElementById('rbacToast');
        const msgEl = document.getElementById('rbacToastMsg');
        const icon  = toast.querySelector('i');

        msgEl.textContent = message;
        toast.className = 'rbac-toast ' + (type === 'error' ? 'toast-error' : 'toast-success');
        icon.className = type === 'error' ? 'fas fa-times-circle' : 'fas fa-check-circle';
        toast.style.display = 'flex';

        setTimeout(function() {
            toast.style.display = 'none';
        }, 3000);
    }
});
</script>
