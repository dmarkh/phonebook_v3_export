<?php

# 

function eventpolls_toggle_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $states = array('active', 'inactive');

  $id = intval($params['id']);

  $query = 'SELECT `status` FROM `'.$db_name.'`.`eventpolls` WHERE `id` = '.$id;
  $res = $db->Query($query);
  $status = strtolower($res['status'][0]);
  if (!in_array($status, $states)) {
	return json_encode(false);
  }
  $new_status = '';
  switch ($status) {
	case 'active':
		$new_status = 'inactive';
		break;
	case 'inactive':
		$new_status = 'active';
		break;
	default:
		return json_encode(false);
		break;
  }

  $query = 'UPDATE `'.$db_name.'`.`eventpolls` SET `status` = "'.$db->Escape($new_status).'" WHERE `id` = '.$id.' LIMIT 1';
  $db->Query($query);

  return json_encode(true);
}
