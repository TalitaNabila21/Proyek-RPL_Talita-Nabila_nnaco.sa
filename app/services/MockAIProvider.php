<?php

require_once 'AIServiceInterface.php';

class MockAIProvider implements AIServiceInterface {
    
    public function askQuestion(string $systemContext, string $userMessage): string {
        $userMessage = strtolower(trim($userMessage));
        $response = "";
        
        // Check if user is asking for recommendation based on keywords
        if (strpos($userMessage, 'budget') !== false || strpos($userMessage, 'harga') !== false || strpos($userMessage, 'cari') !== false || strpos($userMessage, 'hadiah') !== false) {
            
            $response = "Tentu, saya dapat membantu Anda. Berdasarkan database kami:\n\n";
            
            // Ekstrak informasi dari context string (sangat basic mock parsing)
            // Context format in AIChatController will be something like: 
            // "Products: [ID: 1, Name: Kalung Mutiara, Price: 250000, Category: kalung]"
            
            preg_match_all('/Name: (.*?), Price: (\d+)/', $systemContext, $matches);
            
            if (!empty($matches[1])) {
                $count = count($matches[1]);
                for ($i = 0; $i < $count; $i++) {
                    $name = $matches[1][$i];
                    $price = number_format($matches[2][$i], 0, ',', '.');
                    $response .= "- **$name** seharga Rp $price\n";
                }
                $response .= "\nApakah ada salah satu dari produk ini yang menarik bagi Anda? Anda dapat langsung mencarinya di halaman toko kami.";
            } else {
                $response .= "Maaf, saat ini saya tidak dapat menemukan produk yang sesuai.";
            }
            
        } elseif (strpos($userMessage, 'stok') !== false || strpos($userMessage, 'sisa') !== false) {
            $response = "Untuk informasi stok, kami memiliki beberapa produk unggulan saat ini. Berdasarkan data kami:\n\n" . $systemContext;
        } else {
            $response = "Halo! Saya adalah AI Assistant (Mock) nnaco_sa. Saya siap membantu Anda memilih produk yang tepat berdasarkan harga, kategori, dan kebutuhan Anda. Apa yang sedang Anda cari hari ini?";
        }

        return $response;
    }
}
