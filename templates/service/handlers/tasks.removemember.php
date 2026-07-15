<?php

function tasks_removemember_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }
  if ( !isset($data['task_id']) || !isset($data['member_id']) ) { return json_encode(false); }

  $query = 'DELETE FROM `'.$db_name.'`.`tasks_members` WHERE `task_id` = '. intval($data['task_id']) . ' AND `member_id` = '. intval($data['member_id']) .' LIMIT 1;';
  $db->Query($query);

  return json_encode([ "result" => "success" ]);
}
