<?php

function tasks_assigned_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $query = 'SELECT id, entryTime, task_id, member_id, group_id, fte, DATE(begin_time) as begin_time, DATE(end_time) as end_time, validated FROM `'.$db_name.'`.`tasks_assigned` ORDER BY end_time, id DESC;';
  $res = $db->Query($query);

  return json_encode( $res );
}
