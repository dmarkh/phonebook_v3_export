<?php

# 
# Get tasks history for task <id>
#
# /tasks/history/id:[N]
#
#

function tasks_history_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $history = [];
  if ( isset($params['id']) ) {
	  $id = intval($params['id']);
	  $query = 'SELECT * FROM `'.$db_name.'`.`tasks_history` WHERE `tasks_id` = '.$id.' ORDER BY `date` DESC';
	  $history = $db->Query($query);
  } else {
	  $query = 'SELECT * FROM `'.$db_name.'`.`tasks_history` WHERE 1 ORDER BY `date` DESC LIMIT 1000;';
	  $history = $db->Query($query);
  }
  return json_encode($history);
}
