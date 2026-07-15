<?php

function workflows_addcomment_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( empty($params['workflow_id']) ) { return json_encode(false); }
  if ( empty($params['document_id']) ) { return json_encode(false); }
  if ( empty($params['member_id']) ) { return json_encode(false); }
  if ( empty($params['comment']) ) { return json_encode(false); }

  $query = 'INSERT INTO `'.$db_name.'`.`workflows_comments` (`workflow_id`, `document_id`, `member_id`, `comment` )'
	.' VALUES ('.intval($params['workflow_id']).', '.intval($params['document_id']).', '.intval($params['member_id']).', "'.$db->Escape($params['comment']).'")';

  $db->Query($query);
  $id = $db->LastID();

  if ( empty($id) ) {
 	return json_encode(false);
  }

  return json_encode([ 'id' => $id ]);
}
