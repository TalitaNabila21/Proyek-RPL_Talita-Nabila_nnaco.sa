<?php

class CartController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function index() {
        $data['judul'] = 'Keranjang Belanja';
        $data['cart'] = $_SESSION['cart'];
        $data['csrf_token'] = $this->generateCsrfToken();
        
        $total = 0;
        foreach ($data['cart'] as $item) {
            $total += $item['subtotal'];
        }
        $data['total'] = $total;

        $this->view('layouts/main', [
            'view' => 'cart/index',
            'data' => $data
        ]);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token']);
            $product_id = $_POST['product_id'];
            $quantity = (int)$_POST['quantity'];

            if ($quantity <= 0) {
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }

            $product = $this->model('Product')->getById($product_id);
            
            if (!$product || $product['status'] != 'active') {
                $_SESSION['flash_msg'] = 'Produk tidak valid.';
                $_SESSION['flash_type'] = 'danger';
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }

            // Check if already in cart
            $current_qty = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id]['quantity'] : 0;
            $new_qty = $current_qty + $quantity;

            if ($new_qty > $product['stock']) {
                $_SESSION['flash_msg'] = 'Stok tidak mencukupi. Sisa stok: ' . $product['stock'];
                $_SESSION['flash_type'] = 'danger';
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }

            $_SESSION['cart'][$product_id] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'slug' => $product['slug'],
                'quantity' => $new_qty,
                'subtotal' => $new_qty * $product['price'],
                'stock' => $product['stock']
            ];

            $_SESSION['flash_msg'] = 'Produk berhasil ditambahkan ke keranjang.';
            $_SESSION['flash_type'] = 'success';
            header("Location: " . BASEURL . "/CartController");
            exit;
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token']);
            $product_id = $_POST['product_id'];
            $quantity = (int)$_POST['quantity'];

            if (isset($_SESSION['cart'][$product_id])) {
                $product = $this->model('Product')->getById($product_id);

                if ($quantity <= 0) {
                    unset($_SESSION['cart'][$product_id]);
                } elseif ($quantity > $product['stock']) {
                    $_SESSION['flash_msg'] = 'Stok tidak mencukupi. Maksimal: ' . $product['stock'];
                    $_SESSION['flash_type'] = 'danger';
                } else {
                    $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                    $_SESSION['cart'][$product_id]['subtotal'] = $quantity * $product['price'];
                }
            }
            header("Location: " . BASEURL . "/CartController");
            exit;
        }
    }

    public function remove($product_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrfToken($_POST['csrf_token']);
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
            }
        }
        header("Location: " . BASEURL . "/CartController");
        exit;
    }
}
