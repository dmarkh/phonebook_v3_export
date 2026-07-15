<?php

# 
# Get tasks details
#
# /tasks/get/id:[N]
#

function tasks_get_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $id = intval($params['id']);

  $query = 'SELECT * FROM `'.$db_name.'`.`tasks` WHERE `id` = '.$id.' LIMIT 1';
  $task = $db->Query($query);
  if (!empty($task)) {
  	$task = $task[0];
  }

  $query = 'SELECT * FROM `'.$db_name.'`.`tasks_fields`';
  $fields_res = $db->Query($query);
  $fields = array();
  foreach($fields_res as $k => $v) {
	$fields[$v['id']] = $v;
  }

  $task_fields = array();
  foreach(array('string','int','date','text') as $k => $v) {
  	$query = 'SELECT tasks_fields_id as field_id, value as field_value FROM `'.$db_name.'`.`tasks_data_'.$v.'s` WHERE tasks_id = '.$id;
	$res = $db->Query($query);
	if (empty($res)) continue;
	foreach($res as $k2 => $v2) {
		if ($v == 'int') { $v2['field_value'] = intval($v2['field_value']); }
		$task_fields[$v2['field_id']] = $v2['field_value'];
	}
  }
  return json_encode(array('task' => $task, 'fields' => $task_fields));
}
