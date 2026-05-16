<?php

class Model {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    protected function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected function findAll($table, $orderBy = 'created_at DESC') {
        $stmt = $this->db->prepare("SELECT * FROM {$table} ORDER BY {$orderBy}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    protected function findById($table, $idColumn, $id) {
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE {$idColumn} = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    protected function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    protected function update($table, $data, $idColumn, $id) {
        $set = [];
        foreach (array_keys($data) as $col) {
            $set[] = "{$col} = :{$col}";
        }
        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE {$idColumn} = :_id";
        $data['_id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    protected function delete($table, $idColumn, $id) {
        $stmt = $this->db->prepare("DELETE FROM {$table} WHERE {$idColumn} = :id");
        return $stmt->execute([':id' => $id]);
    }

    protected function count($table, $where = '', $params = []) {
        $sql = "SELECT COUNT(*) as total FROM {$table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }
}
