<?php

# 
# Get workflow details
#
# /workflows/get/id:[N]
#

function workflows_get_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $id = intval($params['id']);
  if ( empty($id) ) { return json_encode(false); }

  $query = 'SELECT * FROM `'.$db_name.'`.`workflows` WHERE `id` = '.$id.' LIMIT 1';
  $docb = $db->Query($query);
  if ( empty($docb) ) {
	return json_encode(false);
  }
  $docb = $docb[0];
  return json_encode( array( 'workflow' => $docb ) );
}
