<?php

# 
# Get documents details
#
# /documents/get/id:[N]
#

function documents_get_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $id = intval($params['id']);

  $query = 'SELECT * FROM `'.$db_name.'`.`documents` WHERE `id` = '.$id.' LIMIT 1';
  $docb = $db->Query($query);
  if (!empty($docb)) {
  	$docb = $docb[0];
  }

  $query = 'SELECT * FROM `'.$db_name.'`.`documents_fields`';
  $fields_res = $db->Query($query);
  $fields = array();
  foreach($fields_res as $k => $v) {
	$fields[$v['id']] = $v;
  }

  $docb_fields = array();
  foreach(array('string','int','date','text') as $k => $v) {
  	$query = 'SELECT documents_fields_id as field_id, value as field_value FROM `'.$db_name.'`.`documents_data_'.$v.'s` WHERE documents_id = '.$id;
	$res = $db->Query($query);
	if (empty($res)) continue;
	foreach($res as $k2 => $v2) {
		if ($v == 'int') { $v2['field_value'] = intval($v2['field_value']); }
		$docb_fields[$v2['field_id']] = $v2['field_value'];
	}
  }
  return json_encode(array('document' => $docb, 'fields' => $docb_fields));
}
