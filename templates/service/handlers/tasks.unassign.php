<?php

function tasks_unassign_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( !isset($params['id']) ) { return json_encode(false); }
  $id = intval($params['id']);

  $query = 'DELETE FROM `'.$db_name.'`.`tasks_assigned` WHERE `id` = '.$id.' LIMIT 1';
  $db->Query($query);

  return json_encode(true);
}
