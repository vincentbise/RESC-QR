let pollInterval = null;

function startDashboardPolling(intervalMs = 10000) {
    if (pollInterval) clearInterval(pollInterval);
    pollInterval = setInterval(async () => {
        try {
            const data = await App.ajax('/dashboard/stats', { method: 'GET' });
            if (data.success) {
                updateStatCard('statTotal', data.totalStudents);
                updateStatCard('statSafe', data.safeCount);
                updateStatCard('statMissing', data.missingCount);
                updateStatCard('statEvents', data.activeEvents);

                if (data.recentScans && data.recentScans.length) {
                    renderRecentScans(data.recentScans);
                }
            }
        } catch (e) {
            console.warn('Dashboard poll error:', e);
        }
    }, intervalMs);
}

function updateStatCard(id, newValue) {
    const el = document.getElementById(id);
    if (!el) return;
    const oldValue = parseInt(el.textContent) || 0;
    const nv = parseInt(newValue) || 0;
    if (oldValue !== nv) {
        el.textContent = nv;
        el.style.transition = 'transform 0.3s ease, color 0.3s ease';
        el.style.transform = 'scale(1.2)';
        el.style.color = nv > oldValue ? 'var(--accent-success)' : 'var(--accent-warning)';
        setTimeout(() => {
            el.style.transform = 'scale(1)';
            el.style.color = '';
        }, 500);
    }
}

function renderRecentScans(scans) {
    const tbody = document.getElementById('recentScansBody');
    if (!tbody) return;
    tbody.innerHTML = scans.map(s => `
        <tr>
            <td><strong>${App.escapeHtml(s.first_name + ' ' + s.last_name)}</strong></td>
            <td>${App.escapeHtml(s.section_name)}</td>
            <td>${App.formatDate(s.scan_time)}</td>
            <td><span class="badge badge-${s.scan_result === 'Valid' ? 'success' : 'danger'}">${App.escapeHtml(s.scan_result)}</span></td>
        </tr>
    `).join('');
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('statTotal')) {
        startDashboardPolling(10000);
    }
});