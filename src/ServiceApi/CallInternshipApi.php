<?php

namespace App\ServiceApi;

use Symfony\Contracts\HttpClient\HttpClientInterface;

//Create class to retrieve data from an API
class CallInternshipApi
{
    private $client;
    protected $apiUrl;
    protected $apiKey;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apiUrl = $_ENV['API_URL'];
        $this->apiKey = $_ENV['API_KEY'];
    }
    //getDataApi is a function to retrieve orders and contacts from an API and return array  of data
    public function getDataApi(string $verbe): array | string
    {
        $response = $this->client->request(
            'GET',
            $this->apiUrl.$verbe,
            [
                'headers' => [
                    'x-api-key' => $this->apiKey
                ]
            ]
        );

        $statusCode = $response->getStatusCode();

        return $this->getReponseStatus($statusCode, $response);
    }
    //getItemsApi is a function to retrieve Sales Order Lines from an API and return results data in array
    public function getItemsApi(): array | string
    {
        $getProductsApi = $this->getDataApi("orders");
        if(is_array($getProductsApi)){
            $SalesOrderLines = [];
            for ($i=0;$i<count($getProductsApi);$i++) {
                $lines = $getProductsApi[$i]["SalesOrderLines"]["results"];
                for ($j=0;$j<count($lines);$j++) {
                    $SalesOrderLines[] = $lines[$j];
                }
            }
            return $SalesOrderLines;
        }
        return $getProductsApi;
    }
    //getReponseStatus is a function to return a status message from an API
    public function getReponseStatus($statusCode, $response): array | string
    {
        if($statusCode === 404){
            return "404 page not found";
        }
        if($statusCode === 500){
            return "500 Internal Server Error";
        }
        $data = $response->toArray();
        if($statusCode === 200){
            return $data["results"];
        }
        if($statusCode == 401)
            return $data["status"].' '.$data["message"];
        return '';

    }
}


