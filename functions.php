<?php

function fetch($url, $z = null)
{
    $result = '';
    try {
        $ch = curl_init();

        if (!empty($z['params'])) {
            $url .= '?' . http_build_query($z['params']);
        }

        $useragent = isset($z['useragent']) ? $z['useragent'] : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:10.0.2) Gecko/20100101 Firefox/10.0.2';

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//        curl_setopt($ch, CURLOPT_POST, isset($z['params']));
//
//        if (isset($z['post'])) {
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $z['params']);
//        }

        if (isset($z['refer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $z['refer']);
        }

        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (isset($z['timeout']) ? $z['timeout'] : 5));
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'elibrary.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'elibrary.txt');

        $result = curl_exec($ch);
        curl_close($ch);
    } catch (Exception $ex) {

    }

    return $result;
}


function echoVarDumpPre($var)
{
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    exit;
}

