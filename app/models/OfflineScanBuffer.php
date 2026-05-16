<?php

class OfflineScanBuffer extends Model {

    public function bufferScan($data) {
        return $this->insert('offline_scan_buffer', $data);
    }

    public function getUnsynced() {
        $stmt = $this->query("SELECT * FROM offline_scan_buffer WHERE synced_at IS NULL ORDER BY scan_time ASC");
        return $stmt->fetchAll();
    }

    public function syncAll() {
        $db = $this->db;
        $stmt = $db->prepare("CALL sp_sync_offline_scans()");
        return $stmt->execute();
    }
}
