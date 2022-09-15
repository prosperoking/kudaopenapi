<?php
namespace Prosperoking\KudaOpenApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use phpseclib\Crypt\Random;
use Prosperoking\KudaOpenApi\Contracts\IAuthCacheDriver;
use Prosperoking\KudaOpenApi\Exceptions\AuthCacheStoreException;
use Prosperoking\KudaOpenApi\Exceptions\KudaAuthenticationException;

class KudaOpenApiV2 {
    private string $email;
    private string $apiKey;
    private string $baseUri;
    private IAuthCacheDriver $cacheDriver;
    /**
     * @param string $username
     * @param mixed $string 
     * @param mixed $apikey
     * @return void 
     */
    public function __construct(string $username, string $apikey, ?IAuthCacheDriver $cacheDriver, $baseUri='https://sandbox.kudabank.com/v1')
    {
        $this->email = $username;
        $this->apiKey = $apikey;
        $this->baseUri = $baseUri;
        $this->cacheDriver =  $cacheDriver ?? new DefaultCacheDriver();
    }

    public function setCacheDriver(IAuthCacheDriver $driver)
    {
        $this->cacheDriver = $driver;
    }

    /**
     * @param string $environment
     * @return bool
     */
    public function setEnvironment(string $environment): bool
    {
        $urls = [
            'test'=>'https://kuda-openapi-uat.kudabank.com/v2/',
            'live'=>'https://kuda-openapi.kuda.com/v2/',
        ];
        if(!array_key_exists($environment, $urls)) return false;
        $this->baseUri = $urls[$environment];
        return true;
    }

    public function setBaseUrl(string $url): bool
    {
        // Todo validate it's valid url
        $this->baseUri = $url;
        return true;
    }

    public static function initLive(string $username, string $apikey, ?IAuthCacheDriver $cacheDriver = null): KudaOpenApiV2
    {
        $client = new KudaOpenApiV2($username, $apikey, $cacheDriver);
        $client->setEnvironment('live');
        return $client;
    }

    public static function initTest(string $username, string $apikey, ?IAuthCacheDriver $cacheDriver = null): KudaOpenApiV2
    {
        $client = new KudaOpenApiV2($username, $apikey, $cacheDriver);
        $client->setEnvironment('test');
        return $client;
    }


    public function getAccountInfo($payload,$requestRef=null) 
    {
        return $this->makeRequest(ServiceTypes::NAME_ENQUIRY, $payload,$requestRef);
    }

    public function getBankList($requestRef=null)
    {
        return $this->makeRequest(ServiceTypes::BANK_LIST, [],$requestRef);
    }

    public function getMainAccountBalance()
    {
        return $this->makeRequest(ServiceTypes::ADMIN_RETRIEVE_MAIN_ACCOUNT_BALANCE,[]);
    }

    public function kudaAccountTransfer($payload, $requestRef=null)
    {
        return $this->makeRequest(ServiceTypes::SINGLE_FUND_TRANSFER,$payload,$requestRef);
    }

    /**
     * @throws GuzzleException
     * @throws KudaAuthenticationException
     */
    private function getToken(): string
    {
        $token = $this->cacheDriver->getAuthToken();

        if($token !== null) return $token;

        $client = new Client([
            'base_uri'=>$this->baseUri
        ]);

        $response = $client->post('Account/GetToken',[
            'json'=>[
                'email'=>"$this->email",
                'apiKey'=>"$this->apiKey",
            ]
        ]);

        if($response->getStatusCode() !== 200)
            throw new KudaAuthenticationException("Authentication failed: ".$response->getBody()->getContents());
        $token = $response->getBody()->getContents();
        $this->cacheDriver->setAuthToken($token);
        return $token;
    }

    /**
     * @throws GuzzleException
     * @throws KudaAuthenticationException
     */
    public function makeRequest(string $action , array $payload, $requestRef=null)
    {
        $token = $this->getToken();
        $client = new Client([
            'base_uri'=>$this->baseUri
        ]);
        try {
            /**
             * @var Response $response
             */
            $response =  $client->post('',[
                'json'=>[
                    "data"=>json_encode([
                        'serviceType'=>$action,
                        'requestRef'=>$requestRef??bin2hex(random_bytes(10)),
                        'data'=>$payload
                    ])
                ],
                'headers'=>[
                    'Authorization'=> "Bearer ".$token
                ]
            ]) ;
            ["data"=>$data] = json_decode($response->getBody()->getContents(), true);
            return json_decode($data);
        }
        catch(ClientException $exception) {
            $response = $exception->getResponse();
            return (object) [
                'Status'=>false,
                'error'=> $this->errors($response->getStatusCode()),
                'Message'=>json_decode($response->getBody()->getContents(),true),
                ];
        }
        catch (\Throwable $th) {
            return (object) ['Status'=>false, 'Message'=>$th->getMessage()];
        }
        
    }

    private function errors($status_code)
    {
        return [
            "400"=>"Exception occurred",
            "401"=>"Authentication failure",
            "403" => "Forbidden",
            "404"=>"Resource not found",
            "405"=>"Method Not Allowed",
            "409"=>"Conflict",
            "412"=>"Precondition Failed",
            "413"=>"Request Entity Too Large",
            "500"=>"Internal Server Error",
            "501"=>"Not Implemented",
            "503"=>"Service Unavailable"
        ][$status_code] ?? 'Unknown Error';
    }

}