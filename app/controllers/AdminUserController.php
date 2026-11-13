<?php

class AdminUserController extends Controller {
    public function __construct() {
        // Hanya Admin dan Owner yang memiliki akses ke Manajemen Pengguna
        $this->requireRole(['admin', 'owner']);
    }

    /**
     * Menampilkan daftar seluruh pengguna dengan filter pencarian, role, dan status
     */
    public function index() {
        $data['judul'] = 'Manajemen Pengguna';
        $userModel = $this->model('User');

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $role = isset($_GET['role']) ? trim($_GET['role']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';

        $data['users'] = $userModel->getAllWithFilters($search, $role, $status);
        $data['stats'] = $userModel->getUserStats();
        $data['search'] = $search;
        $data['role'] = $role;
        $data['status'] = $status;
        $data['current_user_id'] = $_SESSION['user_id'] ?? null;
        $data['current_user_role'] = $_SESSION['user_role'] ?? null;
        $data['csrf_token'] = $this->generateCsrfToken();

        $this->view('layouts/admin', [
            'view' => 'admin_user/index',
            'data' => $data
        ]);
    }

    /**
     * Menampilkan formulir tambah pengguna baru
     */
    public function create() {
        $data['judul'] = 'Tambah Pengguna Baru';
        $data['csrf_token'] = $this->generateCsrfToken();
        $data['current_user_role'] = $_SESSION['user_role'] ?? null;
        $data['old_input'] = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);

        $this->view('layouts/admin', [
            'view' => 'admin_user/create',
            'data' => $data
        ]);
    }

    /**
     * Memproses penyimpanan data pengguna baru
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/AdminUserController");
            exit;
        }

        $this->validateCsrfToken($_POST['csrf_token'] ?? '');

        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $role = trim($_POST['role'] ?? 'customer');
        $status = trim($_POST['status'] ?? 'active');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        $userModel = $this->model('User');
        $errors = [];

        // Validasi Nama
        if (empty($name) || strlen($name) < 3) {
            $errors[] = 'Nama lengkap wajib diisi minimal 3 karakter.';
        }

        // Validasi Email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        } elseif ($userModel->isEmailRegistered($email)) {
            $errors[] = 'Alamat email ini sudah terdaftar.';
        }

        // Validasi Nomor WhatsApp/HP jika diisi
        if (!empty($phone)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($cleanPhone) < 9 || strlen($cleanPhone) > 16) {
                $errors[] = 'Nomor telepon/WhatsApp harus terdiri dari 9 hingga 16 digit angka.';
            } elseif ($userModel->isPhoneRegistered($cleanPhone)) {
                $errors[] = 'Nomor telepon/WhatsApp ini sudah digunakan oleh akun lain.';
            }
        }

        // Validasi Role
        $allowedRoles = ['customer', 'operator', 'admin'];
        if (($_SESSION['user_role'] ?? '') === 'owner') {
            $allowedRoles[] = 'owner';
        }
        if (!in_array($role, $allowedRoles)) {
            $errors[] = 'Pilihan role pengguna tidak valid.';
        }

        // Validasi Status
        if (!in_array($status, ['active', 'inactive'])) {
            $status = 'active';
        }

        // Validasi Password
        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Kata sandi wajib diisi minimal 6 karakter.';
        } elseif ($password !== $password_confirm) {
            $errors[] = 'Konfirmasi kata sandi tidak cocok.';
        }

        if (!empty($errors)) {
            $_SESSION['flash_msg'] = implode('<br>', $errors);
            $_SESSION['flash_type'] = 'danger';
            $_SESSION['old_input'] = $_POST;
            header("Location: " . BASEURL . "/AdminUserController/create");
            exit;
        }

        $data = [
            'name' => $name,
            'email' => $email,
            'phone' => !empty($phone) ? $phone : null,
            'role' => $role,
            'status' => $status,
            'password' => $password
        ];

        if ($userModel->createUser($data) > 0) {
            $_SESSION['flash_msg'] = 'Pengguna <strong>' . htmlspecialchars($name) . '</strong> berhasil ditambahkan.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_msg'] = 'Gagal menyimpan data pengguna baru ke database.';
            $_SESSION['flash_type'] = 'danger';
        }

        header("Location: " . BASEURL . "/AdminUserController");
        exit;
    }

    /**
     * Menampilkan formulir edit pengguna
     */
    public function edit($id) {
        $id = (int)$id;
        $userModel = $this->model('User');
        $user = $userModel->getUserById($id);

        if (!$user) {
            $_SESSION['flash_msg'] = 'Pengguna tidak ditemukan.';
            $_SESSION['flash_type'] = 'danger';
            header("Location: " . BASEURL . "/AdminUserController");
            exit;
        }

        // Proteksi: Admin biasa tidak boleh mengedit akun Owner
        $currentRole = $_SESSION['user_role'] ?? '';
        if ($user['role'] === 'owner' && $currentRole !== 'owner') {
            $_SESSION['flash_msg'] = 'Hanya Owner yang berhak mengedit data akun Owner.';
            $_SESSION['flash_type'] = 'warning';
            header("Location: " . BASEURL . "/AdminUserController");
            exit;
        }

        $data['judul'] = 'Edit Pengguna: ' . $user['name'];
        $data['user'] = $user;
        $data['csrf_token'] = $this->generateCsrfToken();
        $data['current_user_id'] = $_SESSION['user_id'] ?? null;
        $data['current_user_role'] = $currentRole;

        $this->view('layouts/admin', [
            'view' => 'admin_user/edit',
            'data' => $data
        ]);
    }

