<?php

require_once __DIR__ . '/../bootstrap.php';

$m = new DBLayerPDO(getenv('mysql.db'), getenv('mysql'), getenv('mysql.user'), getenv('mysql.password'));
$m->setQB(new SQLBuilder($m));
$rows = $m->fetchAllSelectQuery('files', []);
debug($rows);

