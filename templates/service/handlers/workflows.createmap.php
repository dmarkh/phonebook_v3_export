<?php

# 
# Create workflow map details:
#  `document_id` int unsigned NOT NULL,
#  `workflow_id` int unsigned NOT NULL,
#
# /workflows/createmap
#
# JSON body:
# "data": {
#   "document_id": X,
#   "workflow_id": Y
# }
#

function workflows_createmap_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }
  if ( empty($params['data']['document_id']) || empty($params['data']['workflow_id']) ) { return json_encode(false); }

  // create workflow mapping in the `workflows_documents` table:
  $query = 'INSERT INTO `'.$db_name.'`.`workflows_documents` ( `document_id`, `workflow_id` )'
	.' VALUES ( '.intval($params['data']['document_id']).', '.intval($params['data']['workflow_id']).')';

  $db->Query($query);
  $id = $db->LastID();

  if ( empty($id) ) {
 	return json_encode(false);
  }

  return json_encode([ 'id' => $id ]);
}
