<?php

function workflows_removeconfig_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( empty($params['data']) ) { return json_encode(false); }
  if ( !isset($params['data']['workflow_id']) ) { return json_encode(false); }
  if ( !isset($params['data']['block_id']) ) { return json_encode(false); }
  if ( !isset($params['data']['step_id']) ) { return json_encode(false); }

  $query = 'DELETE FROM `'.$db_name.'`.`workflows_configs` WHERE `workflow_id` = '.intval($params['data']['workflow_id']).' AND `block_id` = '.intval($params['data']['block_id']).' AND `step_id` = '.intval($params['data']['step_id']).' LIMIT 1';
  $db->Query($query);

  return json_encode(true);
}
