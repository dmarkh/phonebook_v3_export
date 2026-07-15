<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
ini_set('memory_limit', '128M');
set_time_limit(120);

require_once __DIR__ . '/vendor/autoload.php';

require_once('./auth.php');

$check = authenticate();

if ( $check === false ) {
	// auth failed, exit
	echo json_encode([ 'error' => 'NOT AUTHENTICATED' ]);
	exit;
} else if ( is_array( $check ) ) {
	// auth successful, return grants list
	echo json_encode( $check );
	exit;
} else {
	// auth successful - via token
}

include_once('./include/ServiceConfig.class.php');
include_once('./include/ServiceController.class.php');
include_once('./include/ServiceDb.class.php');
include_once('./include/common.functions.php');
include_once('./include/workflows.php');
include_once('./include/php-excel.class.php');
include_once('./include/invenio-integration.php');

$cnf =& ServiceConfig::Instance();
$db =& ServiceDb::Instance('phonebook_api');

$controller = new ServiceController();
$controller->Run();
