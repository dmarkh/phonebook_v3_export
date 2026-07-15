<?php

function tasks_validate_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( !isset($params['id']) ) { return json_encode(false); }
  $id = intval($params['id']);

  $val = intval($params['val']);

  $query = 'UPDATE `'.$db_name.'`.`tasks_assigned` SET `validated` = '.$val.' WHERE `id` = '.$id.' LIMIT 1';
  $db->Query($query);

  return json_encode(true);
}
