<?php

function workflows_advance_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $document_id = intval($params['id']);

  if ( empty($document_id) ) { return json_encode(false); }

  $rc = workflows_advance( $document_id );

  return json_encode( $rc );
}
