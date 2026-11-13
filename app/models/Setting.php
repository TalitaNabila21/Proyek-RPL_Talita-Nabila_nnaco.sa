<?php

class Setting {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Mengambil nilai pengaturan berdasarkan key
     */
    public function getSetting(string $key, $default = null) {
        $this->db->query("SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1");
        $this->db->bind('key', $key);
        $row = $this->db->single();
        return $row !== false && isset($row['setting_value']) ? $row['setting_value'] : $default;
    }

    /**
     * Mengambil seluruh pengaturan dalam bentuk associative array [key => value]
     */
    public function getAllSettings(): array {
        $this->db->query("SELECT setting_key, setting_value FROM settings");
        $rows = $this->db->resultSet();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    /**
     * Memperbarui atau menyimpan nilai pengaturan
     */
    public function updateSetting(string $key, ?string $value): bool {
        $this->db->query("INSERT INTO settings (setting_key, setting_value) 
                          VALUES (:key, :value) 
                          ON DUPLICATE KEY UPDATE setting_value = :value_update, updated_at = CURRENT_TIMESTAMP");
        $this->db->bind('key', $key);
        $this->db->bind('value', $value);
        $this->db->bind('value_update', $value);
        return $this->db->execute();
    }

    /**
     * Memperbarui beberapa pengaturan sekaligus
     */
    public function updateSettings(array $settings): bool {
        try {
            $this->db->beginTransaction();
            foreach ($settings as $key => $value) {
                $this->updateSetting($key, (string)$value);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
