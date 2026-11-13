<?php

class ProductRecommendationService {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Get product recommendations based on context.
     * 
     * @param array $context Context array which can contain:
     *                       - current_product_id (int)
     *                       - category_id (int)
     *                       - price (float)
     *                       - limit (int, default 4)
     * @return array Array of recommended products
     */
    public function getRecommendations(array $context = []): array {
        $limit = isset($context['limit']) ? (int)$context['limit'] : 4;
        $currentProductId = isset($context['current_product_id']) ? (int)$context['current_product_id'] : 0;
        
        // Base query: Only active products with stock > 0
        $query = "SELECT products.*, categories.name as category_name 
                  FROM products 
                  LEFT JOIN categories ON products.category_id = categories.id 
                  WHERE products.status = 'active' AND categories.status = 'active' AND products.stock > 0";
                  
        // Exclude current product if provided
        if ($currentProductId > 0) {
            $query .= " AND products.id != :current_product_id";
        }

        // To implement a fallback scoring system purely in SQL:
        // We order by relevance. 
        // Relevance = (Same category ? 100 : 0) - (Price difference penalty)
        $orderClauses = [];
        $binds = [];

        if ($currentProductId > 0) {
            $binds['current_product_id'] = $currentProductId;
        }

        if (isset($context['category_id']) && $context['category_id'] > 0) {
            $query .= " AND products.category_id = :category_id";
            $binds['category_id'] = $context['category_id'];
        }

        // Simple Randomizer if no specific context provided (like Homepage)
        if (empty($context['category_id']) && empty($context['price'])) {
            $query .= " ORDER BY RAND() LIMIT :limit";
        } else {
            // Order by closest price if price is provided (Fallback mechanism)
            if (isset($context['price'])) {
                $query .= " ORDER BY ABS(products.price - :target_price) ASC LIMIT :limit";
                $binds['target_price'] = $context['price'];
            } else {
                $query .= " ORDER BY RAND() LIMIT :limit";
            }
        }

        $this->db->query($query);

        // Bind parameters
        foreach ($binds as $key => $value) {
            $this->db->bind($key, $value);
        }
        $this->db->bind('limit', $limit);

        $results = $this->db->resultSet();

        // If we didn't get enough recommendations and we filtered by category,
        // let's fill the rest with random active products
        if (count($results) < $limit) {
            $needed = $limit - count($results);
            
            $excludeIds = array_column($results, 'id');
            if ($currentProductId > 0) {
                $excludeIds[] = $currentProductId;
            }
            
            $fallbackQuery = "SELECT products.*, categories.name as category_name 
                              FROM products 
                              LEFT JOIN categories ON products.category_id = categories.id 
                              WHERE products.status = 'active' AND categories.status = 'active' AND products.stock > 0";
                              
            if (!empty($excludeIds)) {
                $idList = implode(',', $excludeIds);
                // Safe here because they are known integer IDs from previous query
                $fallbackQuery .= " AND products.id NOT IN ($idList)";
            }
            
            $fallbackQuery .= " ORDER BY RAND() LIMIT :needed";
            
            $this->db->query($fallbackQuery);
            $this->db->bind('needed', $needed);
            $fallbackResults = $this->db->resultSet();
            
            $results = array_merge($results, $fallbackResults);
        }

        return $results;
    }
}
