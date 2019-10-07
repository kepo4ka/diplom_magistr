<?php

include 'init.php';


//exit;

//$elib = new ElibraryParser();
$elibCurl = new ElibraryCurl();
$elibDB = new ElibraryDB();

$org = 5051;

$start = microtime(true);

$info = $elibCurl->getOrganisationInfo($org);
$elibDB->saveOrganisation($info);

$k = 1;

while (true) {
    $info = $elibCurl->getOrgPublications($org, $k);
    if (!empty($info)) {
        foreach ($info as $publ_id) {
            if (!$elibDB->checkExist('publications', $publ_id)) {
                $publication = $elibCurl->getPublication($publ_id);
                if (empty($publication)) {
                    continue;
                }

                $elibDB->savePublication($publication);

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

                        foreach ($author['organisations'] as $organisation_id) {
                            if (!$elibDB->checkExist('organisations', $organisation_id)) {
                                $organisation = $elibCurl->getOrganisationInfo($organisation_id);

                                if (empty($organisation)) {
                                    continue;
                                }

                                $elibDB->saveOrganisation($organisation);
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

echo 'Информация об организации <>' . $info['name'] . '</b> Добавлена <hr>';
echo 'Время выполнения скрипта: ' . round(microtime(true) - $start, 4) . ' сек.';
exit;

?>


