<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/env.php';

class ExchangeRateService {
    private $db;
    private $apiKey;
    private $apiUrl;
    private $cacheTTL = 86400; // 24 hours
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->apiKey = Env::get('EXCHANGE_RATE_API_KEY');
        $this->apiUrl = Env::get('EXCHANGE_RATE_API_URL');
    }
    
    public function getRate($from = 'USD', $to = 'IDR') {
        // Check cache first
        $cached = $this->getCachedRate($from, $to);
        if ($cached !== null) {
            return $cached;
        }
        
        // Fetch from API
        $rate = $this->fetchFromApi($from, $to);
        
        // Cache the result
        if ($rate !== null) {
            $this->cacheRate($from, $to, $rate);
        }
        
        return $rate;
    }
    
    private function getCachedRate($from, $to) {
        $result = $this->db->fetchOne(
            "SELECT rate FROM exchange_rate_cache 
             WHERE base_currency = :from 
             AND target_currency = :to 
             AND expires_at > NOW()",
            ['from' => $from, 'to' => $to]
        );
        
        return $result ? (float)$result['rate'] : null;
    }
    
    private function fetchFromApi($from, $to) {
        if (empty($this->apiKey) || $this->apiKey === 'your_api_key_here') {
            // Return default rates for development
            return $this->getDefaultRate($from, $to);
        }
        
        $url = "{$this->apiUrl}/{$this->apiKey}/pair/{$from}/{$to}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['conversion_rate'])) {
                return (float)$data['conversion_rate'];
            }
        }
        
        // Fallback to default rates
        return $this->getDefaultRate($from, $to);
    }
    
    private function getDefaultRate($from, $to) {
        // Default rates for development (approximate values)
        $rates = [
            'USD-IDR' => 15750.00,
            'EUR-IDR' => 17250.00,
            'SGD-IDR' => 11680.00,
            'MYR-IDR' => 3520.00,
            'JPY-IDR' => 105.50,
            'CNY-IDR' => 2180.00,
            'IDR-IDR' => 1.00,
        ];
        
        $key = "$from-$to";
        return $rates[$key] ?? 1.00;
    }
    
    private function cacheRate($from, $to, $rate) {
        // Delete old cache
        $this->db->delete('exchange_rate_cache', 
            'base_currency = :from AND target_currency = :to',
            ['from' => $from, 'to' => $to]
        );
        
        // Insert new cache
        $this->db->insert('exchange_rate_cache', [
            'base_currency' => $from,
            'target_currency' => $to,
            'rate' => $rate,
            'expires_at' => date('Y-m-d H:i:s', time() + $this->cacheTTL)
        ]);
    }
    
    public function convert($amount, $from = 'USD', $to = 'IDR') {
        $rate = $this->getRate($from, $to);
        return $amount * $rate;
    }
    
    public function getSupportedCurrencies() {
        return [
            'IDR' => 'Indonesian Rupiah',
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'SGD' => 'Singapore Dollar',
            'MYR' => 'Malaysian Ringgit',
            'JPY' => 'Japanese Yen',
            'CNY' => 'Chinese Yuan'
        ];
    }
    
    public function cleanExpiredCache() {
        $this->db->delete('exchange_rate_cache', 'expires_at < NOW()');
    }
}