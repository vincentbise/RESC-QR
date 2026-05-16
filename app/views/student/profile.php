<div class="page-header">
    <div>
        <h1>My Profile</h1>
        <p>View your personal and academic information</p>
    </div>
</div>

<div class="card" style="max-width:600px;">
    <div class="card-body">
        <div style="text-align:center;margin-bottom:28px;">
            <div class="avatar" style="width:80px;height:80px;font-size:28px;margin:0 auto 12px;background:linear-gradient(135deg,var(--accent-primary),var(--accent-secondary));">
                <?= strtoupper(substr($student['first_name'] ?? '',0,1) . substr($student['last_name'] ?? '',0,1)) ?>
            </div>
            <div style="font-size:20px;font-weight:800;"><?= e(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></div>
            <div class="text-muted"><?= e($student['course'] ?? '') ?> — <?= e($student['year_level'] ?? '') ?></div>
        </div>

        <table style="width:100%;">
            <tr>
                <td class="text-muted" style="padding:12px 0;width:140px;border-bottom:1px solid var(--border-color);">Student ID</td>
                <td style="padding:12px 0;border-bottom:1px solid var(--border-color);font-weight:600;">#<?= e($student['student_id'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="text-muted" style="padding:12px 0;border-bottom:1px solid var(--border-color);">Section</td>
                <td style="padding:12px 0;border-bottom:1px solid var(--border-color);"><span class="badge badge-info"><?= e($student['section_name'] ?? '') ?></span></td>
            </tr>
            <tr>
                <td class="text-muted" style="padding:12px 0;border-bottom:1px solid var(--border-color);">Email</td>
                <td style="padding:12px 0;border-bottom:1px solid var(--border-color);"><?= e($student['email'] ?? '—') ?></td>
            </tr>
            <tr>
                <td class="text-muted" style="padding:12px 0;border-bottom:1px solid var(--border-color);">Phone</td>
                <td style="padding:12px 0;border-bottom:1px solid var(--border-color);"><?= e($student['phone'] ?? '—') ?></td>
            </tr>
            <tr>
                <td class="text-muted" style="padding:12px 0;border-bottom:1px solid var(--border-color);">QR Code</td>
                <td style="padding:12px 0;border-bottom:1px solid var(--border-color);"><code style="color:var(--accent-primary)"><?= e($student['qr_code_value'] ?? '—') ?></code></td>
            </tr>
            <tr>
                <td class="text-muted" style="padding:12px 0;border-bottom:1px solid var(--border-color);">Status</td>
                <td style="padding:12px 0;border-bottom:1px solid var(--border-color);"><span class="badge badge-<?= ($student['profile_status'] ?? '') === 'Active' ? 'success' : 'danger' ?>"><?= e($student['profile_status'] ?? '') ?></span></td>
            </tr>
            <tr>
                <td class="text-muted" style="padding:12px 0;">Registered</td>
                <td style="padding:12px 0;"><?= date('M d, Y', strtotime($student['created_at'] ?? 'now')) ?></td>
            </tr>
        </table>
    </div>
</div>
