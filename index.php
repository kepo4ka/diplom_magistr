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

if (empty($_REQUEST['start'])) {
    echo "Недостаточно прав.";
    exit;
}


$query_count = 1;
$start = microtime(true);
arrayLog('Work Started', 'Start', 'start');
ProxyDB::update();


$filter = array();
$filter['publicationid'] = 39204055;
$filter['authorid'] = 1001122;


echoVarDumpPre($elibCurl::getKeywordPublications());

$organisation = Organisation::get($org_id);

$paganum = 1;


while (true) {
    $org_publications = Organisation::parsePublicationsPart($org_id, $paganum);
    $paganum++;

    Organisation::savePublications($org_id, $org_publications);

    foreach ($org_publications as $org_publication) {
        $publication = Publication::get($org_publication, true);


        foreach ($publication['authors'] as $pub_author) {
            $author = Author::get($pub_author, true);

            $paganum1 = 1;

            while ($author_publications_part = Author::parsePublicationsPart($pub_author, $paganum1)) {
                $ppublication = Publication::get($author_publications_part);
                $paganum1++;
            }


            foreach ($author['publications'] as $author_publication) {
                Publication::get($author_publication);
            }

            foreach ($author['organisations'] as $author_organisation) {
                Organisation::get($author_organisation);
            }
        }

        foreach ($publication['keywords'] as $keyword) {
            Keyword::get($keyword);
        }


        foreach ($publication['refs'] as $pub_refs) {
            $ref = Publication::get($pub_refs, true);

            foreach ($ref['authors'] as $ref_author) {
                Author::get($ref_author);
            }

            foreach ($ref['keywords'] as $keyword) {
                Keyword::get($keyword);
            }

            foreach ($ref['publications'] as $author_publication) {
                Publication::get($author_publication);
            }

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




