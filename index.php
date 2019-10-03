<?php

include 'init.php';

$elibParser = new ElibraryParser();
$elibDB = new ElibraryDB();

$org = 5051;

$k = 0;

while ($k < 1) {

    $info = $elibParser->getPublications($org, $k + 1);
    if (!empty($info)) {
        foreach ($info as $publ_id) {
            if (!$elibDB->checkExist('publications', $publ_id)) {
                $publication = $elibParser->getPublication($publ_id);
                if (empty($publication)) {
                    continue;
                }
                echoVarDumpPre($publ_id);

                $elibDB->savePublication($publication);

                if (empty($publication['authors'])) {
                    continue;
                }

                foreach ($publication['authors'] as $author_id) {

                    if (!$elibDB->checkExist('authors', $author_id)) {
                        $author = $elibParser->getAuthorInfo($author_id);

                        if (empty($author)) {
                            continue;
                        }

                        $elibDB->saveAuthor($author);


                        foreach ($author['organisations'] as $organisation_id) {
                            if (!$elibDB->checkExist('organisations', $organisation_id)) {
                                $organisation = $elibParser->getOrganisationInfo($organisation_id);

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


$elibDB->saveAuthor($info);

foreach ($info['organisations'] as $org_id) {
    $org = $elibParser->getOrganisationInfo($org_id);
    $elibDB->saveOrganisation($org);
}


echoVarDumpPre($info);
?>


