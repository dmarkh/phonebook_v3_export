<?php

# 
# Create group (one at a time) details: 
#	status = user status
#
# /groups/create
#
# JSON body: 
# "data": {
#	"status": "active|inactive|archived",
#	"parent": 0, // 0 = no parent
#   "name",
#	"desc",
#	"private",
#	"email"
# }

function groups_create_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }


  if ( !isset($params['data']['status']) || empty($params['data']['status']) ) { return json_encode(false); }

  // create group in the `groups` table:
  $query = 'INSERT INTO `'.$db_name.'`.`groups` (`parent`, `status`, `private`, `name`, `desc`, `category`, `email` ) VALUES ( '.intval($data['parent']).',"'.$db->Escape($data['status']).'","'.$db->Escape($data['private']).'","'.$db->Escape($data['name']).'","'.$db->Escape($data['desc']).'","'.$db->Escape($data['category']).'","'.$db->Escape($data['email']).'")';
  $db->Query($query);

  // populate fields in `members_data_dates`/_data_strings/_data_ints tables:
  $id = $db->LastID();

  if (empty($id)) {
 	return json_encode(false);
  }

  return json_encode([ 'id' => $id ]);
}
