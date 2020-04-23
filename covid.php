<?php

include __DIR__ . '/init.php';

$user = 'root';
$pass = '';
$db_name = 'coronovirus';
$host = 'localhost';

$db = new SafeMysql(array('host' => $host, 'user' => $user, 'pass' => $pass, 'db' => $db_name, 'charset' => 'utf8'));

$project_url = 'http://localhost/';

$proxy_list = ProxyDB::getList();

ProxyDB::update();



getCitiesFromAirportsDB();




