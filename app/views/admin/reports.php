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
        <span class="badge badge-primary" id="reportCount"><?= count($reports) ?> reports</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Generated</th>
                        <th>Total</th>
                        <th>Safe</th>
                        <th>Missing</th>
                        <th>Print</th>
                    </tr>
                </thead>
                <tbody id="reportsBody">
                    <?php if (empty($reports)): ?>
                        <tr><td colspan="6" class="text-center text-muted" style="padding:60px;">No reports generated yet</td></tr>
                    <?php else: ?>
                        <?php foreach ($reports as $r): ?>
                        <tr>
                            <td>
                                <strong><?= e($r['event_type']) ?></strong>
                                <div class="text-muted" style="font-size:11px;"><?= date('M d, Y', strtotime($r['event_datetime'])) ?></div>
                            </td>
                            <td><?= date('M d, Y g:i A', strtotime($r['report_time'])) ?></td>
                            <td><span class="badge badge-info"><?= (int)$r['total_students'] ?></span></td>
                            <td><span class="badge badge-success"><?= (int)$r['safe_count'] ?></span></td>
                            <td><span class="badge badge-danger"><?= (int)$r['missing_count'] ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-secondary btn-icon" title="Print Report"
                                    data-report-id="<?= (int)$r['report_id'] ?>">
                                    <i class="fas fa-print"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Generate Report Modal -->
<div class="modal-overlay" id="generateReportModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-file-alt" style="color:var(--accent-primary)"></i> Generate Incident Report</h3>
            <button class="modal-close" onclick="App.modal.close('generateReportModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="report_event_id">Select Event</label>
                <select class="form-control" id="report_event_id">
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
                <textarea class="form-control" id="report_summary" rows="4"
                    placeholder="Incident description, actions taken, observations..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="App.modal.close('generateReportModal')">Cancel</button>
            <button class="btn btn-primary" id="genBtn" onclick="generateReport()">
                <i class="fas fa-file-alt"></i> Generate
            </button>
        </div>
    </div>
</div>

<!-- Print styles -->
<style>
@media print {
    body * { visibility: hidden !important; }
    #printArea, #printArea * { visibility: visible !important; }
    #printArea {
        position: fixed !important;
        inset: 0 !important;
        padding: 32px 40px !important;
        background: #fff !important;
        font-family: 'Inter', Arial, sans-serif !important;
        font-size: 13px !important;
        color: #111 !important;
    }
}
#printArea { display: none; }
</style>

<!-- Hidden print area -->
<div id="printArea"></div>

<!-- Seed PHP report data into JS so print buttons never rely on inline JSON attributes -->
<script>
const REPORT_DATA = {};
<?php foreach ($reports as $r): ?>
REPORT_DATA[<?= (int)$r['report_id'] ?>] = <?= json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
<?php endforeach; ?>

// Attach print button listeners
document.getElementById('reportsBody').addEventListener('click', function (e) {
    const btn = e.target.closest('[data-report-id]');
    if (!btn) return;
    const id = parseInt(btn.dataset.reportId);
    if (REPORT_DATA[id]) printReport(REPORT_DATA[id]);
});

