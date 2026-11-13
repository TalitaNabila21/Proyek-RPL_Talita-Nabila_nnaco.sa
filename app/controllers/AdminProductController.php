<?php

class AdminProductController extends Controller {
    public function __construct() {
        $this->requireRole(['admin']);
    }

    public function index() {
        $data['judul'] = 'Kelola Produk';
        
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $data['products'] = $this->model('Product')->getAll($search);
        $data['search'] = $search;
        $data['csrf_token'] = $this->generateCsrfToken();
        
        $this->view('layouts/admin', [
            'view' => 'admin_product/index',
            'data' => $data
        ]);
    }

    public function create() {
        $data['judul'] = 'Tambah Produk';
        $data['categories'] = $this->model('Category')->getAll(); // Get all categories for dropdown
        $data['csrf_token'] = $this->generateCsrfToken();
        $data['old_input'] = isset($_SESSION['old_input']) ? $_SESSION['old_input'] : [];
        unset($_SESSION['old_input']);
        
        $this->view('layouts/admin', [
            'view' => 'admin_product/create',
            'data' => $data
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token']);
            
            // Server-side Validation
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
            $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : -1;
            $status = isset($_POST['status']) ? $_POST['status'] : '';

            $errors = [];
            
            if (empty($name) || strlen($name) < 3) {
                $errors[] = "Nama produk wajib diisi minimal 3 karakter.";
            }
            if (empty($category_id)) {
                $errors[] = "Kategori wajib dipilih.";
            }
            if (empty($description)) {
                $errors[] = "Deskripsi wajib diisi.";
            }
            if ($price <= 0) {
                $errors[] = "Harga wajib berupa angka dan lebih besar dari 0.";
            }
            if ($stock < 0) {
                $errors[] = "Stok wajib berupa integer dan tidak boleh negatif.";
            }
            if (!in_array($status, ['active', 'inactive'])) {
                $errors[] = "Status tidak valid.";
            }
            if (!isset($_FILES['image']) || $_FILES['image']['error'] != 0) {
                $errors[] = "Foto produk wajib diupload.";
            }

            if (!empty($errors)) {
                $_SESSION['flash_msg'] = implode("<br>", $errors);
                $_SESSION['flash_type'] = 'danger';
                $_SESSION['old_input'] = $_POST;
                header("Location: " . BASEURL . "/AdminProductController/create");
                exit;
            }

            $slug = strtolower(str_replace(' ', '-', htmlspecialchars($name))) . '-' . time();
            $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug);
            
            $imageName = '';
            // Handle File Upload safely
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
            $allowed_mime = ['image/jpeg', 'image/png', 'image/webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $tmp_name = $_FILES['image']['tmp_name'];

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);
            
            if (in_array($ext, $allowed_ext) && in_array($mime, $allowed_mime) && $_FILES['image']['size'] < 2000000) { // 2MB Max
                $imageName = $slug . '.' . $ext;
                $uploadPath = '../public/img/products/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                if (!move_uploaded_file($tmp_name, $uploadPath . $imageName)) {
                    $_SESSION['flash_msg'] = 'Gagal memindahkan file yang diupload.';
                    $_SESSION['flash_type'] = 'danger';
                    $_SESSION['old_input'] = $_POST;
                    header("Location: " . BASEURL . "/AdminProductController/create");
                    exit;
                }
            } else {
                $_SESSION['flash_msg'] = 'Format gambar tidak didukung, mime type tidak valid, atau ukuran terlalu besar (Max 2MB).';
                $_SESSION['flash_type'] = 'danger';
                $_SESSION['old_input'] = $_POST;
                header("Location: " . BASEURL . "/AdminProductController/create");
                exit;
            }

            $data = [
                'category_id' => $category_id,
                'name' => htmlspecialchars($name),
                'slug' => $slug,
                'description' => htmlspecialchars($description),
                'price' => $price,
                'stock' => $stock,
                'status' => $status,
                'image' => $imageName
            ];

            if ($this->model('Product')->insert($data) > 0) {
                $_SESSION['flash_msg'] = 'Produk berhasil ditambahkan.';
                $_SESSION['flash_type'] = 'success';
            } else {
                // Hapus file yang sudah terupload karena database gagal
                if (file_exists('../public/img/products/' . $imageName)) {
                    unlink('../public/img/products/' . $imageName);
                }
                $_SESSION['flash_msg'] = 'Gagal menyimpan data produk ke database.';
                $_SESSION['flash_type'] = 'danger';
                $_SESSION['old_input'] = $_POST;
                header("Location: " . BASEURL . "/AdminProductController/create");
                exit;
            }
            header("Location: " . BASEURL . "/AdminProductController");
            exit;
        }
    }

    public function edit($id) {
        $data['judul'] = 'Edit Produk';
        $data['product'] = $this->model('Product')->getById($id);
        $data['categories'] = $this->model('Category')->getAll();
        $data['csrf_token'] = $this->generateCsrfToken();

        if (!$data['product']) {
            header("Location: " . BASEURL . "/AdminProductController");
            exit;
        }

        $this->view('layouts/admin', [
            'view' => 'admin_product/edit',
            'data' => $data
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token']);
            
            $id = $_POST['id'];
            $name = htmlspecialchars($_POST['name']);
            $slug = strtolower(str_replace(' ', '-', $name)) . '-' . time();
            $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug);

            $imageName = '';
            // Handle File Upload if exists
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $filename = $_FILES['image']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed) && $_FILES['image']['size'] < 2000000) {
                    $imageName = $slug . '.' . $ext;
                    $uploadPath = '../public/img/products/';
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }
                    move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath . $imageName);
                    
                    // Delete old image
                    $oldProduct = $this->model('Product')->getById($id);
                    if ($oldProduct && $oldProduct['image']) {
                        $oldPath = $uploadPath . $oldProduct['image'];
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                }
            }

            $data = [
                'id' => $id,
                'category_id' => $_POST['category_id'],
                'name' => $name,
                'slug' => $slug,
                'description' => htmlspecialchars($_POST['description']),
                'price' => (float) $_POST['price'],
                'stock' => (int) $_POST['stock'],
                'status' => $_POST['status']
            ];

            if ($imageName != '') {
                $data['image'] = $imageName;
            }

            if ($this->model('Product')->update($data) > 0) {
                $_SESSION['flash_msg'] = 'Produk berhasil diupdate.';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_msg'] = 'Tidak ada perubahan atau gagal update.';
                $_SESSION['flash_type'] = 'warning';
            }
            header("Location: " . BASEURL . "/AdminProductController");
            exit;
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token']);
            
            // Delete old image
            $product = $this->model('Product')->getById($id);
            if ($product && $product['image']) {
                $oldPath = '../public/img/products/' . $product['image'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            if ($this->model('Product')->delete($id) > 0) {
                $_SESSION['flash_msg'] = 'Produk berhasil dihapus.';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_msg'] = 'Gagal menghapus produk.';
                $_SESSION['flash_type'] = 'danger';
            }
            header("Location: " . BASEURL . "/AdminProductController");
            exit;
        }
    }
}
