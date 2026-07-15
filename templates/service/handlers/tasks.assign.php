<?php

function tasks_assign_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }
  if ( !isset($data['task_id']) || empty($data['member_id']) || empty( $data['group_id'] ) ) { return json_encode(false); }

	$task_id = intval($data['task_id']);
	$member_id = intval($data['member_id']);
	$group_id = intval($data['group_id']);
	$fte = isset($data['fte']) ? floatval($data['fte']) : 0;
	$begin_time = ( isset($data['begin_time']) && !empty($data['begin_time']) && strtolower($data['begin_time']) !== 'null' ) ? ( '"'.$data['begin_time'].'"' ) : 'NULL';
  	$end_time = ( isset($data['end_time']) && !empty($data['end_time']) && strtolower($data['end_time']) !== 'null' ) ? ( '"'.$data['end_time'].'"' ) : 'NULL';

  $query = 'INSERT INTO `'.$db_name.'`.`tasks_assigned` (`task_id`, `member_id`, `group_id`, `fte`, `begin_time`, `end_time`)'
		.' VALUES ( '.$task_id.', '.$member_id.', '.$group_id.', '.$fte.', '.$begin_time.', '.$end_time.' )';

  $db->Query($query);
  $id = $db->LastID();
  if (empty($id)) {
 	return json_encode(false);
  }
  return json_encode([ 'id' => $id ]);
}
