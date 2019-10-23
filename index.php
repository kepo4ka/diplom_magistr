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

        if (empty($publication)) {
            continue;
        }

        arrayLog($publication['title'], 'Работа со статьей ' . $publication['id']);

        Organisation::savePublication($org_id, $publication['id']);


        foreach ($publication['authors'] as $pub_author) {
            $author = Author::get($pub_author, true);

            if (empty($author)) {
                continue;
            }

//            arrayLog($author['fio'], 'Работа со автором статьи ' . $author['id']);

            Author::savePublication($pub_author, $org_publication);

            $pagenum1 = 1;
            while (!empty(Author::parsePublicationsPart($pub_author, $pagenum1))) {
                $pagenum1++;
            }

            foreach ($author['organisations'] as $author_organisation) {
                $org = Organisation::get($author_organisation);

                if (empty($org)) {
                    continue;
                }

//                arrayLog($org['name'], 'Работа со организацией автора ' . $org['id']);

                Author::saveOrganisation($pub_author, $author_organisation);
            }
        }

        foreach ($publication['keywords_full'] as $keyword) {
            if (empty($keyword)) {
                continue;
            }

//            arrayLog($keyword['name'], 'Работа со ключом статьи ' . $keyword['id']);

            Keyword::save($keyword);
            Publication::saveKeyword($org_publication, $keyword['id']);
        }


        foreach ($publication['refs'] as $pub_ref) {
            $ref = Publication::get($pub_ref, true);

            if (empty($ref)) {
                continue;
            }

//            arrayLog($ref, 'Работа со ссылочной статьёй ' . $ref['id']);

            Publication::saveRef($org_publication, $pub_ref);

            foreach ($ref['authors'] as $ref_author) {
                $aauthor = Author::get($ref_author);

                if (empty($aauthor)) {
                    continue;
                }

//                arrayLog($aauthor['fio'], 'Работа со автором ссылочной статьи ' . $aauthor['id']);

                Publication::saveAuthor($pub_ref, $ref_author);
            }


            if (!empty($publication['keywords_full'])) {
                foreach ($ref['keywords_full'] as $ref_key) {
                    if (empty($ref_key)) {
                        continue;
                    }

//                arrayLog($ref_key['name'], 'Работа со ключевым словом ссылочной статьи ' . $ref_key['id']);

                    Keyword::save($ref_key);
                    Publication::saveKeyword($pub_ref, $ref_key['id']);
                }
            }

//            foreach ($ref['publications'] as $ref_publication) {
//                Publication::get($ref_publication);
//                Publication::saveRef($pub_ref, $ref_publication);
//            }

        }
    }

}


/*

$organisation = $elibCurl->getOrganisationInfo($org_id);
$elibDB->saveOrganisation($organisation);
$k = 1;

while (true) {
    $org_publications = $elibCurl->getOrgPublications($org_id, $k);

    if (!empty($org_publications)) {
        foreach ($org_publications as $publ_id) {
            if (!checkExist('publications', $publ_id)) {
                $publication = $elibCurl->getPublication($publ_id);

                if (empty($publication)) {
                    continue;
                }

                $elibDB->savePublication($publication);
            } else {
                $publication = $elibDB->getPublication($publ_id);
            }

            foreach ($publication['refs'] as $ref) {
                $elibDB->saveRelationPublicationPublication($ref, $publication['id']);
            }

            $elibDB->saveRelationOrganisationPublication($publication['id'], $organisation['id']);


            if (empty($publication['authors'])) {
                continue;
            }

            foreach ($publication['authors'] as $author_id) {

                if (!checkExist('authors', $author_id)) {
                    $author = $elibCurl->getAuthorInfo($author_id);

                    if (empty($author)) {
                        continue;
                    }

                    $elibDB->saveAuthor($author);
                } else {
                    $publication = Author::get($author_id);
                }
                $elibDB->saveRelationAuthorPublication($publication['id'], $author['id']);

                foreach ($author['organisations'] as $organisation_id) {
                    if (!checkExist('organisations', $organisation_id)) {
                        $organisation1 = $elibCurl->getOrganisationInfo($organisation_id);

                        if (empty($organisation1)) {
                            continue;
                        }

                        $elibDB->saveOrganisation($organisation1);
                        $elibDB->saveRelationOrganisationAuthor($author['id'], $organisation1['id']);
                    }
                }
            }
        }
    } else {
        break;
    }
    $k++;
}

*/

arrayLog($query_count, 'Количество запросов');
arrayLog('Информация об организация <strong>' . $organisation['name'] . '</strong> Добавлена', 'Информация об организации');
arrayLog('Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.', 'Время выполнения скрипта');

exit;




