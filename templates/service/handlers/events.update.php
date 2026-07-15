<?php

# 
# Update events details
#
# /events/update
#
# JSON body: 
# "data": {
#	"<id>" : {
#				<field_id_1> : <new_value>,
#				...
#				<field_id_N> : <new_value>
#			},
#	"<id_N>" : { <same as above> }
# }

function events_update_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }

  $fields = get_events_fields(); // array ( <id1> : {field_descriptor1}, <id2> : {field_descriptor2} );

  foreach($data as $event_id => $v) { // iterate over event ids
	if (!is_array($v) || empty($v)) { continue; }
	foreach($v as $field_id => $field_value) {
		$history_field = '';
		$history_to_value = '';
		$history_from_value = '';
		switch ( $fields[$field_id]['type'] ) {
			case 'string':
				$history_field = 'string';
				$history_to_value = '"'.$db->Escape($field_value).'"';
				$query = 'SELECT `value` FROM `'.$db_name.'`.`events_data_'.$history_field.'s` WHERE `events_id` = '.intval($event_id).' AND `events_fields_id` = '.intval($field_id).' LIMIT 1';
				$result = $db->Query($query);
				$history_from_value = '"'.$db->Escape($result['value'][0]).'"';
				if (!empty($result)) {
					$history_from_value = '"'.$db->Escape($result['value'][0]).'"';
				} else {
					$history_from_value = '""';
				}
				$query = 'INSERT INTO `'.$db_name.'`.`events_data_strings` (`events_id`, `events_fields_id`, `value`) VALUES ('.intval($event_id).', '.intval($field_id).', "'.$db->Escape($field_value).'") ON DUPLICATE KEY UPDATE `value` = "'.$db->Escape($field_value).'"';
				$db->Query($query);
				break;

			case 'date':
				$history_field = 'date';
				$history_to_value = '"'.$db->Escape($field_value).'"';
				$query = 'SELECT `value` FROM `'.$db_name.'`.`events_data_'.$history_field.'s` WHERE `events_id` = '.intval($event_id).' AND `events_fields_id` = '.intval($field_id).' LIMIT 1';
				$result = $db->Query($query);
				if (!empty($result)) {
					$history_from_value = '"'.$db->Escape($result['value'][0]).'"';
				} else {
					$history_from_value = '"0000-00-00 00:00:00"';
				}
				$query = 'INSERT INTO `'.$db_name.'`.`events_data_dates` (`events_id`, `events_fields_id`, `value`) VALUES ('.intval($event_id).', '.intval($field_id).', "'.$db->Escape($field_value).'") ON DUPLICATE KEY UPDATE `value` = "'.$db->Escape($field_value).'"';
				$db->Query($query);
				break;

			case 'int':
				$history_field = 'int';
				$history_to_value = intval($field_value);
				$query = 'SELECT `value` FROM `'.$db_name.'`.`events_data_'.$history_field.'s` WHERE `events_id` = '.intval($event_id).' AND `events_fields_id` = '.intval($field_id).' LIMIT 1';
				$result = $db->Query($query);
				$history_from_value = intval($result['value'][0]);
				if (!empty($result)) {
					$history_from_value = intval($result['value'][0]);
				} else {
					$history_from_value = 0;
				}
				$query = 'INSERT INTO `'.$db_name.'`.`events_data_ints` (`events_id`, `events_fields_id`, `value`) VALUES ('.intval($event_id).', '.intval($field_id).', '.intval($field_value).') ON DUPLICATE KEY UPDATE `value` = '.intval($field_value);
				$db->Query($query);
				break;

			case 'text':
				$query = 'INSERT INTO `'.$db_name.'`.`events_data_texts` (`events_id`, `events_fields_id`, `value`) VALUES ('.intval($event_id).', '.intval($field_id).', "'.$db->Escape($field_value).'") ON DUPLICATE KEY UPDATE `value` = "'.$db->Escape($field_value).'"';
				$db->Query($query);
				break;

			default:
				break;
		}
		if ( !empty($history_field) ) {
			// history support:
			$query = 'INSERT INTO `'.$db_name.'`.`events_history` (`events_id`, `events_fields_id`, date, `value_from_'.$history_field.'`, `value_to_'.$history_field.'`,`ip`,`user`) 
				VALUES ('.intval($event_id).', '.intval($field_id).', NOW(), '.$history_from_value.', '.$history_to_value.', "'.$db->Escape( get_client_ip() ).'", "'.$db->Escape( get_client_user() ).'")';
			$db->Query($query);
		}
	}
  }

  return json_encode(true);
}
