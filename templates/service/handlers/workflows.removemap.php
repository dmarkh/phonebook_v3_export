<?php

# 
# Remove workflow mapping
#
# /workflows/removemap
#
# JSON body:
# "data": {
#   "workflow_id": X, // mandatory
#   "document_id": Y, // mandatory
# }

function workflows_removemap_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( empty($params['data']) || !is_array($data) ) { return json_encode(false); }
  if ( empty($params['data']['document_id']) || empty($params['data']['workflow_id']) ) { return json_encode(false); }

  $query = 'DELETE FROM `'.$db_name.'`.`workflows_documents` WHERE `document_id` = '.intval($params['data']['document_id']).' AND `workflow_id` = '.intval( $params['data']['workflow_id'] ).' LIMIT 1';
  $db->Query($query);

  return json_encode(true);
}
