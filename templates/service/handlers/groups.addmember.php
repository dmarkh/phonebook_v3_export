<?php

function groups_addmember_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }


  if ( !isset($data['group_id']) || empty($data['member_id']) ) { return json_encode(false); }

  $query = 'INSERT INTO `'.$db_name.'`.`groups_members` (`group_id`, `member_id`, `role_id`) VALUES ( '.intval($data['group_id']).', '.intval($data['member_id']).', '.intval($data['role_id']).')';
  $db->Query($query);

  $id = $db->LastID();

  if (empty($id)) {
 	return json_encode(false);
  }

  return json_encode([ 'id' => $id ]);
}
