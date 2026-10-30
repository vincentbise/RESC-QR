
<?php
    $page = max(1, (int)($page ?? 1));
    $totalPages = max(1, (int)($totalPages ?? 1));
?>

<div class="page-header">
    <div>
        <h1>QR Codes</h1>
        <p>View and manage student QR codes for emergency scanning</p>
    </div>
    <div class="d-flex align-center gap-1" style="flex-wrap:wrap;">
        <div class="search-bar" style="min-width:200px;">
            <i class="fas fa-search"></i>
            <input type="text" id="qrSearchInput" placeholder="Search students...">
        </div>
        <select class="form-control" id="qrClassFilter" style="width:200px;padding:10px 12px;">
            <option value="">All Classes</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?= e($c['class_id']) ?>" <?= $classId == $c['class_id'] ? 'selected' : '' ?>><?= e($c['section_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Print All
        </button>
    </div>
</div>

<div class="d-flex align-center justify-between" style="margin-bottom:12px;">
    <span class="badge badge-primary" id="qrTotalBadge"><?= $total ?> students</span>
    <div class="pagination-inline" id="paginationTop">
        <?php if ($totalPages > 1): ?>
        <?php if ($page > 1): ?>
            <a href="<?= baseUrl('qr?page=' . ($page - 1) . ($classId ? '&class_id=' . $classId : '')) ?>" class="page-btn" data-page="<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
        <?php else: ?>
            <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
        <?php endif; ?>

        <?php
        $startPage = max(1, $page - 2);
        $endPage   = min($totalPages, $page + 2);
        if ($startPage > 1): ?>
            <a href="<?= baseUrl('qr?page=1' . ($classId ? '&class_id=' . $classId : '')) ?>" class="page-btn" data-page="1">1</a>
            <?php if ($startPage > 2): ?><span class="page-btn ellipsis">&hellip;</span><?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="<?= baseUrl('qr?page=' . $i . ($classId ? '&class_id=' . $classId : '')) ?>"
               class="page-btn <?= $i === $page ? 'active' : '' ?>" data-page="<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($endPage < $totalPages): ?>
            <?php if ($endPage < $totalPages - 1): ?><span class="page-btn ellipsis">&hellip;</span><?php endif; ?>
            <a href="<?= baseUrl('qr?page=' . $totalPages . ($classId ? '&class_id=' . $classId : '')) ?>" class="page-btn" data-page="<?= $totalPages ?>"><?= $totalPages ?></a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
            <a href="<?= baseUrl('qr?page=' . ($page + 1) . ($classId ? '&class_id=' . $classId : '')) ?>" class="page-btn" data-page="<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
        <?php else: ?>
            <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));" id="qrGrid">
    <?php if (empty($students)): ?>
        <div class="empty-state" style="grid-column:1/-1;">
            <i class="fas fa-qrcode"></i>
            <h3>No Students Found</h3>
            <p>Register students first to generate QR codes.</p>
        </div>
    <?php else: ?>
        <?php foreach ($students as $s): ?>
        <div class="card qr-card" style="text-align:center;">
            <div class="card-body" style="padding:20px;">
                <img data-qr="<?= e($s['qr_code_value'] ?? 'NONE') ?>"
                     data-qr-size="160"
                     alt="QR Code for <?= e($s['last_name']) ?>"
                     style="width:160px;height:160px;border-radius:8px;background:#fff;padding:8px;margin-bottom:12px;">
                <div style="font-weight:700;font-size:14px;margin-bottom:2px;">
                    <?= e($s['last_name'] . ', ' . $s['first_name']) ?>
                </div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">
                    <?= e($s['section_name']) ?>
                </div>
                <code style="font-size:10px;color:var(--accent-primary);"><?= e($s['qr_code_value'] ?? '') ?></code>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
