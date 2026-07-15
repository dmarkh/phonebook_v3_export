<?php

function workflows_deleteconfig_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( !isset($params['id']) ) { return json_encode(false); }

  $query = 'DELETE FROM `'.$db_name.'`.`workflows_configs` WHERE `workflow_id` = '.intval($params['id']);
  $db->Query($query);

  return json_encode(true);
}
