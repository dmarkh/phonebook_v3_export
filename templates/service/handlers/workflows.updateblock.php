<?php

#
# Get workflow block:
#
# /workflows/updateblock/id:<number>
#

function workflows_updateblock_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $id = intval($params['data']['id']);
  if ( empty($id) ) { return json_encode(false); }

  if ( isset($params['data']['name']) ) {
    $query = 'UPDATE `'.$db_name.'`.`workflows_blocks` SET `name` = "'.$db->Escape( $params['data']['name'] ).'" WHERE `id` = '.$id;
	$db->Query($query);
  }
  if ( isset($params['data']['description']) ) {
    $query = 'UPDATE `'.$db_name.'`.`workflows_blocks` SET `description` = "'.$db->Escape( $params['data']['description'] ).'" WHERE `id` = '.$id;
	$db->Query($query);
  }
  if ( isset($params['data']['weight']) ) {
    $query = 'UPDATE `'.$db_name.'`.`workflows_blocks` SET `weight` = "'.intval( $params['data']['weight'] ).'" WHERE `id` = '.$id;
	$db->Query($query);
  }
  if ( isset($params['data']['configurable']) ) {
    $query = 'UPDATE `'.$db_name.'`.`workflows_blocks` SET `configurable` = "'.$db->Escape( $params['data']['configurable'] ).'" WHERE `id` = '.$id;
	$db->Query($query);
  }
  if ( isset($params['data']['status']) ) {
    $query = 'UPDATE `'.$db_name.'`.`workflows_blocks` SET `status` = "'.$db->Escape( $params['data']['status'] ).'" WHERE `id` = '.$id;
	$db->Query($query);
  }

  return json_encode(true);

}
