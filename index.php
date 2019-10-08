<?php

include 'init.php';


//exit;

//$elib = new ElibraryParser();
$elibCurl = new ElibraryCurl();
$elibDB = new ElibraryDB();

$org_id = 54;

$query_count = 0;

$list = array();

$start = microtime(true);


$organisation = $elibCurl->getOrganisationInfo($org_id);
$elibDB->saveOrganisation($organisation);

$k = 1;

while (true) {
    $org_publications = $elibCurl->getOrgPublications($org_id, $k);
    echoVarDumpPre($org_publications);
    
    if (!empty($org_publications)) {
        foreach ($org_publications as $publ_id) {
            if (!checkExist('publications', $publ_id)) {
                $publication = $elibCurl->getPublication($publ_id);
                if (empty($publication)) {
                    continue;
                }

                $elibDB->savePublication($publication);

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
        }

    } else {
        break;
    }
    $k++;
}

echo "Количество запросов orgPublications: " . $query_count . "<br>";

echo 'Информация об организации <>' . $organisation['name'] . '</b> Добавлена <hr>';
echo 'Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';
exit;




