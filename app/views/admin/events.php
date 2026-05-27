<div class="page-header">
    <div>
        <h1>Emergency Events</h1>
        <p>Manage earthquake and emergency event instances</p>
    </div>
    <button class="btn btn-danger" onclick="App.modal.open('createEventModal')">
        <i class="fas fa-plus"></i> Declare Emergency
    </button>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-exclamation-triangle"></i> Event History</h3>
        <span class="badge badge-primary"><?= count($events) ?> events</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Date &amp; Time</th>
                        <th>Description</th>
                        <th>Created By</th>
                        <th>Safe</th>
                        <th>Missing</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr><td colspan="8" class="text-center text-muted" style="padding:60px;">No emergency events recorded</td></tr>
                    <?php else: ?>
                        <?php foreach ($events as $ev): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-center gap-1">
                                    <div class="stat-icon" style="width:32px;height:32px;font-size:14px;background:rgba(239,68,68,0.12);color:var(--accent-danger);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-<?= $ev['event_type'] === 'Earthquake' ? 'house-crack' : 'exclamation-triangle' ?>"></i>
                                    </div>
                                    <strong><?= e($ev['event_type']) ?></strong>
                                </div>
                            </td>
                            <td><?= date('M d, Y g:i A', strtotime($ev['event_datetime'])) ?></td>
                            <td class="text-muted"><?= e($ev['description'] ?? '—') ?></td>
                            <td><?= e($ev['created_by_name']) ?></td>
                            <td><span class="badge badge-success"><?= (int)($ev['safe_count'] ?? 0) ?></span></td>
                            <td><span class="badge badge-danger"><?= (int)($ev['missing_count'] ?? 0) ?></span></td>
                            <td>
                                <?php if ($ev['status'] === 'Active'): ?>
                                    <span class="badge badge-danger" style="animation:pulse 2s infinite;">● ACTIVE</span>
                                <?php else: ?>
                                    <span class="badge badge-primary">Closed</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($ev['status'] === 'Active'): ?>
                                    <form method="POST" action="<?= baseUrl('event/close/' . $ev['event_id']) ?>" style="display:inline;" onsubmit="return confirm('Close this event? This will finalize all student statuses.');">
                                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="fas fa-times-circle"></i> Close
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="createEventModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle text-danger"></i> Declare Emergency Event</h3>
            <button type="button" class="modal-close" onclick="App.modal.close('createEventModal')">&times;</button>
        </div>
        <form method="POST" action="<?= baseUrl('event/store') ?>">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label for="event_type">Emergency Type</label>
                    <select class="form-control" name="event_type" id="event_type" required>
                        <option value="Earthquake">Earthquake</option>
                        <option value="Fire">Fire</option>
                        <option value="Flood">Flood</option>
                        <option value="Typhoon">Typhoon</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" name="description" id="description" rows="3" placeholder="Brief description of the emergency..."></textarea>
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    <span>Creating an event will mark <strong>all active students</strong> as "Missing" until scanned.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="App.modal.close('createEventModal')">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-exclamation-triangle"></i> Declare Emergency
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.5;} }
</style>