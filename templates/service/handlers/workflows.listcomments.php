<?php

#
# Get workflow comments list:
#
# /workflows/listcomments/workflow:id/document:id
#

function workflows_listcomments_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $workflow_id = intval($params['workflow']);
  if ( empty($workflow_id) ) { return json_encode(false); }

  $document_id = intval($params['document']);
  if ( empty($workflow_id) ) { return json_encode(false); }

  $query = 'SELECT * FROM `'.$db_name.'`.`workflows_comments` WHERE workflow_id = '.$workflow_id.' AND document_id = '.$document_id.' ORDER BY `created` ASC';
  $comments = $db->Query($query);

  return json_encode( array('comments' => $comments ) );
}
