<?php

include 'init.php';


//exit;

//$elib = new ElibraryParser();
$elibCurl = new ElibraryCurl();
$elibDB = new ElibraryDB();

$org_id = 5051;

$start = microtime(true);

$organisation = $elibCurl->getOrganisationInfo($org_id);
$elibDB->saveOrganisation($organisation);

$k = 1;

while (true) {
    $organisation = $elibCurl->getOrgPublications($org_id, $k);
    if (!empty($organisation)) {
        foreach ($organisation as $publ_id) {
            if (!$elibDB->checkExist('publications', $publ_id)) {
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

                    if (!$elibDB->checkExist('authors', $author_id)) {
                        $author = $elibCurl->getAuthorInfo($author_id);

                        if (empty($author)) {
                            continue;
                        }

                        $elibDB->saveAuthor($author);
                        $elibDB->relationAuthorPublication($publication['id'], $author['id']);

                        foreach ($author['organisations'] as $organisation_id) {
                            if (!$elibDB->checkExist('organisations', $organisation_id)) {
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

echo 'Информация об организации <>' . $organisation['name'] . '</b> Добавлена <hr>';
echo 'Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';
exit;




