<?php

#
# Get documents list:
#
# /documents/list/status:[all,active,onhold,inactive]
#
# /documents/list/details:[compact,full]
#
# /documents/list/ids:1,2,4,5,6,7,8
#

function documents_list_handler($params) {
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

  if ( isset($params['data']) && isset($params['data']['ids']) ) {
	$status_query .= ' AND `id` IN ('.implode(',',$params['data']['ids']).')';
  }

  $details = 'full';
  if (isset($params['details']) && !empty($params['details'])) {
	$details = trim($params['details']);
  }

  $limit_query = '';
  if ( isset($params['page']) && isset($params['rows-per-page']) ) {
	$limit_query = ' LIMIT '.intval($params['page']).','.intval($params['rows-per-page']);
  }

  $query = 'SELECT * FROM `'.$db_name.'`.`documents` WHERE '.$status_query.' ORDER BY `id` DESC'.$limit_query;
  $doc = $db->Query($query);

  $documents = array();
  foreach($doc as $k => $v) {
    $documents[$v['id']]['status'] = $v['status'];
    $documents[$v['id']]['status_change_date'] = $v['status_change_date'];
    $documents[$v['id']]['status_change_reason'] = $v['status_change_reason'];
    $documents[$v['id']]['last_update'] = $v['last_update'];
  }

  $query = 'SELECT * FROM `'.$db_name.'`.`documents_fields`';
  $fields_res = $db->Query($query);
  $fields = array();
  $fields_fn = array();
  $inst_field_id = 0;
  foreach($fields_res as $k => $v) {
	$fields[$v['id']] = $v;
	$fields_fn[$v['name_fixed']] = $v;
  }

  $documents_fields = array();
  foreach(array('string','int','date','text') as $k => $v) {
  	$query = 'SELECT * FROM `'.$db_name.'`.`documents_data_'.$v.'s`';
	$res = $db->Query($query);
	if (empty($res)) continue;
	foreach($res as $k2 => $v2) {
        if ($v == 'int') { $v2['value'] = intval($v2['value']); }
        $documents_fields[$v2['documents_id']][$v2['documents_fields_id']] = $v2['value'];
	}
  }

  foreach($documents_fields as $k => $v) {
	if (!isset($documents[$k])) { continue; }
	switch ($details) {
		case 'compact':
			$documents[$k]['fields'][$fields_fn['title']['id']] = $v[$fields_fn['title']['id']];
			$documents[$k]['fields'][$fields_fn['abstract']['id']] = $v[$fields_fn['abstract']['id']];
			$documents[$k]['fields'][$fields_fn['url']['id']] = $v[$fields_fn['url']['id']];
			break;
		case 'full':
			$documents[$k]['fields'] = $v;
			break;
	}
  }

  array_reverse( $documents, true );

  return json_encode($documents);
}
