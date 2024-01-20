<?php

function getConfigVariable($variable){
    $configPath = 'config.conf';
    if(!file_exists($configPath)){
        die("No se ha encontrado archivo de configuración");
    }

    $read = parse_ini_file($configPath);

    if(!isset($read[$variable])){
        die("No se ha encontrado la variable");
    }

    return $read[$variable];
}
 function generateHash($data){
    $pepper = getConfigVariable("pepper");
    $pepperedPwd = hash_hmac("sha512", $data, $pepper);
    return substr(password_hash($pepperedPwd, PASSWORD_DEFAULT), 7);
}

function prepareHashToUpload($hasToTurn){

    $first = substr($hasToTurn, 0, 10);
    $rest = substr($hasToTurn, 10);
    $reverse = strrev($rest . $first);
    return $reverse;

}

function prepareHashToUse($hasToUse){

    $reverse = strrev($hasToUse);
    $first_10 = substr($reverse, -10);
    $rest = substr($reverse, 0, -10);
    $original = $first_10 . $rest;
    return $original;
}

function checkHash($hash, $wtCheck){
    $hashReady = "$2y$10$". prepareHashToUse($hash);
    $pepper = getConfigVariable("pepper");
    $pepperedPwd = hash_hmac("sha512", $wtCheck, $pepper);
    return password_verify($pepperedPwd, $hashReady);
}

function generateRSA(){
    $keys = openssl_pkey_new(array("private_key_bits" => 2048, "private_key_type" => OPENSSL_KEYTYPE_RSA));
    openssl_pkey_export($keys, $privateKey);
    $publicKeyDetails = openssl_pkey_get_details($keys);
    $publicKey = $publicKeyDetails['key'];
    return array("private"=>$privateKey, "public"=>$publicKey);
}

function RSAencoding($rsaPkey, $dataToEncode){
    $encrypted_data = '';
    openssl_public_encrypt($dataToEncode, $encrypted_data, $rsaPkey, OPENSSL_PKCS1_OAEP_PADDING);
    return base64_encode($encrypted_data);
}

function RSAdecode($rsaPkey, $dataToDecode){
    $decrypted_data = '';
    $dataToDecode = base64_decode($dataToDecode);
    openssl_private_decrypt($dataToDecode, $decrypted_data, $rsaPkey, OPENSSL_PKCS1_OAEP_PADDING);
    return $decrypted_data;
}



function AESEncoding($dataToEncode, $keyEncoding){
    $iv_len = openssl_cipher_iv_length("aes-256-gcm");
    $iVector = openssl_random_pseudo_bytes($iv_len);
    $tag = '';
    $tagLength = 16;
    $encodedData = openssl_encrypt($dataToEncode, "aes-256-gcm", $keyEncoding, OPENSSL_RAW_DATA, $iVector, $tag, "", $tagLength);
    //$textToEncode = $iVector . $tag . $encodedData;
    return base64_encode($iVector.$encodedData.$tag);
}

function AESDecode($dataToDecode, $keyDecoding){
    $stringCombined = base64_decode($dataToDecode);
    $iv_len = openssl_cipher_iv_length("aes-256-gcm");
    $tagLength = 16;
    $iVector = substr($stringCombined, 0, $iv_len);
    $cipherText = substr($stringCombined, $iv_len, -$tagLength);
    $tag = substr($stringCombined, -$tagLength);
    return openssl_decrypt($cipherText, 'aes-256-gcm', $keyDecoding, OPENSSL_RAW_DATA, $iVector, $tag);
}

function VSign($data, $pvk){
    $signature = 0;
    openssl_sign($data, $signature, $pvk, OPENSSL_ALGO_SHA512);
    return base64_encode($signature);
}

function VSignCheck($data, $signature, $pk){
    $signature = base64_decode($signature);
    return openssl_verify($data, $signature, $pk, OPENSSL_ALGO_SHA512);
}

?>