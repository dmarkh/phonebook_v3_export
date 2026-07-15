<?php

function eventpolls_list_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');


  $status = 'active';
  if ( isset($params['status']) && in_array($params['status'], [ 'active', 'inactive', 'onhold' ]) ) {
	$status = $params['status'];
  }

  $limit_query = '';
  if ( isset($params['page']) && isset($params['rows-per-page']) ) {
    $limit_query = ' LIMIT '.intval($params['page'] * $params['row-per-page']).','.intval($params['rows-per-page']);
  }

  $query = 'SELECT * FROM `'.$db_name.'`.`eventpolls` WHERE `status` = "'.$db->Escape($status).'" ORDER BY `id` DESC'.$limit_query;
  $res = $db->Query($query);
  if ( empty($res) ) { return json_encode(false); }

  $eventpolls = array();
  foreach( $res as $k => $v ) {
    $eventpolls[] = $v;
  }

  return json_encode($eventpolls);
}