    /**
     * Memproses pembaharuan data pengguna
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/AdminUserController");
            exit;
        }

        $id = (int)$id;
        $this->validateCsrfToken($_POST['csrf_token'] ?? '');

        $userModel = $this->model('User');
        $existingUser = $userModel->getUserById($id);

        if (!$existingUser) {
            $_SESSION['flash_msg'] = 'Pengguna tidak ditemukan.';
            $_SESSION['flash_type'] = 'danger';
            header("Location: " . BASEURL . "/AdminUserController");
            exit;
        }

        $currentUserId = $_SESSION['user_id'] ?? null;
        $currentUserRole = $_SESSION['user_role'] ?? '';

        // Proteksi: Admin tidak boleh mengedit Owner
        if ($existingUser['role'] === 'owner' && $currentUserRole !== 'owner') {
            $_SESSION['flash_msg'] = 'Anda tidak memiliki wewenang untuk mengubah data akun Owner.';
            $_SESSION['flash_type'] = 'danger';
            header("Location: " . BASEURL . "/AdminUserController");
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $role = trim($_POST['role'] ?? $existingUser['role']);
        $status = trim($_POST['status'] ?? $existingUser['status']);
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        $errors = [];

        // Validasi Nama
        if (empty($name) || strlen($name) < 3) {
            $errors[] = 'Nama lengkap wajib diisi minimal 3 karakter.';
        }

        // Validasi Email unik
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        } elseif ($userModel->isEmailRegistered($email, $id)) {
            $errors[] = 'Alamat email ini sudah digunakan oleh akun lain.';
        }

        // Validasi Nomor WhatsApp unik
        if (!empty($phone)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($cleanPhone) < 9 || strlen($cleanPhone) > 16) {
                $errors[] = 'Nomor telepon/WhatsApp harus terdiri dari 9 hingga 16 digit angka.';
            } elseif ($userModel->isPhoneRegistered($cleanPhone, $id)) {
                $errors[] = 'Nomor telepon/WhatsApp ini sudah digunakan oleh akun lain.';
            }
        }

        // Proteksi diri: Jangan izinkan user mengubah role dirinya sendiri atau menonaktifkan dirinya sendiri
        if ($id == $currentUserId) {
            $role = $existingUser['role'];
            $status = 'active';
        }

        // Validasi Role yang diperbolehkan
        $allowedRoles = ['customer', 'operator', 'admin'];
        if ($currentUserRole === 'owner') {
            $allowedRoles[] = 'owner';
        }
        if (!in_array($role, $allowedRoles)) {
            $role = $existingUser['role'];
        }

        // Validasi Status
        if (!in_array($status, ['active', 'inactive'])) {
            $status = $existingUser['status'];
        }

        // Validasi Password Baru jika diisi
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $errors[] = 'Kata sandi baru minimal 6 karakter.';
            } elseif ($password !== $password_confirm) {
                $errors[] = 'Konfirmasi kata sandi baru tidak cocok.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['flash_msg'] = implode('<br>', $errors);
            $_SESSION['flash_type'] = 'danger';
            header("Location: " . BASEURL . "/AdminUserController/edit/" . $id);
            exit;
        }

        $data = [
            'name' => $name,
            'email' => $email,
            'phone' => !empty($phone) ? $phone : null,
            'role' => $role,
            'status' => $status
        ];

        if (!empty($password)) {
            $data['password'] = $password;
        }

        $userModel->updateUser($id, $data);
        $pwMsg = !empty($password) ? ' beserta kata sandi baru' : '';
        $_SESSION['flash_msg'] = 'Data pengguna <strong>' . htmlspecialchars($name) . '</strong>' . $pwMsg . ' berhasil diperbarui.';
        $_SESSION['flash_type'] = 'success';

        header("Location: " . BASEURL . "/AdminUserController");
        exit;
    }

    /**
     * Mengubah status aktif / nonaktif pengguna dengan cepat (1-Click Toggle)
     */
    public function toggleStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/AdminUserController");
            exit;
        }

        $id = (int)$id;
        $this->validateCsrfToken($_POST['csrf_token'] ?? '');

        $currentUserId = $_SESSION['user_id'] ?? null;
        if ($id == $currentUserId) {
            $_SESSION['flash_msg'] = 'Anda tidak dapat menonaktifkan akun Anda sendiri.';
            $_SESSION['flash_type'] = 'warning';
            header("Location: " . BASEURL . "/AdminUserController");
            exit;
        }

        $userModel = $this->model('User');
        $targetUser = $userModel->getUserById($id);

        if (!$targetUser) {
            $_SESSION['flash_msg'] = 'Pengguna tidak ditemukan.';
            $_SESSION['flash_type'] = 'danger';
            header("Location: " . BASEURL . "/AdminUserController");
            exit;
        }

        if ($targetUser['role'] === 'owner') {
            $_SESSION['flash_msg'] = 'Akun Owner tidak dapat dinonaktifkan.';
            $_SESSION['flash_type'] = 'warning';
            header("Location: " . BASEURL . "/AdminUserController");
            exit;
        }

        if ($userModel->toggleStatus($id) > 0) {
            $newStatus = ($targetUser['status'] === 'active') ? 'dinonaktifkan' : 'diaktifkan';
            $_SESSION['flash_msg'] = 'Status akun <strong>' . htmlspecialchars($targetUser['name']) . '</strong> berhasil ' . $newStatus . '.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_msg'] = 'Gagal mengubah status pengguna.';
            $_SESSION['flash_type'] = 'danger';
        }

        header("Location: " . BASEURL . "/AdminUserController");
        exit;
    }

    /**
     * Menghapus pengguna dengan proteksi relasi data
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/AdminUserController");
            exit;
        }

        $id = (int)$id;
        $this->validateCsrfToken($_POST['csrf_token'] ?? '');

        $currentUserId = $_SESSION['user_id'] ?? null;
        if ($id == $currentUserId) {
            $_SESSION['flash_msg'] = 'Anda tidak dapat menghapus akun Anda sendiri.';
            $_SESSION['flash_type'] = 'danger';
            header("Location: " . BASEURL . "/AdminUserController");
            exit;
        }

        $userModel = $this->model('User');
        $targetUser = $userModel->getUserById($id);

        if (!$targetUser) {
            $_SESSION['flash_msg'] = 'Pengguna tidak ditemukan.';
            $_SESSION['flash_type'] = 'danger';
            header("Location: " . BASEURL . "/AdminUserController");
            exit;
        }

        if ($targetUser['role'] === 'owner') {
            $_SESSION['flash_msg'] = 'Akun Owner tidak dapat dihapus.';
            $_SESSION['flash_type'] = 'danger';
            header("Location: " . BASEURL . "/AdminUserController");
            exit;
        }

        // Cek relasi pesanan
        if ($userModel->hasOrders($id)) {
            $_SESSION['flash_msg'] = 'Pengguna <strong>' . htmlspecialchars($targetUser['name']) . '</strong> tidak dapat dihapus karena memiliki riwayat pesanan (Orders). Anda dapat menonaktifkan status akun pengguna tersebut sebagai gantinya.';
            $_SESSION['flash_type'] = 'warning';
            header("Location: " . BASEURL . "/AdminUserController");
            exit;
        }

        if ($userModel->deleteUser($id) > 0) {
            $_SESSION['flash_msg'] = 'Pengguna <strong>' . htmlspecialchars($targetUser['name']) . '</strong> berhasil dihapus.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_msg'] = 'Gagal menghapus pengguna dari database.';
            $_SESSION['flash_type'] = 'danger';
        }

        header("Location: " . BASEURL . "/AdminUserController");
        exit;
    }
}
