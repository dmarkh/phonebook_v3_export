<?php

function groups_addrole_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }


  if ( !isset($data['group_id']) || empty($data['name']) ) { return json_encode(false); }

  $query = 'INSERT INTO `'.$db_name.'`.`groups_roles` (`group_id`, `role`) VALUES ( '.intval($data['group_id']).', "'.$db->Escape($data['name']).'")';
  $db->Query($query);

  $id = $db->LastID();

  if (empty($id)) {
 	return json_encode(false);
  }

  return json_encode([ 'id' => $id ]);
}
