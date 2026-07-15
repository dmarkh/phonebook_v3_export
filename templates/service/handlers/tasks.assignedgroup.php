<?php

function tasks_assignedgroup_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( !isset($params['id']) ) { return json_encode(false); }
  $id = intval($params['id']);

  $query = 'SELECT id, entryTime, task_id, member_id, group_id, fte, DATE(begin_time) as begin_time, DATE(end_time) as end_time, validated FROM `'.$db_name.'`.`tasks_assigned` WHERE `group_id` = '.$id.' ORDER BY end_time, id DESC;';
  $res = $db->Query($query);

  return json_encode( $res );
}
