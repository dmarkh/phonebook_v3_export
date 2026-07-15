<?php

# 
# Get workflow mapping details
#
# /workflows/getmap/documentid:[N]
#

function workflows_getmap_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $id = intval($params['documentid']);
  if ( empty($id) ) { return json_encode(false); }

  $query = 'SELECT * FROM `'.$db_name.'`.`workflows_documents` WHERE `document_id` = '.$id.' LIMIT 1';
  $docb = $db->Query($query);
  if ( empty($docb) ) {
	return json_encode(false);
  }
  $docb = $docb[0];
  return json_encode( array( 'map' => $docb ) );
}
