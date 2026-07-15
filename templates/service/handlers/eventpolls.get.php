<?php

function eventpolls_get_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $id = intval($params['id']);

  $query = 'SELECT * FROM `'.$db_name.'`.`eventpolls` WHERE `id` = '.$id.' LIMIT 1';
  $poll = $db->Query($query);
  if ( empty($poll) ) {
	return json_encode(false);
  }
  $poll = $poll[0];
  $poll['days'] = json_decode($poll['days']);

  $query = 'SELECT * FROM `'.$db_name.'`.`eventpolls_votes` WHERE `poll_id` = '.$id;
  $votes = $db->Query($query);
  $poll['votes'] = empty($votes) ? array() : $votes;

  $query = 'SELECT * FROM `'.$db_name.'`.`eventpolls_members` WHERE `poll_id` = '.$id;
  $members = $db->Query($query);
  if ( !empty($members) ) {
	$mem = [];
	foreach( $members as $k => $v ) {
		$mem[] = $v['member_id'];
	}
	$poll['members'] = empty($mem) ? array() : $mem;
  }

  $query = 'SELECT * FROM `'.$db_name.'`.`eventpolls_groups` WHERE `poll_id` = '.$id;
  $groups = $db->Query($query);
  if ( !empty($groups) ) {
	$grp = [];
	foreach( $groups as $k => $v ) {
		$grp[] = $v['group_id'];
	}
	$poll['groups'] = empty($grp) ? array() : $grp;
  }


  return json_encode( $poll );
}
