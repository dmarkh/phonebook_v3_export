<?php

function members_documents_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( !isset($params['id']) ) { return json_encode(false); }

  $query = 'SELECT doc_id AS id FROM `'.$db_name.'`.`documents_owners` WHERE `mem_id` = '.intval($params['id']).' ORDER BY id DESC;';
  $res1 = $db->Query( $query );

  $query = 'SELECT doc_id AS id FROM `'.$db_name.'`.`documents_authors` WHERE `mem_id` = '.intval($params['id']).' ORDER BY id DESC;';
  $res2 = $db->Query( $query );

  $query = 'SELECT doc_id AS id FROM `'.$db_name.'`.`documents_reviewers` WHERE `mem_id` = '.intval($params['id']).' ORDER BY id DESC;';
  $res3 = $db->Query( $query );

  $output = array(
	'owner' => array(),
	'author' => array(),
	'reviewer' => array()
  );

  if ( !empty($res1['id']) ) { $output['owner'] = array_values($res1['id']); }
  if ( !empty($res2['id']) ) { $output['author']  = array_values($res2['id']); }
  if ( !empty($res3['id']) ) { $output['reviewer']  = array_values($res3['id']); }

  return json_encode( $output );
}
