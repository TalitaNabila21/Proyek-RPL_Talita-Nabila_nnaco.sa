<?php

class User {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getUserByEmail($email) {
        $this->db->query('SELECT * FROM users WHERE email = :email OR (role = "operator" AND :alias IN ("operator", "operator1", "operator 1", "operator1@nnaco.com")) LIMIT 1');
        $this->db->bind('email', $email);
        $this->db->bind('alias', strtolower($email));
        return $this->db->single();
    }

    public function getUserByPhone($phone) {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($cleanPhone)) return null;

        // Mendukung pencarian format 628xxx maupun 08xxx
        $altPhone = '';
        if (strpos($cleanPhone, '62') === 0) {
            $altPhone = '0' . substr($cleanPhone, 2);
        } elseif (strpos($cleanPhone, '0') === 0) {
            $altPhone = '62' . substr($cleanPhone, 1);
        }

        $this->db->query('SELECT * FROM users WHERE phone = :p1 OR phone = :p2 LIMIT 1');
        $this->db->bind('p1', $cleanPhone);
        $this->db->bind('p2', !empty($altPhone) ? $altPhone : $cleanPhone);
        return $this->db->single();
    }

    public function isPhoneRegistered($phone, $excludeUserId = null) {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $altPhone = (strpos($cleanPhone, '62') === 0) ? ('0' . substr($cleanPhone, 2)) : ((strpos($cleanPhone, '0') === 0) ? ('62' . substr($cleanPhone, 1)) : '');

        $sql = 'SELECT id FROM users WHERE (phone = :p1 OR phone = :p2)';
        if ($excludeUserId) {
            $sql .= ' AND id != :excludeId';
        }
        $sql .= ' LIMIT 1';

        $this->db->query($sql);
        $this->db->bind('p1', $cleanPhone);
        $this->db->bind('p2', !empty($altPhone) ? $altPhone : $cleanPhone);
        if ($excludeUserId) {
            $this->db->bind('excludeId', $excludeUserId);
        }
        return (bool)$this->db->single();
    }

    public function registerUser($data) {
        $phone = !empty($data['phone']) ? preg_replace('/[^0-9]/', '', $data['phone']) : null;
        if ($phone && strpos($phone, '0') === 0) {
            $phone = '62' . substr($phone, 1);
        }

        $this->db->query('INSERT INTO users (name, email, phone, password, role) VALUES (:name, :email, :phone, :password, :role)');
        
        $this->db->bind('name', $data['name']);
        $this->db->bind('email', $data['email']);
        $this->db->bind('phone', $phone);
        $this->db->bind('password', $data['password']);
        $this->db->bind('role', 'customer'); // Hardcode role customer for public registration

        $this->db->execute();

        return $this->db->rowCount();
    }

    public function registerUserByPhone($phone, $name = null) {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if ($cleanPhone && strpos($cleanPhone, '0') === 0) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }
        $formattedName = !empty($name) ? $name : ('User ' . substr($cleanPhone, -4));
        $generatedEmail = 'wa_' . $cleanPhone . '@nnaco.com';
        $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);

        $this->db->query('INSERT INTO users (name, email, phone, password, role, status) VALUES (:name, :email, :phone, :password, "customer", "active")');
        $this->db->bind('name', $formattedName);
        $this->db->bind('email', $generatedEmail);
        $this->db->bind('phone', $cleanPhone);
        $this->db->bind('password', $randomPassword);
        $this->db->execute();

        return $this->getUserByPhone($cleanPhone);
    }

    public function getAllAdmins() {
        $this->db->query('SELECT id, name, email, role, status, created_at FROM users WHERE role = "admin"');
        return $this->db->resultSet();
    }

    public function createAdmin($data) {
        $this->db->query('INSERT INTO users (name, email, password, role, status) VALUES (:name, :email, :password, "admin", "active")');
        $this->db->bind('name', $data['name']);
        $this->db->bind('email', $data['email']);
        $this->db->bind('password', password_hash($data['password'], PASSWORD_BCRYPT));
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function toggleStatus($id) {
        // First get current status
        $this->db->query('SELECT status FROM users WHERE id = :id');
        $this->db->bind('id', $id);
        $user = $this->db->single();
        
        if ($user) {
            $newStatus = ($user['status'] == 'active') ? 'inactive' : 'active';
            $this->db->query('UPDATE users SET status = :status WHERE id = :id');
            $this->db->bind('status', $newStatus);
            $this->db->bind('id', $id);
            $this->db->execute();
            return $this->db->rowCount();
        }
        return 0;
    }

    public function getUserById($id) {
        $this->db->query('SELECT id, name, email, phone, password, role, status, created_at FROM users WHERE id = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function updatePassword($id, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->db->query('UPDATE users SET password = :password WHERE id = :id');
        $this->db->bind('password', $hash);
        $this->db->bind('id', (int)$id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getUserStats() {
        $this->db->query('SELECT COUNT(*) as total FROM users');
        $total = (int)($this->db->single()['total'] ?? 0);

        // Pengguna yang bisa login (status active)
        $this->db->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
        $canLoginTotal = (int)($this->db->single()['total'] ?? 0);

        // Pengguna yang dinonaktifkan (tidak bisa login)
        $this->db->query("SELECT COUNT(*) as total FROM users WHERE status = 'inactive'");
        $cannotLoginTotal = (int)($this->db->single()['total'] ?? 0);

        // Rincian pengguna yang bisa login per role
        $this->db->query("SELECT role, COUNT(*) as count FROM users WHERE status = 'active' GROUP BY role");
        $activeRoleRows = $this->db->resultSet();
        $canLoginByRole = [
            'customer' => 0,
            'admin' => 0,
            'operator' => 0,
            'owner' => 0
        ];
        foreach ($activeRoleRows as $r) {
            $canLoginByRole[$r['role']] = (int)$r['count'];
        }

        // Rincian seluruh pengguna per role
        $this->db->query('SELECT role, COUNT(*) as count FROM users GROUP BY role');
        $roleRows = $this->db->resultSet();
        $roles = [
            'customer' => 0,
            'admin' => 0,
            'operator' => 0,
            'owner' => 0
        ];
        foreach ($roleRows as $r) {
            $roles[$r['role']] = (int)$r['count'];
        }

        return [
            'total' => $total,
            'can_login_total' => $canLoginTotal,
            'cannot_login_total' => $cannotLoginTotal,
            'can_login_by_role' => $canLoginByRole,
            'by_role' => $roles
        ];
    }

    public function getTotalCount() {
        $this->db->query('SELECT COUNT(*) as total FROM users');
        return (int)($this->db->single()['total'] ?? 0);
    }

    public function getAllUsers() {
        $this->db->query('SELECT id, name, email, phone, role, status, created_at FROM users ORDER BY id ASC');
        return $this->db->resultSet();
    }

    public function isEmailRegistered($email, $excludeUserId = null) {
        $sql = 'SELECT id FROM users WHERE email = :email';
        if ($excludeUserId) {
            $sql .= ' AND id != :excludeId';
        }
        $sql .= ' LIMIT 1';

        $this->db->query($sql);
        $this->db->bind('email', strtolower(trim($email)));
        if ($excludeUserId) {
            $this->db->bind('excludeId', $excludeUserId);
        }
        return (bool)$this->db->single();
    }

    public function getAllWithFilters($search = '', $role = '', $status = '') {
        $sql = 'SELECT id, name, email, phone, role, status, created_at, updated_at FROM users WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $sql .= ' AND (name LIKE :search OR email LIKE :search OR phone LIKE :search)';
            $params['search'] = '%' . trim($search) . '%';
        }

        if (!empty($role) && in_array($role, ['customer', 'admin', 'operator', 'owner'])) {
            $sql .= ' AND role = :role';
            $params['role'] = $role;
        }

        if (!empty($status) && in_array($status, ['active', 'inactive'])) {
            $sql .= ' AND status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY id DESC';

        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        return $this->db->resultSet();
    }

    public function createUser($data) {
        $phone = !empty($data['phone']) ? preg_replace('/[^0-9]/', '', $data['phone']) : null;
        if ($phone && strpos($phone, '0') === 0) {
            $phone = '62' . substr($phone, 1);
        }

        $this->db->query('INSERT INTO users (name, email, phone, password, role, status) VALUES (:name, :email, :phone, :password, :role, :status)');
        $this->db->bind('name', trim($data['name']));
        $this->db->bind('email', strtolower(trim($data['email'])));
        $this->db->bind('phone', $phone);
        $this->db->bind('password', password_hash($data['password'], PASSWORD_BCRYPT));
        $this->db->bind('role', $data['role'] ?? 'customer');
        $this->db->bind('status', $data['status'] ?? 'active');

        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateUser($id, $data) {
        $phone = !empty($data['phone']) ? preg_replace('/[^0-9]/', '', $data['phone']) : null;
        if ($phone && strpos($phone, '0') === 0) {
            $phone = '62' . substr($phone, 1);
        }

        $hasPassword = !empty($data['password']);

        if ($hasPassword) {
            $sql = 'UPDATE users SET name = :name, email = :email, phone = :phone, password = :password, role = :role, status = :status WHERE id = :id';
        } else {
            $sql = 'UPDATE users SET name = :name, email = :email, phone = :phone, role = :role, status = :status WHERE id = :id';
        }

        $this->db->query($sql);
        $this->db->bind('id', $id);
        $this->db->bind('name', trim($data['name']));
        $this->db->bind('email', strtolower(trim($data['email'])));
        $this->db->bind('phone', $phone);
        $this->db->bind('role', $data['role']);
        $this->db->bind('status', $data['status']);
        if ($hasPassword) {
            $this->db->bind('password', password_hash($data['password'], PASSWORD_BCRYPT));
        }

        $this->db->execute();
        return $this->db->rowCount();
    }

    public function hasOrders($userId) {
        $this->db->query('SELECT COUNT(*) as total FROM orders WHERE user_id = :user_id');
        $this->db->bind('user_id', $userId);
        $res = $this->db->single();
        return ((int)($res['total'] ?? 0)) > 0;
    }

    public function deleteUser($id) {
        // Hapus relasi OTP jika ada
        try {
            $this->db->query('DELETE FROM whatsapp_otps WHERE user_id = :user_id');
            $this->db->bind('user_id', $id);
            $this->db->execute();
        } catch (Exception $e) {
            // Ignore if whatsapp_otps table doesn't have FK
        }

        $this->db->query('DELETE FROM users WHERE id = :id');
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}

