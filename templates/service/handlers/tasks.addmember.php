<?php

function tasks_addmember_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }


  if ( !isset($data['task_id']) || empty($data['member_id']) ) { return json_encode(false); }

  $query = 'INSERT INTO `'.$db_name.'`.`tasks_members` (`task_id`, `member_id`, `fte`) VALUES ( '.intval($data['task_id']).', '.intval($data['member_id']).', '.floatval($data['fte']).')';
  $db->Query($query);

  $id = $db->LastID();

  if (empty($id)) {
 	return json_encode(false);
  }

  return json_encode([ 'id' => $id ]);
}
