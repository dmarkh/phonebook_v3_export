<?php

function eventpolls_vote_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( empty($params['data']) || !is_array($data) ) { return json_encode(false); }
  if ( empty($params['data']['poll_id'])  || empty($params['data']['member_id']) 
	|| empty($params['data']['marked']) || empty($params['data']['name']) ) { return json_encode(false); }

  $poll_id = intval($params['data']['poll_id']);
  $member_id = intval($params['data']['member_id']);
  $marked = $params['data']['marked'];
  $name = $params['data']['name'];
  $marked = json_encode($marked);

  $query = 'INSERT INTO `'.$db_name.'`.`eventpolls_votes` (`poll_id`, `member_id`, `name`, `marked`) 
	VALUES ( '.$poll_id.', '.$member_id.', "'.$db->Escape($name).'", "'.$db->Escape($marked).'" ) ON DUPLICATE KEY UPDATE `marked` = "'.$db->Escape($marked).'";';
  $db->Query($query);

  $id = $db->LastID();

  if (empty($id)) {
 	return json_encode(false);
  }

  return json_encode([ 'id' => $id ]);
}
