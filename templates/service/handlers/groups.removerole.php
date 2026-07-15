<?php

function groups_removerole_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }


  if ( !isset($data['group_id']) || !isset($data['role_id']) ) { return json_encode(false); }

  $query = 'DELETE FROM `'.$db_name.'`.`groups_roles` WHERE `group_id` = '.intval($data['group_id']).' AND `id` = '.intval($data['role_id']).' LIMIT 1;';
  $db->Query($query);

  return json_encode(true);
}
