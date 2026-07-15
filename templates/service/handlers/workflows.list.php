<?php

#
# Get workflows list:
#
# /workflows/list/status:[all,active,onhold,inactive]
#

function workflows_list_handler($params) {
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

  $query = 'SELECT * FROM `'.$db_name.'`.`workflows` WHERE '.$status_query.' ORDER BY `weight` ASC,`id` DESC'.$limit_query;
  $workflows = $db->Query($query);

  return json_encode($workflows);
}