async function generateReport() {
    const btn     = document.getElementById('genBtn');
    const eventId = document.getElementById('report_event_id').value;
    const summary = document.getElementById('report_summary').value;

    if (!eventId) { App.toast('Please select an event.', 'error'); return; }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Generating...';

    try {
        const data = await App.post('/report/generate', { event_id: eventId, summary });

        if (data.success) {
            App.toast(data.message, 'success');
            App.modal.close('generateReportModal');
            appendReportRow(data.report);
            const badge = document.getElementById('reportCount');
            badge.textContent = ((parseInt(badge.textContent) || 0) + 1) + ' reports';
            document.getElementById('report_event_id').value = '';
            document.getElementById('report_summary').value  = '';
        } else {
            App.toast(data.message || 'Failed to generate report.', 'error');
        }
    } catch (e) {
        App.toast(e.message || 'Server error. Please try again.', 'error');
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-file-alt"></i> Generate';
}

function appendReportRow(r) {
    if (!r) return;

    // Store in the map so the print button can find it
    REPORT_DATA[r.report_id] = r;

    const tbody = document.getElementById('reportsBody');
    const placeholder = tbody.querySelector('td[colspan]');
    if (placeholder) placeholder.closest('tr').remove();

    const fmt = d => d ? new Date(d).toLocaleString('en-PH', {
        month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit'
    }) : '—';
    const fmtDate = d => d ? new Date(d).toLocaleDateString('en-PH', {
        month:'short', day:'numeric', year:'numeric'
    }) : '—';

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <strong>${App.escapeHtml(r.event_type || '')}</strong>
            <div class="text-muted" style="font-size:11px;">${fmtDate(r.event_datetime)}</div>
        </td>
        <td>${fmt(r.report_time)}</td>
        <td><span class="badge badge-info">${parseInt(r.total_students) || 0}</span></td>
        <td><span class="badge badge-success">${parseInt(r.safe_count) || 0}</span></td>
        <td><span class="badge badge-danger">${parseInt(r.missing_count) || 0}</span></td>
        <td>
            <button class="btn btn-sm btn-secondary btn-icon" title="Print Report"
                data-report-id="${parseInt(r.report_id)}">
                <i class="fas fa-print"></i>
            </button>
        </td>`;
    tbody.insertAdjacentElement('afterbegin', tr);
}

function printReport(r) {
    const fmt = d => d ? new Date(d).toLocaleString('en-PH', {
        month:'long', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit'
    }) : '—';
    const fmtDate = d => d ? new Date(d).toLocaleDateString('en-PH', {
        month:'long', day:'numeric', year:'numeric'
    }) : '—';

    const safe       = parseInt(r.safe_count)           || 0;
    const missing    = parseInt(r.missing_count)        || 0;
    const notInClass = parseInt(r.not_in_class_count)   || 0;
    const total      = parseInt(r.total_students)       || 0;
    const pct        = n => total ? Math.round(n / total * 100) + '%' : '0%';

    document.getElementById('printArea').innerHTML = `
        <div style="border-bottom:2px solid #c00;padding-bottom:16px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:flex-end;">
            <div>
                <div style="font-size:20px;font-weight:800;color:#c00;letter-spacing:.5px;">RESC-QR</div>
                <div style="font-size:11px;color:#555;margin-top:2px;">Rapid Emergency Status Checking via Quick Response</div>
                <div style="font-size:11px;color:#555;">University of Southeastern Philippines</div>
            </div>
            <div style="text-align:right;font-size:11px;color:#555;">
                <div style="font-weight:600;font-size:14px;">INCIDENT REPORT</div>
                <div>Printed: ${fmt(new Date().toISOString())}</div>
            </div>
        </div>

        <table style="width:100%;border-collapse:collapse;margin-bottom:24px;">
            <tr>
                <td style="padding:6px 0;width:50%;vertical-align:top;">
                    <span style="font-size:11px;color:#777;text-transform:uppercase;letter-spacing:.5px;">Event Type</span><br>
                    <span style="font-weight:700;font-size:16px;">${escHtml(r.event_type || '—')}</span>
                </td>
                <td style="padding:6px 0;width:50%;vertical-align:top;">
                    <span style="font-size:11px;color:#777;text-transform:uppercase;letter-spacing:.5px;">Event Date & Time</span><br>
                    <span style="font-weight:600;">${fmtDate(r.event_datetime)}</span>
                </td>
            </tr>
            <tr>
                <td style="padding:6px 0;vertical-align:top;">
                    <span style="font-size:11px;color:#777;text-transform:uppercase;letter-spacing:.5px;">Report Generated</span><br>
                    <span style="font-weight:600;">${fmt(r.report_time)}</span>
                </td>
                <td style="padding:6px 0;vertical-align:top;">
                    <span style="font-size:11px;color:#777;text-transform:uppercase;letter-spacing:.5px;">Generated By</span><br>
                    <span style="font-weight:600;">${escHtml(r.generated_by_name || '—')}</span>
                </td>
            </tr>
        </table>

        <div style="margin-bottom:24px;">
            <div style="font-size:11px;color:#777;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Student Status Summary</div>
            <table style="width:100%;border-collapse:collapse;">
                <tr style="background:#f5f5f5;">
                    <th style="padding:10px 14px;text-align:left;font-size:12px;border:1px solid #ddd;">Category</th>
                    <th style="padding:10px 14px;text-align:center;font-size:12px;border:1px solid #ddd;">Count</th>
                    <th style="padding:10px 14px;text-align:center;font-size:12px;border:1px solid #ddd;">Percentage</th>
                </tr>
                <tr>
                    <td style="padding:10px 14px;border:1px solid #ddd;font-weight:600;color:#16a34a;">✓ Safe</td>
                    <td style="padding:10px 14px;border:1px solid #ddd;text-align:center;font-weight:700;">${safe}</td>
                    <td style="padding:10px 14px;border:1px solid #ddd;text-align:center;">${pct(safe)}</td>
                </tr>
                <tr style="background:#fff8f8;">
                    <td style="padding:10px 14px;border:1px solid #ddd;font-weight:600;color:#dc2626;">✗ Missing / Not Yet Scanned</td>
                    <td style="padding:10px 14px;border:1px solid #ddd;text-align:center;font-weight:700;">${missing}</td>
                    <td style="padding:10px 14px;border:1px solid #ddd;text-align:center;">${pct(missing)}</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px;border:1px solid #ddd;font-weight:600;color:#d97706;">— Not in Class</td>
                    <td style="padding:10px 14px;border:1px solid #ddd;text-align:center;font-weight:700;">${notInClass}</td>
                    <td style="padding:10px 14px;border:1px solid #ddd;text-align:center;">${pct(notInClass)}</td>
                </tr>
                <tr style="background:#f5f5f5;">
                    <td style="padding:10px 14px;border:1px solid #ddd;font-weight:700;">Total Students</td>
                    <td style="padding:10px 14px;border:1px solid #ddd;text-align:center;font-weight:700;">${total}</td>
                    <td style="padding:10px 14px;border:1px solid #ddd;text-align:center;">100%</td>
                </tr>
            </table>
        </div>

        <div style="margin-bottom:32px;">
            <div style="font-size:11px;color:#777;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Summary Notes</div>
            <div style="border:1px solid #ddd;border-radius:4px;padding:12px 14px;min-height:60px;font-size:13px;line-height:1.6;color:#333;">
                ${escHtml(r.summary_text || 'No summary notes provided.')}
            </div>
        </div>

        <div style="margin-top:48px;display:flex;gap:48px;">
            <div style="flex:1;border-top:1px solid #333;padding-top:8px;text-align:center;font-size:11px;color:#555;">Prepared by</div>
            <div style="flex:1;border-top:1px solid #333;padding-top:8px;text-align:center;font-size:11px;color:#555;">Noted by</div>
            <div style="flex:1;border-top:1px solid #333;padding-top:8px;text-align:center;font-size:11px;color:#555;">Approved by</div>
        </div>`;

    window.print();
}

function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>