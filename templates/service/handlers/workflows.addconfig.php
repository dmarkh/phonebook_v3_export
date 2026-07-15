<?php

function workflows_addconfig_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( empty($params['data']) ) { return json_encode(false); }
  if ( !isset($params['data']['workflow_id']) ) { return json_encode(false); }
  if ( !isset($params['data']['block_id']) ) { return json_encode(false); }
  if ( !isset($params['data']['step_id']) ) { return json_encode(false); }

#  `member_ids` varchar (2048) NOT NULL DEFAULT '',
#  `group_role_ids` varchar (2048) NOT NULL DEFAULT '',
#  `group_id` int unsigned NOT NULL DEFAULT 0,

  $query = 'INSERT INTO `'.$db_name.'`.`workflows_configs` (`workflow_id`, `block_id`, `step_id` )'
	.' VALUES ('.intval($params['data']['workflow_id']).', '.intval($params['data']['block_id']).', '.intval($params['data']['step_id']).')';

  $db->Query($query);
  $id = $db->LastID();

  if ( empty($id) ) {
 	return json_encode(false);
  }

  if ( isset($params['data']['group_id']) && !empty( $params['data']['group_id'] ) ) {
	$query = 'UPDATE `'.$db_name.'`.`workflows_configs` SET `group_id` = '.intval($params['data']['group_id']).' WHERE `workflow_id` = '.intval($params['data']['workflow_id']).' AND `block_id` = '.intval($params['data']['block_id']).' AND `step_id` = '.intval($params['data']['step_id']).' LIMIT 1';
    $db->Query($query);
  }
  if ( isset($params['data']['group_role_ids']) && !empty( $params['data']['group_role_ids'] ) ) {
	$query = 'UPDATE `'.$db_name.'`.`workflows_configs` SET `group_role_ids` = "'.$db->Escape($params['data']['group_role_ids']).'" WHERE `workflow_id` = '.intval($params['data']['workflow_id']).' AND `block_id` = '.intval($params['data']['block_id']).' AND `step_id` = '.intval($params['data']['step_id']).' LIMIT 1';
    $db->Query($query);
  }
  if ( isset($params['data']['member_ids']) && !empty( $params['data']['member_ids'] ) ) {
	$query = 'UPDATE `'.$db_name.'`.`workflows_configs` SET `member_ids` = "'.$db->Escape($params['data']['member_ids']).'" WHERE `workflow_id` = '.intval($params['data']['workflow_id']).' AND `block_id` = '.intval($params['data']['block_id']).' AND `step_id` = '.intval($params['data']['step_id']).' LIMIT 1';
	$db->Query($query);
  }

  return json_encode([ 'id' => $id ]);
}
