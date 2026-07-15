<?php

function members_groups_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( !isset($params['id']) ) { return json_encode(false); }

  $query = 'SELECT id, group_id, member_id, role_id FROM `'.$db_name.'`.`groups_members` WHERE `member_id` = '.intval($params['id']).' ORDER BY id DESC;';
  $res = $db->Query($query);

  return json_encode( $res );
}
