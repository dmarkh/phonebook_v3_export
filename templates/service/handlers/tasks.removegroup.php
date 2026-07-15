<?php

function tasks_removegroup_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }
  if ( !isset($data['task_id']) || !isset($data['group_id']) ) { return json_encode(false); }

  $query = 'DELETE FROM `'.$db_name.'`.`tasks_groups` WHERE `task_id` = '. intval($data['task_id']) . ' AND `group_id` = '. intval($data['group_id']) .' LIMIT 1;';
  $db->Query($query);

  return json_encode([ "result" => "success" ]);
}
