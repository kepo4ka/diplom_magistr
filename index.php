<?php

include 'init.php';

$elib = new Elibrary();


$organisation = $elib->getOrganisationInfo('5051');


echoVarDumpPre($organisation);
?>


