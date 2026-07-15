<?php

function tasks_assignedupdate_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( !isset($params['data']) || !isset($params['data']['id']) ) { return json_encode(false); }

  $id = intval($params['data']['id']);
  $mid = intval($params['data']['member_id']);
  $task_id = intval($params['data']['task_id']);
  $group_id = intval($params['data']['group_id']);
  $fte = floatval(strval($params['data']['fte']));
  $begin_time = $params['data']['begin_time'];
  $end_time = $params['data']['end_time'];
  $validated = intval($params['data']['validated']);

  $query = 'UPDATE `'.$db_name.'`.`tasks_assigned` SET task_id = '.$task_id.', member_id = '.$mid.', group_id = '.$group_id.', fte = '.$fte.', begin_time = "'.$db->Escape($begin_time).'", end_time = "'.$db->Escape($end_time).'", validated = '.$validated.' WHERE `id` = '.$id.' LIMIT 1';
  $db->Query($query);

  return json_encode( true );
}
