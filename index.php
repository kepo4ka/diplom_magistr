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

if (!empty($_REQUEST['pagenum'])) {
    $pagenum = preg_replace('/[^\d]+/m', '', $_REQUEST['pagenum']);
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


//$z = array();
//$z['proxy'] = array();
//$z['proxy']['full'] = '185.204.208.78:8080';
//$z['proxy']['type'] = CURLPROXY_HTTP;
//$z['proxy']['auth'] = ':';
//
//$res = fetch('https://elibrary.ru/item.asp?id=27517846', $z);
////$res = fetch('https://google.ru', $z);
//echoVarDumpPre($res);


$filter = array();
$filter['publicationid'] = 39204055;
$filter['authorid'] = 1001122;

$organisation = Organisation::get($org_id, true);

while (true) {
    $org_publications = Organisation::parsePublicationsPart($org_id, $pagenum);

    arrayLog('', 'Полученные статьи организации на странице ' . $pagenum);

    $pagenum++;

    if (empty($org_publications)) {
        break;
    }

    foreach ($org_publications as $org_publication) {
        $publication = Publication::get($org_publication, true);

        if (empty($publication['title'])) {
            continue;
        }

        arrayLog($publication['title'], 'Работа со статьей ' . $publication['id']);

        if (!empty($publication['authors'])) {
            foreach ($publication['authors'] as $pub_author) {
                $pagenum1 = 1;
                while (!empty(Author::parsePublicationsPart($pub_author, $pagenum1))) {
                    $pagenum1++;
                }
            }
        }

        if (!empty($publication['keywords_full'])) {
            foreach ($publication['keywords_full'] as $keyword) {
                Keyword::save($keyword);
            }
        }

        if (!empty($publication['refs'])) {
            foreach ($publication['refs'] as $pub_ref) {
                $ref = Publication::get($pub_ref, true);
            }
        }
    }

}


arrayLog($query_count, 'Количество запросов');
arrayLog('Информация об организация <strong>' . $organisation['name'] . '</strong> Добавлена', 'Информация об организации');
arrayLog('Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.', 'Время выполнения скрипта');

exit;




