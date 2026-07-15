<?php

#
# Get events list:
#
# /events/list/status:[all,active,onhold,inactive]
#
# /events/list/details:[compact,full]
#

function events_list_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $status_query = '`status` != "undefined"';
  if (isset($params['status']) && !empty($params['status'])) {
    $params['status'] = strtolower($params['status']);
    switch($params['status']) {
        case 'all':
            $status_query = '`status` != "undefined"';
            break;
        case 'active':
            $status_query = '`status` = "active"';
            break;
        case 'onhold':
            $status_query = '`status` = "onhold"';
            break;
        case 'inactive':
            $status_query = '`status` = "inactive"';
            break;
        default:
            $status_query = '`status` != "undefined"';
            break;
    }
  }


  $details = 'full';
  if (isset($params['details']) && !empty($params['details'])) {
	$details = trim($params['details']);
  }

  $limit_query = '';
  if ( isset($params['page']) && isset($params['rows-per-page']) ) {
    $limit_query = ' LIMIT '.intval($params['page']).','.intval($params['rows-per-page']);
  }

  $query = 'SELECT * FROM `'.$db_name.'`.`events` WHERE '.$status_query.' ORDER BY `id` DESC'.$limit_query;
  $evt = $db->Query($query);

  $events = array();
  foreach($evt as $k => $v) {
    $events[$v['id']]['status'] = $v['status'];
    $events[$v['id']]['status_change_date'] = $v['status_change_date'];
    $events[$v['id']]['status_change_reason'] = $v['status_change_reason'];
    $events[$v['id']]['last_update'] = $v['last_update'];
  }

  $query = 'SELECT * FROM `'.$db_name.'`.`events_fields`';
  $fields_res = $db->Query($query);
  $fields = array();
  $fields_fn = array();
  $inst_field_id = 0;
  foreach($fields_res as $k => $v) {
	$fields[$v['id']] = $v;
	$fields_fn[$v['name_fixed']] = $v;
  }

  $events_fields = array();
  foreach(array('string','int','date','text') as $k => $v) {
  	$query = 'SELECT * FROM `'.$db_name.'`.`events_data_'.$v.'s`';
	$res = $db->Query($query);
	if (empty($res)) continue;
	foreach($res as $k2 => $v2) {
        if ($v == 'int') { $v2['value'] = intval($v2['value']); }
        $events_fields[$v2['events_id']][$v2['events_fields_id']] = $v2['value'];
	}
  }

  foreach($events_fields as $k => $v) {
	if (!isset($events[$k])) { continue; }
	switch ($details) {
		case 'compact':
			$events[$k]['fields'][$fields_fn['title']['id']] = $v[$fields_fn['title']['id']];
			$events[$k]['fields'][$fields_fn['abstract']['id']] = $v[$fields_fn['abstract']['id']];
			$events[$k]['fields'][$fields_fn['url']['id']] = $v[$fields_fn['url']['id']];
			break;
		case 'full':
			$events[$k]['fields'] = $v;
			break;
	}
  }
  return json_encode($events);
}