(function () {
    const searchInput  = document.getElementById('qrSearchInput');
    const classFilter  = document.getElementById('qrClassFilter');
    let currentPage    = <?= $page ?>;

    async function fetchQR(page) {
        page = page || 1;
        const search  = searchInput.value.trim();
        const classId = classFilter.value;

        try {
            const res = await fetch(
                BASE_URL + '/qr?page=' + page
                    + '&search='   + encodeURIComponent(search)
                    + '&class_id=' + encodeURIComponent(classId),
                { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN } }
            );
            const data = await res.json();
            if (data.success) {
                currentPage = data.page;
                renderCards(data.students);
                document.getElementById('qrTotalBadge').textContent = data.total + ' students';
                renderPagination(data.page, data.total_pages);
            }
        } catch (e) {
            console.error('QR fetch failed:', e);
        }
    }

    const debouncedFetch = App.debounce(() => fetchQR(1), 300);
    searchInput.addEventListener('input', debouncedFetch);
    classFilter.addEventListener('change', () => fetchQR(1));

    function renderCards(students) {
        const grid = document.getElementById('qrGrid');
        if (!students || !students.length) {
            grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><i class="fas fa-qrcode"></i><h3>No Students Found</h3><p>No matching students.</p></div>';
            return;
        }
        grid.innerHTML = students.map(s => {
            const qrValue = App.escapeHtml(s.qr_code_value || 'NONE');
            return `<div class="card qr-card" style="text-align:center;">
                <div class="card-body" style="padding:20px;">
                    <img data-qr="${qrValue}"
                         data-qr-size="160"
                         alt="QR Code"
                         style="width:160px;height:160px;border-radius:8px;background:#fff;padding:8px;margin-bottom:12px;">
                    <div style="font-weight:700;font-size:14px;margin-bottom:2px;">
                        ${App.escapeHtml(s.last_name + ', ' + s.first_name)}
                    </div>
                    <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;">
                        ${App.escapeHtml(s.section_name)}
                    </div>
                    <code style="font-size:10px;color:var(--accent-primary);">${App.escapeHtml(s.qr_code_value || '')}</code>
                </div>
            </div>`;
        }).join('');
        // Render QR codes for the newly inserted cards
        renderAllQRCodes(grid);
    }

    function buildPageBtn(page, label, isActive, isDisabled, isEllipsis) {
        if (isEllipsis) return `<span class="page-btn ellipsis">${label}</span>`;
        if (isDisabled) return `<span class="page-btn disabled">${label}</span>`;
        return `<a href="#" class="page-btn${isActive ? ' active' : ''}" data-page="${page}">${label}</a>`;
    }

    function renderPagination(page, totalPages) {
        const container = document.getElementById('paginationTop');
        if (!container) return;
        if (totalPages <= 1) { container.innerHTML = ''; return; }

        const startPage = Math.max(1, page - 2);
        const endPage   = Math.min(totalPages, page + 2);
        let html = '';

        html += buildPageBtn(page - 1, '<i class="fas fa-chevron-left"></i>', false, page <= 1, false);
        if (startPage > 1) {
            html += buildPageBtn(1, '1', false, false, false);
            if (startPage > 2) html += buildPageBtn(null, '…', false, false, true);
        }
        for (let i = startPage; i <= endPage; i++) {
            html += buildPageBtn(i, i, i === page, false, false);
        }
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += buildPageBtn(null, '…', false, false, true);
            html += buildPageBtn(totalPages, totalPages, false, false, false);
        }
        html += buildPageBtn(page + 1, '<i class="fas fa-chevron-right"></i>', false, page >= totalPages, false);

        container.innerHTML = html;
        container.querySelectorAll('.page-btn[data-page]').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                fetchQR(parseInt(btn.dataset.page));
            });
        });
    }

    // Bind server-rendered pagination buttons
    document.querySelectorAll('#paginationTop .page-btn[data-page]').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            fetchQR(parseInt(btn.dataset.page));
        });
    });
})();
</script>

<style>
/* ── Inline pagination ───────────────────────────────────── */
.pagination-inline {
    display: flex;
    align-items: center;
    gap: 2px;
}

.page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    padding: 0 6px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid transparent;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
    user-select: none;
    color: var(--text-secondary);
    text-decoration: none;
    background: transparent;
    line-height: 1;
}

.page-btn:not(.disabled):not(.active):not(.ellipsis):hover {
    background: var(--bg-card-hover);
    border-color: var(--border-hover);
    color: var(--text-primary);
}

.page-btn.active {
    background: var(--accent-red);
    color: #fff;
    font-weight: 700;
    cursor: default;
    border-color: var(--accent-red);
}

.page-btn.disabled {
    opacity: 0.3;
    cursor: not-allowed;
    pointer-events: none;
}

.page-btn.ellipsis {
    cursor: default;
    border: none;
    pointer-events: none;
    color: var(--text-muted);
}

.justify-between {
    justify-content: space-between;
}

@media print {
    .sidebar, .topbar, .page-header, #qrClassFilter, .search-bar, .pagination-inline, #qrTotalBadge { display:none !important; }
    .main-content { margin-left:0 !important; }
    .qr-card { break-inside:avoid; border:1px solid #ccc !important; }
    body { background:#fff; color:#000; }
    .card-body code { color:#333 !important; }
}
</style>