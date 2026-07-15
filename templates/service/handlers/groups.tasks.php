<?php

function groups_tasks_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( !isset($params['id']) ) { return json_encode(false); }

  $query = 'SELECT id, entryTime, task_id, group_id, fte, DATE(begin_time) as begin_time, DATE(end_time) as end_time, validated FROM `'.$db_name.'`.`tasks_groups` WHERE `group_id` = '.intval($params['id']).' ORDER BY end_time DESC;';
  $res = $db->Query($query);

  return json_encode( $res );
}
