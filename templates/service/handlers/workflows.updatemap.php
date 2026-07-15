<?php

# 
# Update workflow mapping details
#
# /workflow/updatemap
#
# JSON body:
# "data": {
#   "workflow_id": X, // mandatory
#   "document_id": Y, // mandatory
#   "status": M,
#   "current_step_id": N
# }

function workflows_updatemap_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( empty($params['data']) || !is_array($data) ) { return json_encode(false); }
  if ( empty($params['data']['document_id']) || empty($params['data']['workflow_id']) ) { return json_encode(false); }

  $upd = array();
  if ( isset($params['data']['status'] ) ) {
	$upd[] = '`status` = '.intval( $params['data']['status']);
  }
  if ( isset($params['data']['current_step_id'] ) ) {
	$upd[] = '`current_step_id` = '.intval( $params['data']['current_step_id'] );
  }
  if ( empty($upd) ) { return json_encode(false); }

  $query = 'UPDATE `'.$db_name.'`.`workflows_documents` SET '.implode(',', $upd).' WHERE `document_id` = '.intval($params['data']['document_id']).' AND `workflow_id` = '.intval( $params['data']['workflow_id'] );
  $db->Query($query);

  return json_encode(true);
}
