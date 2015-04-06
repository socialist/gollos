<?php

/**
 * @author Skripnichenko Sergey <skripnichenko.s.a@gmail.com>
 * @copyright 2015
 */

class Gollos {
    
    private $apiKey;
    private $secretKey;
    private $root = 'https://gollosapi.com/api/';
    
    public function __construct($apiKey, $secretKey)
    {
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
    }
    
    public function getOrder($id)
    {
        return $this->call('orders?id='.$id, '', 'GET');
    }
    
    public function getProduct($id)
    {
        return $this->call('products?id='.$id, '', 'GET');
    }
    
    public function getCustomer($id)
    {
        $customers = $this->call('customers?id=' . $id, '', 'GET');
        return $customers[0];
    }
    
    private function getHash()
    {
        return hash_hmac('sha1', $this->apiKey, $this->secretKey);
    }
    
    private function call($func, $params, $type)
    {        
        $headers = array(
            'Content-Type: application/json',
            'x-go-apiKey: ' . $this->apiKey,
            'x-go-hmac: ' . $this->getHash(),
        );
        
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_USERAGENT, 'PHP/Client');
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 600);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        if($type == 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        }
        
        curl_setopt($curl, CURLOPT_URL, $this->root . $func);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        
        $responce = json_decode(curl_exec($curl), true);
        
        if(curl_error($curl)) {
            throw new Exception("API call to Gollos failed: " . curl_error($curl));
        }
        if(isset($responce['status']) && $responce['status'] == 401) {
            throw new Exception("API call to Gollos failed: <b>{$responce['message']}</b>");
        }
        return $responce;
    }
}
