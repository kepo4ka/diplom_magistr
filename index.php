<?php

include 'init.php';


$proccess_id = substr(md5(microtime()), 0, 5);

$elibCurl = new ElibraryCurl();
$elibDB = new ElibraryDB();

$org_id = 1273;

if (!empty($_REQUEST['org_id'])) {
    $org_id = preg_replace('/[^\d]+/m', '', $_REQUEST['org_id']);
}

if (empty($_REQUEST['start'])) {
    echo "Недостаточно прав.";
    exit;
}


$filter = array();
$filter['publicationid'] =
$filter['publicationid'] =

$res = checkExistMulti('publications_to_authors', $filter);
echoVarDumpPre($res);


$query_count = 1;

$list = array();

$start = microtime(true);

ProxyDB::update();

arrayLog('Work Started', 'Start', 'start');
arrayLog($def_proxy_info['full'], 'First Proxy');


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
                $elibDB->relationPublicationPublication($ref, $publication['id']);
            }

            $elibDB->relationOrganisationPublication($publication['id'], $organisation['id']);


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
                    $elibDB->relationAuthorPublication($publication['id'], $author['id']);

                    foreach ($author['organisations'] as $organisation_id) {
                        if (!checkExist('organisations', $organisation_id)) {
                            $organisation1 = $elibCurl->getOrganisationInfo($organisation_id);

                            if (empty($organisation1)) {
                                continue;
                            }

                            $elibDB->saveOrganisation($organisation1);
                            $elibDB->relationOrganisationAuthor($author['id'], $organisation1['id']);
                        }
                    }
                }
            }
        }


    } else {
        break;
    }
    $k++;
}

arrayLog($query_count, 'Количество запросов');
arrayLog('Информация об организация <strong>' . $organisation['name'] . '</strong> Добавлена', 'Информация об организации');
arrayLog('Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.', 'Время выполнения скрипта');

exit;




