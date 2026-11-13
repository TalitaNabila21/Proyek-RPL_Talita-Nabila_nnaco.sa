<?php

class AdminCategoryController extends Controller {
    public function __construct() {
        $this->requireRole(['admin']);
    }

    public function index() {
        $data['judul'] = 'Kelola Kategori';
        $data['categories'] = $this->model('Category')->getAll();
        $data['csrf_token'] = $this->generateCsrfToken();
        
        $this->view('layouts/admin', [
            'view' => 'admin_category/index',
            'data' => $data
        ]);
    }

    public function create() {
        $data['judul'] = 'Tambah Kategori';
        $data['csrf_token'] = $this->generateCsrfToken();
        
        $this->view('layouts/admin', [
            'view' => 'admin_category/create',
            'data' => $data
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token']);
            
            $name = htmlspecialchars($_POST['name']);
            $slug = strtolower(str_replace(' ', '-', $name));
            // Hapus karakter non-alphanumeric dari slug
            $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug);

            $data = [
                'name' => $name,
                'slug' => $slug,
                'description' => htmlspecialchars($_POST['description']),
                'status' => $_POST['status']
            ];

            if ($this->model('Category')->insert($data) > 0) {
                $_SESSION['flash_msg'] = 'Kategori berhasil ditambahkan.';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_msg'] = 'Gagal menambah kategori.';
                $_SESSION['flash_type'] = 'danger';
            }
            header("Location: " . BASEURL . "/AdminCategoryController");
            exit;
        }
    }

    public function edit($id) {
        $data['judul'] = 'Edit Kategori';
        $data['category'] = $this->model('Category')->getById($id);
        $data['csrf_token'] = $this->generateCsrfToken();

        if (!$data['category']) {
            header("Location: " . BASEURL . "/AdminCategoryController");
            exit;
        }

        $this->view('layouts/admin', [
            'view' => 'admin_category/edit',
            'data' => $data
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token']);
            
            $name = htmlspecialchars($_POST['name']);
            $slug = strtolower(str_replace(' ', '-', $name));
            $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug);

            $data = [
                'id' => $_POST['id'],
                'name' => $name,
                'slug' => $slug,
                'description' => htmlspecialchars($_POST['description']),
                'status' => $_POST['status']
            ];

            if ($this->model('Category')->update($data) > 0) {
                $_SESSION['flash_msg'] = 'Kategori berhasil diupdate.';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_msg'] = 'Tidak ada perubahan atau gagal update.';
                $_SESSION['flash_type'] = 'warning';
            }
            header("Location: " . BASEURL . "/AdminCategoryController");
            exit;
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token']);
            if ($this->model('Category')->delete($id) > 0) {
            $_SESSION['flash_msg'] = 'Kategori berhasil dihapus.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_msg'] = 'Gagal menghapus kategori.';
            $_SESSION['flash_type'] = 'danger';
        }
        header("Location: " . BASEURL . "/AdminCategoryController");
        exit;
        }
    }
}
