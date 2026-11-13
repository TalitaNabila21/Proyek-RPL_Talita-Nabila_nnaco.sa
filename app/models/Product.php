<?php

class Product {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAll($search = '') {
        $query = "SELECT products.*, categories.name as category_name 
                  FROM products 
                  LEFT JOIN categories ON products.category_id = categories.id ";
        if ($search) {
            $query .= " WHERE products.name LIKE :search OR categories.name LIKE :search ";
        }
        $query .= " ORDER BY products.created_at DESC";

        $this->db->query($query);
        if ($search) {
            $this->db->bind('search', "%$search%");
        }
        return $this->db->resultSet();
    }

    public function getActive($category_id = null, $search = '') {
        $query = "SELECT products.*, categories.name as category_name 
                  FROM products 
                  LEFT JOIN categories ON products.category_id = categories.id 
                  WHERE products.status = 'active' AND categories.status = 'active'";
        
        if ($category_id) {
            $query .= " AND products.category_id = :category_id";
        }
        if ($search) {
            $query .= " AND products.name LIKE :search";
        }
        
        $query .= " ORDER BY products.created_at DESC";

        $this->db->query($query);
        
        if ($category_id) {
            $this->db->bind('category_id', $category_id);
        }
        if ($search) {
            $this->db->bind('search', "%$search%");
        }
        
        return $this->db->resultSet();
    }

    public function getFilteredActive($filters = []) {
        $query = "SELECT products.*, categories.name as category_name 
                  FROM products 
                  LEFT JOIN categories ON products.category_id = categories.id 
                  WHERE products.status = 'active' AND categories.status = 'active'";
        
        $params = [];
        
        if (!empty($filters['category'])) {
            $query .= " AND categories.slug = :category";
            $params['category'] = $filters['category'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (products.name LIKE :search OR products.description LIKE :search)";
            $params['search'] = "%" . $filters['search'] . "%";
        }

        if (!empty($filters['min_price'])) {
            $query .= " AND products.price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $query .= " AND products.price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }
        
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_low':
                    $query .= " ORDER BY products.price ASC";
                    break;
                case 'price_high':
                    $query .= " ORDER BY products.price DESC";
                    break;
                case 'name_asc':
                    $query .= " ORDER BY products.name ASC";
                    break;
                case 'newest':
                default:
                    $query .= " ORDER BY products.created_at DESC";
                    break;
            }
        } else {
            $query .= " ORDER BY products.created_at DESC";
        }

        if (isset($filters['limit']) && isset($filters['offset'])) {
            // Limits are handled directly in string to avoid binding issues in some PDO drivers
            $limit = (int)$filters['limit'];
            $offset = (int)$filters['offset'];
            $query .= " LIMIT $limit OFFSET $offset";
        }

        $this->db->query($query);
        
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        return $this->db->resultSet();
    }

    public function countFilteredActive($filters = []) {
        $query = "SELECT COUNT(*) as total
                  FROM products 
                  LEFT JOIN categories ON products.category_id = categories.id 
                  WHERE products.status = 'active' AND categories.status = 'active'";
        
        $params = [];
        
        if (!empty($filters['category'])) {
            $query .= " AND categories.slug = :category";
            $params['category'] = $filters['category'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (products.name LIKE :search OR products.description LIKE :search)";
            $params['search'] = "%" . $filters['search'] . "%";
        }

        if (!empty($filters['min_price'])) {
            $query .= " AND products.price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $query .= " AND products.price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }

        $this->db->query($query);
        
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $result = $this->db->single();
        return $result ? $result['total'] : 0;
    }

    public function getById($id) {
        $this->db->query("SELECT * FROM products WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getBySlug($slug) {
        $this->db->query("SELECT products.*, categories.name as category_name 
                  FROM products 
                  LEFT JOIN categories ON products.category_id = categories.id 
                  WHERE products.slug = :slug");
        $this->db->bind('slug', $slug);
        return $this->db->single();
    }

    public function insert($data) {
        $this->db->query("INSERT INTO products (category_id, name, slug, description, price, stock, image, status) 
                          VALUES (:category_id, :name, :slug, :description, :price, :stock, :image, :status)");
        $this->db->bind('category_id', $data['category_id']);
        $this->db->bind('name', $data['name']);
        $this->db->bind('slug', $data['slug']);
        $this->db->bind('description', $data['description']);
        $this->db->bind('price', $data['price']);
        $this->db->bind('stock', $data['stock']);
        $this->db->bind('image', $data['image']);
        $this->db->bind('status', $data['status']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function update($data) {
        $query = "UPDATE products SET 
                  category_id = :category_id, name = :name, slug = :slug, 
                  description = :description, price = :price, stock = :stock, status = :status";
        
        if (isset($data['image']) && $data['image'] != '') {
            $query .= ", image = :image";
        }
        $query .= " WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('id', $data['id']);
        $this->db->bind('category_id', $data['category_id']);
        $this->db->bind('name', $data['name']);
        $this->db->bind('slug', $data['slug']);
        $this->db->bind('description', $data['description']);
        $this->db->bind('price', $data['price']);
        $this->db->bind('stock', $data['stock']);
        $this->db->bind('status', $data['status']);
        
        if (isset($data['image']) && $data['image'] != '') {
            $this->db->bind('image', $data['image']);
        }

        $this->db->execute();
        return $this->db->rowCount();
    }

    public function delete($id) {
        $this->db->query("DELETE FROM products WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
