<?php
 function generateHash($data){
    return substr(password_hash($data, PASSWORD_BCRYPT), 7);
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
    return password_verify($wtCheck, $hashReady);
}

function generateRSA(){
    $keys = openssl_pkey_new(array("private_key_bits" => 2048, "private_key_type" => OPENSSL_KEYTYPE_RSA));
    openssl_pkey_export($keys, $privateKey);
    $publicKeyDetails = openssl_pkey_get_details($keys);
    $publicKey = $publicKeyDetails['key'];
    $pre = '/-----BEGIN PUBLIC KEY-----/';
    $post = '/-----END PUBLIC KEY-----/';
    $publicKey = preg_replace($pre,'',$publicKey);
    $publicKey = preg_replace($post,'',$publicKey);
    $pre = '/-----BEGIN PRIVATE KEY-----/';
    $post = '/-----END PRIVATE KEY-----/';
    $privateKey = preg_replace($pre,'',$privateKey);
    $privateKey = preg_replace($post,'',$privateKey);
    return array("private"=>$privateKey, "public"=>$publicKey);
}

function AESEncoding($dataToEncode, $keyEncoding){
    $iVector = openssl_random_pseudo_bytes(16);
    $encodedData = openssl_encrypt($dataToEncode,"aes-256-ctr", $keyEncoding, OPENSSL_RAW_DATA, $iVector);
    $textToEncode = $iVector . $encodedData;
    return base64_encode($textToEncode);
}

function AESDecode($dataToDecode, $keyDecoding){
    $stringCombined = base64_decode($dataToDecode);
    $iVector = substr($stringCombined,0,16);
    $cipherText = substr($stringCombined,16);
    return openssl_decrypt($cipherText, 'aes-256-ctr', $keyDecoding, OPENSSL_RAW_DATA, $iVector);
}

?>