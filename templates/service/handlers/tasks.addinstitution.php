<?php

function tasks_addinstitution_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }


  if ( !isset($data['task_id']) || empty($data['institution_id']) ) { return json_encode(false); }

  $query = 'INSERT INTO `'.$db_name.'`.`tasks_institutions` (`task_id`, `institution_id`, `fte`) VALUES ( '.intval($data['task_id']).', '.intval($data['institution_id']).', '.floatval($data['fte']).')';
  $db->Query($query);

  $id = $db->LastID();

  if (empty($id)) {
	return json_encode(false);
  }

  return json_encode([ 'id' => $id ]);
}
