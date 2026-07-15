<?php

function workflows_updateconfig_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( empty($params['data']) ) { return json_encode(false); }
  if ( !isset($params['data']['workflow_id']) ) { return json_encode(false); }
  if ( !isset($params['data']['block_id']) ) { return json_encode(false); }
  if ( !isset($params['data']['step_id']) ) { return json_encode(false); }

  if ( isset($params['group_id']) ) {
	$query = 'UPDATE `'.$db_name.'`.`workflows_configs` SET `group_id` = '.intval($params['data']['group_id']).' WHERE `workflow_id` = '.intval($params['data']['workflow_id']).' AND `block_id` = '.intval($params['data']['block_id']).' AND `step_id` = '.intval($params['data']['step_id']).' LIMIT 1';
  }
  if ( isset($params['group_role_ids']) ) {
	$query = 'UPDATE `'.$db_name.'`.`workflows_configs` SET `group_role_ids` = "'.$db->Escape($params['data']['group_role_ids']).'" WHERE `workflow_id` = '.intval($params['data']['workflow_id']).' AND `block_id` = '.intval($params['data']['block_id']).' AND `step_id` = '.intval($params['data']['step_id']).' LIMIT 1';
  }
  if ( isset($params['member_ids']) ) {
	$query = 'UPDATE `'.$db_name.'`.`workflows_configs` SET `member_ids` = "'.$db->Escape($params['data']['member_ids']).'" WHERE `workflow_id` = '.intval($params['data']['workflow_id']).' AND `block_id` = '.intval($params['data']['block_id']).' AND `step_id` = '.intval($params['data']['step_id']).' LIMIT 1';
  }

  return json_encode([ 'id' => $id ]);
}
