<?php

	include __DIR__ . "/classes/AuthNew.class.php";
	include __DIR__ . "/functions/functions.php";
	include __DIR__ . "/functions/dbFunctions.php";
	include __DIR__ . "/config.inc.php";
	include __DIR__ . "/versao.php";
 	include __DIR__ . "/languages/" . LANGUAGE; //TEMPORARIAMENTE
 	include __DIR__ . "/queries/queries.php";

	$apiAutoload = dirname(__DIR__) . "/api/ocomon_api/vendor/autoload.php";
	if (is_file($apiAutoload)) {
		require $apiAutoload;
	}

?>
