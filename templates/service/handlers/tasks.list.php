<?php

#
# Get tasks list:
#
# /tasks/list/status:[all,active,onhold,inactive]
#
# /tasks/list/details:[compact,full]
#

function tasks_list_handler($params) {
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

  $query = 'SELECT * FROM `'.$db_name.'`.`tasks` WHERE '.$status_query.' ORDER BY `id` DESC'.$limit_query;
  $task = $db->Query($query);

  $tasks = array();
  foreach($task as $k => $v) {
    $tasks[$v['id']]['status'] = $v['status'];
    $tasks[$v['id']]['status_change_date'] = $v['status_change_date'];
    $tasks[$v['id']]['status_change_reason'] = $v['status_change_reason'];
    $tasks[$v['id']]['last_update'] = $v['last_update'];
  }

  $query = 'SELECT * FROM `'.$db_name.'`.`tasks_fields`';
  $fields_res = $db->Query($query);
  $fields = array();
  $fields_fn = array();
  $inst_field_id = 0;
  foreach($fields_res as $k => $v) {
	$fields[$v['id']] = $v;
	$fields_fn[$v['name_fixed']] = $v;
  }

  $tasks_fields = array();
  foreach(array('string','int','date','text') as $k => $v) {
  	$query = 'SELECT * FROM `'.$db_name.'`.`tasks_data_'.$v.'s`';
	$res = $db->Query($query);
	if (empty($res)) continue;
	foreach($res as $k2 => $v2) {
        if ($v == 'int') { $v2['value'] = intval($v2['value']); }
        $tasks_fields[$v2['tasks_id']][$v2['tasks_fields_id']] = $v2['value'];
	}
  }

  foreach($tasks_fields as $k => $v) {
	if (!isset($tasks[$k])) { continue; }
	switch ($details) {
		case 'compact':
			$tasks[$k]['fields'][$fields_fn['title']['id']] = $v[$fields_fn['title']['id']];
			$tasks[$k]['fields'][$fields_fn['abstract']['id']] = $v[$fields_fn['abstract']['id']];
			$tasks[$k]['fields'][$fields_fn['url']['id']] = $v[$fields_fn['url']['id']];
			break;
		case 'full':
			$tasks[$k]['fields'] = $v;
			break;
	}
  }
  return json_encode($tasks);
}
