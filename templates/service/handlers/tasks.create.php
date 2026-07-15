<?php

# 
# Create task (one at a time) details: 
#	status = user status
#	field ids => fields to be inserted
#
# /tasks/create
#
# JSON body: 
# "data": {
#	"status": "active|onhold|inactive",
#	"fields": {
#				<field_id_1> : <new_value>,
#				...
#				<field_id_N> : <new_value>
#			}
# }

function tasks_create_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }


  if ( !isset($params['data']['status']) || empty($params['data']['status'])
		|| !isset($params['data']['fields']) || empty($params['data']['fields']) || !is_array($params['data']['fields'])
	) { return json_encode(false); }

  $fields = get_tasks_fields(); // array ( <id1> : {field_descriptor1}, <id2> : {field_descriptor2} );

  // create task in the `tasks` table:
  $query = 'INSERT INTO `'.$db_name.'`.`tasks` (`status`, `status_change_date`, `status_change_reason`, `last_update_date`) 
	VALUES ("'.$db->Escape($params['data']['status']).'", NOW(), "new task created", NOW() )';
  $db->Query($query);

  // populate fields in `tasks_data_dates`/_data_strings/_data_ints tables:
  $id = $db->LastID();

  if (!empty($id)) {

	foreach($params['data']['fields'] as $k => $v) {
		$query = '';
        $fixed_name = $fields[intval($k)]['name_fixed'];
		switch ($fields[$k]['type']) {
			case 'string':
				$query = 'INSERT INTO `'.$db_name.'`.`tasks_data_strings` (`tasks_id`, `tasks_fields_id`, `value`) VALUES ('.intval($id).', '.intval($k).', "'.$db->Escape($v).'")';
				break;
			case 'int':
				$query = 'INSERT INTO `'.$db_name.'`.`tasks_data_ints` (`tasks_id`, `tasks_fields_id`, `value`) VALUES ('.intval($id).', '.intval($k).', '.intval($v).')';
				break;
			case 'date':
				$query = 'INSERT INTO `'.$db_name.'`.`tasks_data_dates` (`tasks_id`, `tasks_fields_id`, `value`) VALUES ('.intval($id).', '.intval($k).', "'.$db->Escape($v).'")';
				break;
			case 'text':
				$query = 'INSERT INTO `'.$db_name.'`.`tasks_data_texts` (`tasks_id`, `tasks_fields_id`, `value`) VALUES ('.intval($id).', '.intval($k).', "'.$db->Escape($v).'")';
				break;
			default:
				break;
		}
		if (!empty($query)) {
			$db->Query($query);
		}
	}
  } else {
 	return json_encode(false);
  }

  return json_encode([ 'id' => $id ]);
}
