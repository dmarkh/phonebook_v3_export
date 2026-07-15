<?php

# 
# Get group details
#
# /groups/get/id:[N]
#

function groups_get_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $id = intval($params['id']);

  $query = 'SELECT * FROM `'.$db_name.'`.`groups` WHERE `id` = '.$id.' LIMIT 1';
  $grp = $db->Query($query);
  if (!empty($grp)) {
  	$grp = $grp[0];

    $query = 'SELECT member_id, role_id FROM `'.$db_name.'`.`groups_members` WHERE `group_id` = '.intval($id);
    $members = $db->Query($query);
	$grp['members'] = count($members) ? $members : [];

    $query = 'SELECT id FROM `'.$db_name.'`.`groups` WHERE `parent` = '.intval($id);
	$groups = $db->Query($query);
    $grp['groups'] = count($groups) ? $groups['id'] : [];
  }

  return json_encode( $grp );
}
