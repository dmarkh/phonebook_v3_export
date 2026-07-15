<?php

function groups_updatememberrole_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }
  if ( !isset($data['group_id']) || !isset($data['member_id']) || !isset($data['role_id']) ) { return json_encode(false); }

  $query = 'UPDATE `'.$db_name.'`.`groups_members` SET role_id = '.intval($data['role_id']).' WHERE `group_id` = '. intval($data['group_id']) . ' AND `member_id` = '. intval($data['member_id']) .';';
  $db->Query($query);

  return json_encode([ "result" => "success" ]);
}
