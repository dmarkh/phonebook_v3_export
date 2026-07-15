<?php

function institutions_tasks_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( !isset($params['id']) ) { return json_encode(false); }

  $query = 'SELECT id, entryTime, task_id, institution_id, fte, DATE(begin_time) as begin_time, DATE(end_time) as end_time FROM `'.$db_name.'`.`tasks_institutions` WHERE `institution_id` = '.intval($params['id']).' ORDER BY end_time DESC;';
  $res = $db->Query($query);

  return json_encode( $res );
}
