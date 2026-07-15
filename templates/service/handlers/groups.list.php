<?php

#
# Get groups list:
#
# /groups/list/status:[all,active,inactive]
#

function groups_list_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $status_query = '`status` != "undefined"';
  if (isset($params['status']) && !empty($params['status'])) {
    $params['status'] = strtolower($params['status']);
    switch($params['status']) {
        case 'all':
            $status_query = '`status` != "undefined"';
            break;
        case 'active':
            $status_query = '`status` = "active"';
            break;
        case 'inactive':
            $status_query = '`status` = "inactive"';
            break;
        default:
            $status_query = '`status` != "undefined"';
            break;
    }
  }

  $query = 'SELECT * FROM `'.$db_name.'`.`groups` WHERE '.$status_query;
  $grp = $db->Query($query);

  $query = 'SELECT * FROM `'.$db_name.'`.`groups_roles`';
  $roles = $db->Query($query);
  $rl = [];
  foreach( $roles as $k => $v ) {
	$rl[$v['group_id']][$v['id']] = $v;
  }

  $groups = array();
  foreach( $grp as $k => $v ) {
	$group = $v;

	$query = 'SELECT COUNT(*) AS mcnt FROM `'.$db_name.'`.`groups_members` WHERE `group_id` = '.intval( $v['id'] );
	$mcnt = $db->Query($query);
	$group['member-count'] = $mcnt['mcnt'][0];

	$query = 'SELECT COUNT(*) as gcnt FROM `'.$db_name.'`.`groups` WHERE `parent` = '.intval( $v['id'] );
	$gcnt = $db->Query($query);
	$group['group-count'] = $gcnt['gcnt'][0];

	if ( !empty($rl[$v['id']]) ) {
		$group['roles'] = $rl[$v['id']];
	} else {
		$group['roles'] = false;
	}

	$groups[] = $group;
  }

  return json_encode( $groups );
}
