<?php

#
# Get document comments list:
#
# /documents/listcomments/:id
#

function documents_listcomments_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( empty($params['id']) ) { return json_encode(false); }
  $id = intval($params['id']);

  $query = 'SELECT * FROM `'.$db_name.'`.`documents_comments` WHERE `document_id` = '.$id.' ORDER BY `created` ASC';
  $doc = $db->Query($query);

  return json_encode($doc);
}
