<?php

function groups_roles_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( !isset($params['id']) ) { return json_encode(false); }

  $query = 'SELECT * FROM `'.$db_name.'`.`groups_roles` WHERE `group_id` = '.intval($params['id']).' ORDER BY `weight` ASC;';
  $res = $db->Query($query);

  return json_encode( $res );
}
