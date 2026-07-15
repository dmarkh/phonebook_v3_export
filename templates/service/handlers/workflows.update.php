<?php

# 
# Update workflow details
#
# /workflow/update
#
# JSON body:
# "data": {
#	"<id>" : {
#				<field_name_1> : <new_value>,
#				...
#				<field_name_N> : <new_value>
#			},
#	"<id_N>" : { <same as above> }
# }

function workflows_update_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( empty($params['data']) || !is_array($data) ) { return json_encode(false); }


  foreach ($data as $workflow_id => $v ) { // iterate over workflow ids

	if (!is_array($v) || empty($v)) { continue; }

	$upd = array();
	if ( isset($v['name']) ) {
		$upd[] = '`name` = "'.$db->Escape($v['name']).'"';
	}
	if ( isset($v['description']) ) {
		$upd[] = '`description` = "'.$db->Escape($v['description']).'"';
	}
	if ( isset($v['status']) ) {
		$upd[] = '`status` = "'.$db->Escape($v['status']).'"';
	}
	if ( isset($v['weight']) ) {
		$upd[] = '`weight` = '.intval($v['weight']);
	}

	if ( !empty($upd) ) {
		$query = 'UPDATE `'.$db_name.'`.`workflows` SET '.implode(',', $upd).' WHERE `id` = '.intval($workflow_id).' LIMIT 1';
		$db->Query($query);
	}
  }

  return json_encode(true);
}
