<?php

class SettingsController extends Controller {

    public function index() {
        $data['judul'] = 'Pengaturan Tampilan & Keamanan';
        $data['csrf_token'] = $this->generateCsrfToken();
        
        $userModel = $this->model('User');
        $currentUser = null;
        if (isset($_SESSION['user_id'])) {
            $currentUser = $userModel->getUserById($_SESSION['user_id']);
        }
        
        $data['currentUser'] = $currentUser;
        
        // Pilih layout admin atau layout utama customer
        $layout = (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'owner', 'operator'])) ? 'layouts/admin' : 'layouts/main';

        $this->view($layout, [
            'view' => 'settings/index',
            'data' => $data
        ]);
    }

    /**
     * Memproses penggantian password mandiri oleh pengguna yang sedang login
     */
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASEURL . "/settings");
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_msg'] = 'Silakan login terlebih dahulu untuk mengubah kata sandi.';
            $_SESSION['flash_type'] = 'warning';
            header("Location: " . BASEURL . "/auth/login");
            exit;
        }

        $this->validateCsrfToken($_POST['csrf_token'] ?? '');

        $userId = (int)$_SESSION['user_id'];
        $userModel = $this->model('User');
        $user = $userModel->getUserById($userId);

        if (!$user) {
            $_SESSION['flash_msg'] = 'Data akun pengguna tidak ditemukan.';
            $_SESSION['flash_type'] = 'danger';
            header("Location: " . BASEURL . "/settings");
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        // Validasi password lama
        if (empty($currentPassword)) {
            $errors[] = 'Kata sandi saat ini wajib diisi.';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $errors[] = 'Kata sandi saat ini yang Anda masukkan salah.';
        }

        // Validasi password baru
        if (empty($newPassword) || strlen($newPassword) < 6) {
            $errors[] = 'Kata sandi baru minimal 6 karakter.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Konfirmasi kata sandi baru tidak cocok.';
        } elseif (!empty($currentPassword) && $currentPassword === $newPassword) {
            $errors[] = 'Kata sandi baru tidak boleh sama dengan kata sandi saat ini.';
        }

        if (!empty($errors)) {
            $_SESSION['flash_msg'] = implode('<br>', $errors);
            $_SESSION['flash_type'] = 'danger';
            header("Location: " . BASEURL . "/settings#password-section");
            exit;
        }

        // Eksekusi update password
        $userModel->updatePassword($userId, $newPassword);
        
        $_SESSION['flash_msg'] = 'Kata sandi akun Anda berhasil diperbarui. Silakan gunakan kata sandi baru untuk login berikutnya.';
        $_SESSION['flash_type'] = 'success';

        header("Location: " . BASEURL . "/settings#password-section");
        exit;
    }
}
