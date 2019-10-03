<?php

include 'init.php';

$elib = new Elibrary();

$org = 5051;
$publlication = 37039312;

$info = $elib->getPublication($publlication);


echoVarDumpPre($info);
?>


