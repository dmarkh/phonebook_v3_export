<?php

#
# Get workflows config list:
#
# /workflows/listconfig
#

function workflows_listconfig_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $id = intval($params['id']);
  if ( empty($id) ) { return json_encode(false); }

  $query = 'SELECT * FROM `'.$db_name.'`.`workflows_configs` WHERE `workflow_id` = '.$id.' ORDER BY `step_id` ASC';
  $doc = $db->Query($query);

  return json_encode( array( 'data' => $doc ) );
}
