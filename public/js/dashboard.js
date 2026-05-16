let pollInterval = null;

function startDashboardPolling(intervalMs = 5000) {
    if (pollInterval) clearInterval(pollInterval);
    pollInterval = setInterval(async () => {
        try {
            const data = await App.get('/dashboard/stats');
            if (data.success) {
                updateStatCard('statTotal', data.totalStudents);
                updateStatCard('statSafe', data.safeCount);
                updateStatCard('statMissing', data.missingCount);
                updateStatCard('statEvents', data.activeEvents);
            }
        } catch (e) {
            console.error('Dashboard poll failed:', e);
        }
    }, intervalMs);
}

function updateStatCard(id, newValue) {
    const el = document.getElementById(id);
    if (!el) return;
    const oldValue = parseInt(el.textContent) || 0;
    if (oldValue !== newValue) {
        el.textContent = newValue;
        el.style.transition = 'transform 0.3s ease, color 0.3s ease';
        el.style.transform = 'scale(1.2)';
        el.style.color = newValue > oldValue ? 'var(--accent-success)' : 'var(--accent-warning)';
        setTimeout(() => {
            el.style.transform = 'scale(1)';
            el.style.color = '';
        }, 500);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('statTotal')) {
        startDashboardPolling(5000);
    }
});
