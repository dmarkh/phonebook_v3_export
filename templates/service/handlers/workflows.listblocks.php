<?php

#
# Get workflow blocks list:
#
# /workflows/listblocks/status:[all,active,onhold,inactive]
#

function workflows_listblocks_handler($params) {
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

  $limit_query = '';
  if ( isset($params['page']) && isset($params['rows-per-page']) ) {
	$limit_query = ' LIMIT '.intval($params['page']).','.intval($params['rows-per-page']);
  }

  $query = 'SELECT * FROM `'.$db_name.'`.`workflows_blocks` WHERE '.$status_query.' ORDER BY `weight` ASC'.$limit_query;
  $doc = $db->Query($query);

  $workflows_blocks = array();

  foreach($doc as $k => $v) {
    $workflows_blocks[$v['id']] = $v;
  }

  return json_encode($workflows_blocks);
}
