<?php

include 'init.php';

$elibParser = new ElibraryParser();
$elibDB = new ElibraryDB();

$org = 5051;
$publlication = 37039312;

$info = $elibParser->getAuthorInfo();

$elibDB->saveAuthor($info);

foreach ($info['organisations'] as $org_id) {
    $org = $elibParser->getOrganisationInfo($org_id);
    $elibDB->saveOrganisation($org);
}


echoVarDumpPre($info);
?>


