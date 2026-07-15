<?php

function eventpolls_create_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }
  if (     empty($params['data']['title'])  || !isset($params['data']['hr_start'])
		|| !isset($params['data']['hr_end']) || empty($params['data']['days']) || empty($params['data']['type'])
	) { return json_encode(false); }

  if ( $params['data']['type'] !== 'doodle' && $params['data']['type'] !== 'when2meet' ) {
		return json_encode(false);
	}

	$mid = intval($params['data']['mid']);
	$location = isset($params['data']['location']) ? trim($params['data']['location']) : '';
	$timezone = isset($params['data']['timezone']) ? trim($params['data']['timezone']) : 'America/New_York';
	$hr_end   = intval($params['data']['hr_end']);
	$hr_start = intval($params['data']['hr_start']);
	$days = json_encode($params['data']['days']);

  // create event in the `events` table:
  $query = 'INSERT INTO `'.$db_name.'`.`eventpolls` (`mid`, `type`, `title`, `location`, `timezone`, `hr_start`, `hr_end`, `days`) 
	VALUES ('.$mid.',"'.$db->Escape($params['data']['type']).'", "'.$db->Escape($params['data']['title']).'", "'.$db->Escape($location).'", "'.$db->Escape($timezone).'", '.$hr_start.', '.$hr_end.', "'.$db->Escape($days).'" )';
  $db->Query($query);

  $id = $db->LastID();

  if (empty($id)) {
 	return json_encode(false);
  }

  return json_encode([ 'id' => $id ]);
}
