<?php

require_once '../app/services/ProductRecommendationService.php';

class HomeController extends Controller {
    public function index() {
        $data['judul'] = 'Home';
        
        $recommender = new ProductRecommendationService();
        $data['recommendations'] = $recommender->getRecommendations([
            'limit' => 4
        ]);

        // Memanggil layout utama yang di dalamnya meng-include component dan view spesifik
        $this->view('layouts/main', [
            'view' => 'home/index',
            'data' => $data
        ]);
    }
}
