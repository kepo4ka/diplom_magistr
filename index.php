<?php

include 'init.php';

$elibParser = new ElibraryParser();
$elibDB = new ElibraryDB();

$org = 5051;
$publlication = 37039312;

$info = $elibParser->getPublication();

$elibDB->savePublication($info);

$refs = $info['refs'];



echoVarDumpPre($refs);
?>


