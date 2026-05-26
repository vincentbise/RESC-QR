<div class="page-header">
    <div>
        <h1>Scan Logs</h1>
        <p>Audit trail of all QR code scan activities</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Scan Activity</h3>
        <span class="badge badge-info" id="logCount"><?= count($logs) ?> records</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Section</th>
                        <th>Event</th>
                        <th>Scanned By</th>
                        <th>Time</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody id="logsBody">
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="7" class="text-center text-muted" style="padding:60px;">No scan records yet</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><span class="text-muted">#<?= e($log['scan_id']) ?></span></td>
                            <td><strong><?= e($log['first_name'] . ' ' . $log['last_name']) ?></strong></td>
                            <td><span class="badge badge-info"><?= e($log['section_name']) ?></span></td>
                            <td>
                                <?= e($log['event_type']) ?>
                                <div class="text-muted" style="font-size:11px;"><?= date('M d', strtotime($log['event_datetime'])) ?></div>
                            </td>
                            <td><?= e($log['mayor_name']) ?></td>
                            <td><?= date('M d, g:i:s A', strtotime($log['scan_time'])) ?></td>
                            <td>
                                <span class="badge badge-<?= $log['scan_result'] === 'Valid' ? 'success' : 'danger' ?>">
                                    <?= e($log['scan_result']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>