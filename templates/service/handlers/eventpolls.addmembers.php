<?php

function eventpolls_addmembers_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }
  if ( empty($params['data']['poll_id'])  || empty($params['data']['members_ids']) || !is_array($params['data']['members_ids']) ) { return json_encode(false); }

  $poll_id = intval($params['data']['poll_id']);
  $members_ids = $params['data']['members_ids'];

  foreach ($members_ids as $k => $v ) {
	  $query = 'INSERT INTO `'.$db_name.'`.`eventpolls_members` (`poll_id`, `member_id`) VALUES ('.$poll_id.', '.intval($v).' )';
	  $db->Query($query);
  }

  return json_encode([ 'id' => true ]);
}
