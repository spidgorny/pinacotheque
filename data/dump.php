<?php

$pdo = new PDO('pgsql:host=vlad01dev01.nintendo.de port=5432 dbname=dci', 'dci_adm', 'dci_adm');

$res = $pdo->query('SELECT * FROM "system"');

$data = $res->fetchAll();

file_put_contents('system.json',
	json_encode($data, JSON_PRETTY_PRINT));
