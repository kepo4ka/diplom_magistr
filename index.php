<?php

include 'init.php';
$proccess_id = substr(md5(microtime()), 0, 5);

$proxy_list = ProxyDB::getList();
updateAuthAccount();

$elibCurl = new ElibraryCurl();

$org_id = 5051;

if (!empty($_REQUEST['org_id'])) {
    $org_id = preg_replace('/[^\d]+/m', '', $_REQUEST['org_id']);
}

$pagenum = 1;
$pagenum_end = 0;

if (!empty($_REQUEST['pagenum'])) {
    $pagenum = preg_replace('/[^\d]+/m', '', $_REQUEST['pagenum']);
}

if (!empty($_REQUEST['pagenum_end'])) {
    $pagenum_end = preg_replace('/[^\d]+/m', '', $_REQUEST['pagenum_end']);
}


if (!empty($_REQUEST['nsleep'])) {
    $sleep_mode = false;
}

if (empty($_REQUEST['start'])) {
    echo "Недостаточно прав.";
    exit;
}


$query_count = 1;
$start = microtime(true);
arrayLog(array('Work Started'), 'Start', 'start');
ProxyDB::update();

echoVarDumpPre(ElibraryCurl::getAllOrganisations());


//Organisation::parseOrganisationPublications();





