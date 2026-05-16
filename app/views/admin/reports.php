<div class="page-header">
    <div>
        <h1>Incident Reports</h1>
        <p>Generate and view emergency incident reports</p>
    </div>
    <button class="btn btn-primary" onclick="App.modal.open('generateReportModal')">
        <i class="fas fa-file-alt"></i> Generate Report
    </button>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-file-alt"></i> Report History</h3>
        <span class="badge badge-primary"><?= count($reports) ?> reports</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event</th>
                        <th>Generated</th>
                        <th>By</th>
                        <th>Total</th>
                        <th>Safe</th>
                        <th>Missing</th>
                        <th>Summary</th>
                    </tr>
                </thead>
                <tbody id="reportsBody">
                    <?php if (empty($reports)): ?>
                        <tr><td colspan="8" class="text-center text-muted" style="padding:60px;">No reports generated yet</td></tr>
                    <?php else: ?>
                        <?php foreach ($reports as $r): ?>
                        <tr>
                            <td><span class="badge badge-primary">#<?= e($r['report_id']) ?></span></td>
                            <td>
                                <strong><?= e($r['event_type']) ?></strong>
                                <div class="text-muted" style="font-size:11px;"><?= date('M d, Y', strtotime($r['event_datetime'])) ?></div>
                            </td>
                            <td><?= date('M d, Y g:i A', strtotime($r['report_time'])) ?></td>
                            <td><?= e($r['generated_by_name']) ?></td>
                            <td><span class="badge badge-info"><?= (int)$r['total_students'] ?></span></td>
                            <td><span class="badge badge-success"><?= (int)$r['safe_count'] ?></span></td>
                            <td><span class="badge badge-danger"><?= (int)$r['missing_count'] ?></span></td>
                            <td class="text-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <?= e($r['summary_text'] ?? '—') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="generateReportModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-file-alt text-primary" style="color:var(--accent-primary)"></i> Generate Incident Report</h3>
            <button class="modal-close" onclick="App.modal.close('generateReportModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="generateReportForm">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <div class="form-group">
                    <label for="report_event_id">Select Event</label>
                    <select class="form-control" name="event_id" id="report_event_id" required>
                        <option value="">Choose an event...</option>
                        <?php foreach ($events as $ev): ?>
                            <option value="<?= e($ev['event_id']) ?>">
                                <?= e($ev['event_type']) ?> — <?= date('M d, Y g:i A', strtotime($ev['event_datetime'])) ?>
                                (<?= e($ev['status']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="report_summary">Summary Notes</label>
                    <textarea class="form-control" name="summary" id="report_summary" rows="4" placeholder="Incident description, actions taken, observations..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="App.modal.close('generateReportModal')">Cancel</button>
            <button class="btn btn-primary" id="genBtn" onclick="generateReport()">
                <i class="fas fa-file-alt"></i> Generate
            </button>
        </div>
    </div>
</div>

<script>
async function generateReport() {
    const btn = document.getElementById('genBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Generating...';

    const formData = new FormData(document.getElementById('generateReportForm'));

    try {
        const data = await App.ajax('/report/generate', { method: 'POST', body: formData });
        if (data.success) {
            App.toast(data.message, 'success');
            App.modal.close('generateReportModal');
            setTimeout(() => location.reload(), 800);
        } else {
            App.toast(data.message || 'Failed to generate report.', 'error');
        }
    } catch (e) {
        App.toast('An error occurred.', 'error');
    }
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-file-alt"></i> Generate';
}
</script>
