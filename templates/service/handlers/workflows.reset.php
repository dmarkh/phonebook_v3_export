<?php

function workflows_reset_handler($params) {

  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $document_id = intval($params['id']);

  if ( empty($document_id) ) { return json_encode(false); }

  $query = 'UPDATE `'.$db_name.'`.`workflows_documents` SET `status` = 0, `current_step_id` = 0 WHERE `document_id` = '.intval($document_id);
  $rc = $db->Query($query);

  $query = 'DELETE FROM `'.$db_name.'`.`workflows_progress` WHERE `document_id` = '.intval($document_id);
  $rc = $db->Query($query);

  return json_encode( $rc );
}
