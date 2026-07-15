<?php

function groups_updaterole_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }


  if ( !isset($data['group_id']) || !isset($data['role_id']) || !isset($data['name']) ) { return json_encode(false); }

  $query = 'UPDATE `'.$db_name.'`.`groups_roles` SET `role` = "'.$db->Escape($data['name']).'", `weight` = '.intval($data['weight']).' WHERE `group_id` = '.intval($data['group_id']).' AND `id` = '.intval($data['role_id']).';';
  $db->Query($query);

  return json_encode(true);
}
