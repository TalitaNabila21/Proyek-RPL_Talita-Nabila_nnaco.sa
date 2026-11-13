<?php

class OwnerAdminController extends Controller {
    public function __construct() {
        $this->requireRole(['owner']);
    }

    public function index() {
        $data['judul'] = 'Manajemen Admin';
        $data['admins'] = $this->model('User')->getAllAdmins();
        $data['csrf_token'] = $this->generateCsrfToken();
        
        $this->view('layouts/admin', [
            'view' => 'owner/admin_manage',
            'data' => $data
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token']);
            
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            
            if ($this->model('User')->getUserByEmail($email)) {
                $_SESSION['flash_msg'] = 'Email sudah terdaftar!';
                $_SESSION['flash_type'] = 'danger';
                header("Location: " . BASEURL . "/OwnerAdminController");
                exit;
            }

            $data = [
                'name' => htmlspecialchars($_POST['name']),
                'email' => $email,
                'password' => $_POST['password']
            ];

            if ($this->model('User')->createAdmin($data) > 0) {
                $_SESSION['flash_msg'] = 'Akun Admin berhasil dibuat!';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_msg'] = 'Gagal membuat akun Admin!';
                $_SESSION['flash_type'] = 'danger';
            }
            header("Location: " . BASEURL . "/OwnerAdminController");
            exit;
        }
    }

    public function toggle($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token']);
            
            if ($this->model('User')->toggleStatus($id) > 0) {
                $_SESSION['flash_msg'] = 'Status Admin berhasil diubah!';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_msg'] = 'Gagal mengubah status!';
                $_SESSION['flash_type'] = 'danger';
            }
            header("Location: " . BASEURL . "/OwnerAdminController");
            exit;
        }
    }

    public function updatePassword($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token'] ?? '');
            $id = (int)$id;
            
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            if (empty($password) || strlen($password) < 6) {
                $_SESSION['flash_msg'] = 'Kata sandi baru minimal 6 karakter.';
                $_SESSION['flash_type'] = 'danger';
            } elseif ($password !== $password_confirm) {
                $_SESSION['flash_msg'] = 'Konfirmasi kata sandi baru tidak cocok.';
                $_SESSION['flash_type'] = 'danger';
            } else {
                $userModel = $this->model('User');
                $adminUser = $userModel->getUserById($id);
                if ($adminUser && $adminUser['role'] === 'admin') {
                    $userModel->updatePassword($id, $password);
                    $_SESSION['flash_msg'] = 'Kata sandi untuk Admin <strong>' . htmlspecialchars($adminUser['name']) . '</strong> berhasil diperbarui!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_msg'] = 'Akun admin tidak valid.';
                    $_SESSION['flash_type'] = 'danger';
                }
            }
            header("Location: " . BASEURL . "/OwnerAdminController");
            exit;
        }
    }
}
