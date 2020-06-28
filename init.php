<?php
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'lib/simple_html_dom.php';

// https://github.com/colshrapnel/safemysql/blob/master/safemysql.class.php
require_once 'vendor/kepo4ka/helper/php/lib/safemysql.class.php';
require_once 'functions.php';
require_once 'class/ProxyDB.php';
require_once 'class/ElibraryParser.php';
require_once 'class/ElibraryCurl.php';
require_once 'class/ElibraryDB.php';
require_once 'class/Organisation.php';
require_once 'class/Publication.php';
require_once 'class/Author.php';
require_once 'class/Keyword.php';
require_once 'class/ElibraryDB.php';

require_once __DIR__ . '/class/covid/Covid.php';
require_once __DIR__ . '/class/nosu/Nosu.php';


$user = 'root';
$pass = '';
$db_name = 'elibrary';
$host = 'localhost';

$db = new SafeMysql(array('host' => $host, 'user' => $user, 'pass' => $pass, 'db' => $db_name, 'charset' => 'utf8'));
$simple_dom = new simple_html_dom();


?>