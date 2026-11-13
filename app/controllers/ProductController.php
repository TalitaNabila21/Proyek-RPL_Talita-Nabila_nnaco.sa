<?php

require_once '../app/services/ProductRecommendationService.php';

class ProductController extends Controller {

    public function index() {
        $data['judul'] = 'Katalog Produk';
        
        // Extract filters from GET request
        $filters = [
            'category' => isset($_GET['category']) ? $_GET['category'] : null,
            'search' => isset($_GET['search']) ? $_GET['search'] : '',
            'min_price' => isset($_GET['min_price']) ? $_GET['min_price'] : '',
            'max_price' => isset($_GET['max_price']) ? $_GET['max_price'] : '',
            'sort' => isset($_GET['sort']) ? $_GET['sort'] : 'newest',
        ];

        // Pagination setup
        $limit = 12; // Items per page
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $limit;

        $filters['limit'] = $limit;
        $filters['offset'] = $offset;

        $productModel = $this->model('Product');
        
        $data['products'] = $productModel->getFilteredActive($filters);
        $totalItems = $productModel->countFilteredActive($filters);
        
        $data['categories'] = $this->model('Category')->getActive();
        
        $data['filters'] = $filters; // Pass back to view to keep form state
        $data['pagination'] = [
            'total_items' => $totalItems,
            'total_pages' => ceil($totalItems / $limit),
            'current_page' => $page,
            'limit' => $limit
        ];
        
        $data['csrf_token'] = $this->generateCsrfToken();
        
        $this->view('layouts/main', [
            'view' => 'products/index',
            'data' => $data
        ]);
    }

    public function detail($slug) {
        $data['product'] = $this->model('Product')->getBySlug($slug);
        
        if (!$data['product']) {
            // Not found
            header("Location: " . BASEURL . "/ProductController");
            exit;
        }

        $data['judul'] = $data['product']['name'];
        $data['csrf_token'] = $this->generateCsrfToken();

        $recommender = new ProductRecommendationService();
        $data['recommendations'] = $recommender->getRecommendations([
            'current_product_id' => $data['product']['id'],
            'category_id' => $data['product']['category_id'],
            'price' => $data['product']['price'],
            'limit' => 4
        ]);

        $this->view('layouts/main', [
            'view' => 'products/detail',
            'data' => $data
        ]);
    }
}
