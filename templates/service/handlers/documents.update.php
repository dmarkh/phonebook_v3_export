<?php

# 
# Update documents details
#
# /documents/update
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

function documents_update_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }

  $fields = get_documents_fields(); // array ( <id1> : {field_descriptor1}, <id2> : {field_descriptor2} );

  foreach($data as $document_id => $v) { // iterate over document ids
	if (!is_array($v) || empty($v)) { continue; }

	foreach($v as $field_id => $field_value) {
		$history_field = '';
		$history_to_value = '';
		$history_from_value = '';
		switch ( $fields[$field_id]['type'] ) {
			case 'string':
				$history_field = 'string';
				$history_to_value = '"'.$db->Escape($field_value).'"';
				$query = 'SELECT `value` FROM `'.$db_name.'`.`documents_data_'.$history_field.'s` WHERE `documents_id` = '.intval($document_id).' AND `documents_fields_id` = '.intval($field_id).' LIMIT 1';
				$result = $db->Query($query);
				$history_from_value = '"'.$db->Escape($result['value'][0]).'"';
				if (!empty($result)) {
					$history_from_value = '"'.$db->Escape($result['value'][0]).'"';
				} else {
					$history_from_value = '""';
				}
				$query = 'INSERT INTO `'.$db_name.'`.`documents_data_strings` (`documents_id`, `documents_fields_id`, `value`) VALUES ('.intval($document_id).', '.intval($field_id).', "'.$db->Escape($field_value).'") ON DUPLICATE KEY UPDATE `value` = "'.$db->Escape($field_value).'"';
				$db->Query($query);
				break;

			case 'date':
				$history_field = 'date';
				$history_to_value = '"'.$db->Escape($field_value).'"';
				$query = 'SELECT `value` FROM `'.$db_name.'`.`documents_data_'.$history_field.'s` WHERE `documents_id` = '.intval($document_id).' AND `documents_fields_id` = '.intval($field_id).' LIMIT 1';
				$result = $db->Query($query);
				if (!empty($result)) {
					$history_from_value = '"'.$db->Escape($result['value'][0]).'"';
				} else {
					$history_from_value = '"0000-00-00 00:00:00"';
				}
				$query = 'INSERT INTO `'.$db_name.'`.`documents_data_dates` (`documents_id`, `documents_fields_id`, `value`) VALUES ('.intval($document_id).', '.intval($field_id).', "'.$db->Escape($field_value).'") ON DUPLICATE KEY UPDATE `value` = "'.$db->Escape($field_value).'"';
				$db->Query($query);
				break;

			case 'int':
				$history_field = 'int';
				$history_to_value = intval($field_value);
				$query = 'SELECT `value` FROM `'.$db_name.'`.`documents_data_'.$history_field.'s` WHERE `documents_id` = '.intval($document_id).' AND `documents_fields_id` = '.intval($field_id).' LIMIT 1';
				$result = $db->Query($query);
				$history_from_value = intval($result['value'][0]);
				if (!empty($result)) {
					$history_from_value = intval($result['value'][0]);
				} else {
					$history_from_value = 0;
				}
				$query = 'INSERT INTO `'.$db_name.'`.`documents_data_ints` (`documents_id`, `documents_fields_id`, `value`) VALUES ('.intval($document_id).', '.intval($field_id).', '.intval($field_value).') ON DUPLICATE KEY UPDATE `value` = '.intval($field_value);
				$db->Query($query);
				break;

			case 'text':
				$query = 'INSERT INTO `'.$db_name.'`.`documents_data_texts` (`documents_id`, `documents_fields_id`, `value`) VALUES ('.intval($document_id).', '.intval($field_id).', "'.$db->Escape($field_value).'") ON DUPLICATE KEY UPDATE `value` = "'.$db->Escape($field_value).'"';
				$db->Query($query);
				break;

			default:
				break;
		}

		foreach($v as $field_id => $field_value) {
			$fixed_name = $fields[intval($field_id)]['name_fixed'];
			switch( $fixed_name ) {
				case 'group_id':
					$query = 'DELETE FROM `'.$db_name.'`.`documents_groups` WHERE `doc_id` = '.intval($document_id);
					$db->Query($query);
					$query = 'INSERT INTO `'.$db_name.'`.`documents_groups` (`doc_id`, `grp_id`) VALUES ('.intval($document_id).', '.intval($field_value).')';
					$db->Query($query);
					break;
				case 'member_ids':
					$query = 'DELETE FROM `'.$db_name.'`.`documents_authors` WHERE `doc_id` = '.intval($document_id);
               		$db->Query( $query );
            		$mem = explode(',', $field_value);
		            foreach ($mem as $km => $vm ) {
        		        $query = 'INSERT INTO `'.$db_name.'`.`documents_authors` (`doc_id`, `mem_id`) VALUES ('.intval($document_id).', '.intval($vm).')';
                		$db->Query( $query );
		            }
					break;
				case 'reviewer_ids':
					$query = 'DELETE FROM `'.$db_name.'`.`documents_reviewers` WHERE `doc_id` = '.intval($document_id);
               		$db->Query( $query );
            		$mem = explode(',', $field_value);
		            foreach ($mem as $km => $vm ) {
        		        $query = 'INSERT INTO `'.$db_name.'`.`documents_reviewers` (`doc_id`, `mem_id`) VALUES ('.intval($document_id).', '.intval($vm).')';
                		$db->Query( $query );
		            }
					break;
				default:
					break;
			}
		}

		if ( !empty($history_field) ) {
			// history support:
			$query = 'INSERT INTO `'.$db_name.'`.`documents_history` (`documents_id`, `documents_fields_id`, date, `value_from_'.$history_field.'`, `value_to_'.$history_field.'`,`ip`,`user`) 
				VALUES ('.intval($document_id).', '.intval($field_id).', NOW(), '.$history_from_value.', '.$history_to_value.', "'.$db->Escape( get_client_ip() ).'", "'.$db->Escape( get_client_user() ).'")';
			$db->Query($query);
		}
	}
  }

  return json_encode(true);
}
