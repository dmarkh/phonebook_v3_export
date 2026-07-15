<?php

# 
# Create document comment: 
#
# /documents/addcomment
#
# JSON body:
# "data": {
#   "document_id": INT,
#   "member_id": INT,
#   "member_name": STRING,
#   "comment": TEXT
# }

function documents_addcomment_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }

  if ( empty($params['data']['document_id']) 
		|| empty($params['data']['member_id'])
		|| !isset($params['data']['member_name'])
		|| empty($params['data']['comment'])
	) { return json_encode(false); }

  $query = 'INSERT INTO `'.$db_name.'`.`documents_comments` (`document_id`, `member_id`, `member_name`, `comment`)
	VALUES ('.intval($params['data']['document_id']).', '.intval($params['data']['member_id'])
		.', "'.$db->Escape($params['data']['member_name']).'", "'.$db->Escape($params['data']['comment']).'" )';

  $db->Query($query);
  $id = $db->LastID();

  if (!empty($id)) {
	  return json_encode([ 'id' => $id ]);
  }
  return json_encode(false);
}
