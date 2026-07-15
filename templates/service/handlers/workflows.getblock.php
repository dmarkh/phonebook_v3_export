<?php

#
# Get workflow block:
#
# /workflows/getblock/id:<number>
#

function workflows_getblock_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $id = intval($params['id']);
  if ( empty($id) ) { return json_encode(false); }

  $query = 'SELECT * FROM `'.$db_name.'`.`workflows_blocks` WHERE id = '.$id.' LIMIT 1';
  $doc = $db->Query($query);

  $docb = $db->Query($query);
  if ( empty($docb) ) {
    return json_encode(false);
  }
  $docb = $docb[0];
  return json_encode( array( 'block' => $docb ) );

}
