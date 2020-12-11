<?php

namespace Prosperoking\KudaOpenApi;

use Exception;
use phpseclib\Crypt\AES;
use phpseclib\Crypt\Base;
use phpseclib\Crypt\Random;
use phpseclib\Crypt\RSA;

define('CRYPT_RSA_PKCS15_COMPAT', true);
class Encrypter {
    public static function encryptRSA(string $text,$key)
    {
        $rsa = new RSA;
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        if($rsa->loadKey($key)) return $rsa->encrypt($text);
        if($rsa->loadKey(file_get_contents($key))) return base64_encode($rsa->encrypt($text));
        throw new Exception("Unable to load key");
    }

    public static function decryptRSA(string $text,$key)
    {
        $rsa = new RSA;
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        if($rsa->loadKey($key)) return $rsa->decrypt(base64_decode($text));
        if($rsa->loadKey(file_get_contents($key))) return $rsa->decrypt(base64_decode($text));
        throw new Exception("Unable to load key");
    }

    public static function encryptAES($text,$password,$salt)
    {
        $derivedKey = openssl_pbkdf2($password,$salt,256,1000);
        $aes = new AES;

        $aes->setPassword($password,'pbkdf2', 'sha1',$salt,1000,256);
        $aes->setIV(substr($derivedKey,0,16));
        $result = $aes->encrypt($text);
        return base64_encode($result);
    }

    public static function decryptAES($text,$password,$salt)
    {
        $derivedKey = openssl_pbkdf2($password,$salt,256,1000);
        $aes = new AES;
        $aes->setPassword($password,'pbkdf2', 'sha1',$salt,1000,256);
        $aes->setIV(substr($derivedKey,0,16));
        $result = $aes->decrypt(base64_decode($text));
        return $result;
    }
}