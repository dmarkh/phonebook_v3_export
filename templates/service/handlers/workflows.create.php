<?php

# 
# Create workflow details:
#	status = user status
#   name = workflow name
#   description = workflow description
#
# /workflows/create
#
# JSON body:
# "data": {
#	"status": "active|onhold|inactive",
#   "name": "<bla>",
#   "description": "<bla>",
#	"weight": <number>
# }
#

function workflows_create_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }
  if ( empty($params['data']['name']) || !isset($params['data']['description']) || !isset($params['data']['weight']) ) { return json_encode(false); }

  // create workflow in the `workflows` table:
  $query = 'INSERT INTO `'.$db_name.'`.`workflows` (`status`, `name`, `description`, `weight` )'
	.' VALUES ("'.$db->Escape($params['data']['status']).'", "'.$db->Escape($params['data']['name']).'", "'.$db->Escape($params['data']['description']).'", '.intval($params['data']['weight']).' )';

  $db->Query($query);
  $id = $db->LastID();

  if ( empty($id) ) {
 	return json_encode(false);
  }

  return json_encode([ 'id' => $id ]);
}
