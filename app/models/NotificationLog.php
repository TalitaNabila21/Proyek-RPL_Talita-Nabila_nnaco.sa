<?php

class NotificationLog {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Memeriksa apakah notifikasi dengan jenis dan penerima tertentu sudah berhasil terkirim (Idempotency)
     */
    public function hasSuccessfulLog(int $orderId, string $recipientType, string $notificationType): bool {
        $this->db->query("SELECT id FROM notification_logs 
                          WHERE order_id = :order_id 
                          AND recipient_type = :recipient_type 
                          AND notification_type = :notification_type 
                          AND message_status = 'sent' 
                          LIMIT 1");
        $this->db->bind('order_id', $orderId);
        $this->db->bind('recipient_type', $recipientType);
        $this->db->bind('notification_type', $notificationType);
        return (bool)$this->db->single();
    }

    /**
     * Mengambil log terakhir untuk kombinasi order, recipient, dan jenis notifikasi
     */
    public function getLogForIdempotency(int $orderId, string $recipientType, string $notificationType) {
        $this->db->query("SELECT * FROM notification_logs 
                          WHERE order_id = :order_id 
                          AND recipient_type = :recipient_type 
                          AND notification_type = :notification_type 
                          ORDER BY id DESC LIMIT 1");
        $this->db->bind('order_id', $orderId);
        $this->db->bind('recipient_type', $recipientType);
        $this->db->bind('notification_type', $notificationType);
        return $this->db->single();
    }

    /**
     * Membuat entri log notifikasi awal (status pending)
     */
    public function createLog(int $orderId, string $recipientType, string $recipientPhone, string $notificationType, string $status = 'pending'): int {
        $this->db->query("INSERT INTO notification_logs 
                          (order_id, recipient_type, recipient_phone, notification_type, message_status, attempt_count, last_attempt_at) 
                          VALUES 
                          (:order_id, :recipient_type, :recipient_phone, :notification_type, :message_status, 1, CURRENT_TIMESTAMP)");
        $this->db->bind('order_id', $orderId);
        $this->db->bind('recipient_type', $recipientType);
        $this->db->bind('recipient_phone', $recipientPhone);
        $this->db->bind('notification_type', $notificationType);
        $this->db->bind('message_status', $status);
        $this->db->execute();

        $this->db->query("SELECT LAST_INSERT_ID() as log_id");
        return (int)$this->db->single()['log_id'];
    }

    /**
     * Memperbarui status pengiriman notifikasi (sent / failed)
     */
    public function updateLogStatus(int $logId, string $status, ?string $responseCode = null, ?string $errorMessage = null, ?string $sentAt = null): bool {
        $query = "UPDATE notification_logs SET 
                    message_status = :status, 
                    provider_response_code = :response_code, 
                    error_message = :error_message, 
                    last_attempt_at = CURRENT_TIMESTAMP";

        if ($status === 'sent') {
            $query .= ", sent_at = IFNULL(:sent_at, CURRENT_TIMESTAMP)";
        }

        $query .= " WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('status', $status);
        $this->db->bind('response_code', $responseCode);
        $this->db->bind('error_message', $errorMessage);
        $this->db->bind('id', $logId);

        if ($status === 'sent') {
            $this->db->bind('sent_at', $sentAt);
        }

        return (bool)$this->db->execute();
    }

    /**
     * Menambah jumlah percobaan (attempt_count) saat retry
     */
    public function incrementAttempt(int $logId): bool {
        $this->db->query("UPDATE notification_logs 
                          SET attempt_count = attempt_count + 1, 
                              last_attempt_at = CURRENT_TIMESTAMP 
                          WHERE id = :id");
        $this->db->bind('id', $logId);
        return (bool)$this->db->execute();
    }

    /**
     * Mengambil log berdasarkan ID
     */
    public function getLogById(int $id) {
        $this->db->query("SELECT nl.*, o.order_number, o.shipping_name 
                          FROM notification_logs nl 
                          JOIN orders o ON nl.order_id = o.id 
                          WHERE nl.id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    /**
     * Mengambil riwayat notifikasi untuk pesanan tertentu
     */
    public function getLogsByOrderId(int $orderId): array {
        $this->db->query("SELECT * FROM notification_logs 
                          WHERE order_id = :order_id 
                          ORDER BY id DESC");
        $this->db->bind('order_id', $orderId);
        return $this->db->resultSet();
    }

    /**
     * Mengambil seluruh log notifikasi untuk dashboard Admin & Owner (dengan pagination dan filter)
     */
    public function getAllLogs(int $limit = 50, int $offset = 0, string $status = ''): array {
        $query = "SELECT nl.*, o.order_number, o.shipping_name, o.total_amount, o.status as order_status 
                  FROM notification_logs nl 
                  JOIN orders o ON nl.order_id = o.id ";

        if (!empty($status)) {
            $query .= " WHERE nl.message_status = :status ";
        }

        $query .= " ORDER BY nl.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->query($query);
        if (!empty($status)) {
            $this->db->bind('status', $status);
        }
        $this->db->bind('limit', $limit);
        $this->db->bind('offset', $offset);

        return $this->db->resultSet();
    }

    /**
     * Menghitung total entri log notifikasi
     */
    public function countLogs(string $status = ''): int {
        $query = "SELECT COUNT(*) as total FROM notification_logs";
        if (!empty($status)) {
            $query .= " WHERE message_status = :status";
        }
        $this->db->query($query);
        if (!empty($status)) {
            $this->db->bind('status', $status);
        }
        return (int)($this->db->single()['total'] ?? 0);
    }
}
