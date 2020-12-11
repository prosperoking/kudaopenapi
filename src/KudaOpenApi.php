<?php
namespace Prosperoking\KudaOpenApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use phpseclib\Crypt\Random;

class KudaOpenApi {
    private string $privateKey;
    private string $publicKey;
    private string $clientKey;
    private Encrypter $crypter;
    private string $baseUri;
    /**
     * @param string $privateKey 
     * @param mixed $string 
     * @param mixed $publicKey 
     * @return void 
     */
    public function __construct(string $privateKey, string $publicKey, string $clientKey, $baseUri='https://sandbox.kudabank.com/v1')
    {
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;    
        $this->clientKey = $clientKey;
        $this->crypter = new Encrypter;
        $this->baseUri = $baseUri;
    }


    public function getAccountInfo($payload,$requestRef=null) 
    {
        return $this->makeRequest(ServiceTypes::NAME_ENQUIRY, $payload,$requestRef);
    }

    public function getBankList($requestRef=null)
    {
        return $this->makeRequest(ServiceTypes::BANK_LIST, [],$requestRef);
    }

    private function encryptPassword($password)
    {
        return $this->crypter->encryptRSA( $password, $this->publicKey);
    }

    private function encryptPayload(array $payload, $password,$salt)
    {
        return $this->crypter->encryptAES(json_encode($payload), $password,$salt);
    }

    private function dencryptPayload(Response $response,$salt)
    {
        $content = $response->getBody()->getContents();
        ['password'=>$password, 'data'=>$data] = json_decode($content,true);
        return json_decode($this->crypter->decryptAES($data, $this->decryptPassword($password),$salt));
    }

    private function decryptPassword($password)
    {
        return $this->crypter->decryptRSA($password, $this->privateKey);
    }


    private function makeRequest(string $action ,array $payload,$requestRef=null)
    {
        $client = new Client([
            'base_uri'=>$this->baseUri
        ]);
        $salt = 'randomsalt';//substr(bin2hex(Random::string(16)),0,16);
        $enctyped_password = $this->encryptPassword($password = $this->clientKey.'-'. substr(bin2hex(Random::string(8)),0,5));
        try {
            /**
             * @var Response $response
             */
            $response =  $client->post('',[
                'json'=>[
                    'data'=>$this->encryptPayload([
                        'serviceType'=>$action,
                        'requestRef'=>$requestRef??bin2hex(random_bytes(10)),
                        'data'=>$payload
                    ], $password,$salt)
                ],
                'headers'=>['password'=>$enctyped_password]
            ]) ;

            return $this->dencryptPayload($response,$salt);
        }
        catch(ClientException $exception) {
            $response = $exception->getResponse();
            return ['Status'=>false, 'Message'=>json_decode($response->getBody()->getContents(),true),];
        }
        catch (\Throwable $th) {
            return ['Status'=>false, 'Message'=>$th->getMessage()];
        }
        
    }

    private function errors($status_code)
    {
        return [
            "400"=>"Exception occured",
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
        ][$status_code];
    }

}