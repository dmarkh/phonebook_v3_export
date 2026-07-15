<?php

function service_lastupdate_handler($params) {
	$cnf =& ServiceConfig::Instance();
    $db =& ServiceDb::Instance('phonebook_api');
    $db_name = $cnf->Get('phonebook_api','database');

	$query = 'SELECT UNIX_TIMESTAMP(`members`) as members, UNIX_TIMESTAMP(`institutions`) as institutions FROM `'.$db_name.'`.`last_update` WHERE 1 LIMIT 1';
	$res = $db->Query($query);
	$res = $res[0];
	$res['members'] = intval( $res['members'] );
	$res['institutions'] = intval( $res['institutions'] );
    return json_encode( $res );
}