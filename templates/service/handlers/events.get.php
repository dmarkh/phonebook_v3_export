<?php

# 
# Get events details
#
# /events/get/id:[N]
#

function events_get_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $id = intval($params['id']);

  $query = 'SELECT * FROM `'.$db_name.'`.`events` WHERE `id` = '.$id.' LIMIT 1';
  $evtb = $db->Query($query);
  if (!empty($evtb)) {
  	$evtb = $evtb[0];
  }

  $query = 'SELECT * FROM `'.$db_name.'`.`events_fields`';
  $fields_res = $db->Query($query);
  $fields = array();
  foreach($fields_res as $k => $v) {
	$fields[$v['id']] = $v;
  }

  $evtb_fields = array();
  foreach(array('string','int','date','text') as $k => $v) {
  	$query = 'SELECT events_fields_id as field_id, value as field_value FROM `'.$db_name.'`.`events_data_'.$v.'s` WHERE events_id = '.$id;
	$res = $db->Query($query);
	if (empty($res)) continue;
	foreach($res as $k2 => $v2) {
		if ($v == 'int') { $v2['field_value'] = intval($v2['field_value']); }
		$evtb_fields[$v2['field_id']] = $v2['field_value'];
	}
  }
  return json_encode(array('event' => $evtb, 'fields' => $evtb_fields));
}
