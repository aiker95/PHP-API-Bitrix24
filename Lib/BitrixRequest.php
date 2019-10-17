<?php

namespace App\BitrixClasses\Lib;

use GuzzleHttp\Client;
use GuzzleHttp;
use File;
use Config;

class BitrixRequest
{
    /**
     * 
     * @var string Base url
     */
    protected $url = 'https://minzdravmo.bitrix24.ru';
    
    /**
     * HTTP client.
     *
     * @var Client
     */
    protected $client;
    
    private $token;
    
    /**
     * @param Client|null $client
     */
    public function __construct(Client $client = null, $token = null){
        
        $this->client = $client ?: new Client(['base_uri' => $this->url]);
        $token ? $this->token = $token : $this->token = Config::get('bitrix.bitrix_token');
        
    }
   
   
    /**
     * Функция обновления задач
     * 
     * @param array $tasks
     * @return string|\Illuminate\Http\JsonResponse
     */
    public function sendRequest($endpoint, BitrixData $data = null)
    {            
             try {
                
                $data ? $json = ['json'=>$data] : $json = null;
                
                $response = $this->client->post('/rest/'.$this->token.'/'.$endpoint.'.json?', $json);
              
                $result =  json_decode($response->getBody()->getContents(), true);
                
                return  $result;
             }
             catch (GuzzleHttp\Exception\ClientException $e) {
                 
                $response = $e->getResponse();
                $result =  json_decode($response->getBody()->getContents(), true);
               
                return $result;   
             }
     }
}