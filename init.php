<?php

require_once 'config.php';
require_once 'lib/simple_html_dom.php';

// https://github.com/colshrapnel/safemysql/blob/master/safemysql.class.php
require_once 'lib/safemysql.class.php';
require_once 'functions.php';
require_once 'class/ProxyDB.php';
require_once 'class/ElibraryParser.php';
require_once 'class/ElibraryCurl.php';
require_once 'class/Publication.php';
require_once 'class/Author.php';


$user = 'root';
$pass = '';
$db_name = 'elibrary';
$host = 'localhost';

$db = new SafeMysql(array('host' => $host, 'user' => $user, 'pass' => $pass, 'db' => $db_name, 'charset' => 'utf8'));
$simple_dom = new simple_html_dom();


?>