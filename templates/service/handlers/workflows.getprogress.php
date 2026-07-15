<?php

# 
# Get workflow progress
#
# /workflows/getprogress/document_id:[N]/workflow_id:[M]
#

function workflows_getprogress_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( empty($params['document_id']) ) { return json_encode(false); }
  if ( empty($params['workflow_id']) ) { return json_encode(false); }

  $query = 'SELECT * FROM `'.$db_name.'`.`workflows_progress` WHERE `document_id` = '.intval($params['document_id']).' AND `workflow_id` = '.intval($params['workflow_id']).' ORDER BY `id` ASC';
  $docb = $db->Query($query);

  return json_encode( array( 'progress' => $docb ) );
}
