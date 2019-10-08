<?php

$cookiePath = dirname(__FILE__) . '/cookie.txt';
$cookiePath1 = dirname(__FILE__) . '\cookie.txt';
$elibrary_config = [
    'login' => 'kapipoh',
    'password' => 'qwerty123',
    'base_url' => 'https://elibrary.ru/'
];

$proxy_active = true;
$proxy_url = 'http://127.0.0.1:1000/';

$def_proxy_info = array();

$proxy_list = array();

$query_count = 0;

$delay_min = 4000000;
$delay_max = 8000000;

$log=array();
?>