<?php

include 'init.php';

$elib = new Elibrary('test', 'test');

echoVarDumpPre($elib->getPublicationsInOrganication(17954));


?>


