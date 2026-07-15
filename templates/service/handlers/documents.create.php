<?php

# 
# Create document (one at a time) details: 
#	status = user status
#	field ids => fields to be inserted
#
# /documents/create
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

function documents_create_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }


  if ( !isset($params['data']['status']) || empty($params['data']['status'])
		|| !isset($params['data']['fields']) || empty($params['data']['fields']) || !is_array($params['data']['fields'])
	) { return json_encode(false); }

  $fields = get_documents_fields(); // array ( <id1> : {field_descriptor1}, <id2> : {field_descriptor2} );

  // create document in the `documents` table:
  $query = 'INSERT INTO `'.$db_name.'`.`documents` (`status`, `status_change_date`, `status_change_reason`, `last_update_date`) 
	VALUES ("'.$db->Escape($params['data']['status']).'", NOW(), "new document created", NOW() )';
  $db->Query($query);

  // populate fields in `documents_data_dates`/_data_strings/_data_ints tables:
  $id = $db->LastID();

  if (!empty($id)) {

	foreach($params['data']['fields'] as $k => $v) {
		$query = '';
        $fixed_name = $fields[intval($k)]['name_fixed'];
		switch ($fields[$k]['type']) {
			case 'string':
				$query = 'INSERT INTO `'.$db_name.'`.`documents_data_strings` (`documents_id`, `documents_fields_id`, `value`) VALUES ('.intval($id).', '.intval($k).', "'.$db->Escape($v).'")';
				break;
			case 'int':
				$query = 'INSERT INTO `'.$db_name.'`.`documents_data_ints` (`documents_id`, `documents_fields_id`, `value`) VALUES ('.intval($id).', '.intval($k).', '.intval($v).')';
				break;
			case 'date':
				$query = 'INSERT INTO `'.$db_name.'`.`documents_data_dates` (`documents_id`, `documents_fields_id`, `value`) VALUES ('.intval($id).', '.intval($k).', "'.$db->Escape($v).'")';
				break;
			case 'text':
				$query = 'INSERT INTO `'.$db_name.'`.`documents_data_texts` (`documents_id`, `documents_fields_id`, `value`) VALUES ('.intval($id).', '.intval($k).', "'.$db->Escape($v).'")';
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

  foreach( $params['data']['fields'] as $k => $v ) {
	$fixed_name = $fields[intval($k)]['name_fixed'];
	switch( $fixed_name ) {
		case 'author_id':
			$query = 'INSERT INTO `'.$db_name.'`.`documents_owners` (`doc_id`, `mem_id`) VALUES ('.intval($id).', '.intval($v).')';
			$db->Query( $query );
			break;
		case 'member_ids':
			$mem = explode(',', $v);
			foreach ($mem as $km => $vm ) {
				$query = 'INSERT INTO `'.$db_name.'`.`documents_authors` (`doc_id`, `mem_id`) VALUES ('.intval($id).', '.intval($vm).')';
				$db->Query( $query );
			}
			break;
		case 'reviewer_ids':
			$mem = explode(',', $v);
			foreach ($mem as $km => $vm ) {
				$query = 'INSERT INTO `'.$db_name.'`.`documents_reviewers` (`doc_id`, `mem_id`) VALUES ('.intval($id).', '.intval($vm).')';
				$db->Query( $query );
			}
			break;
		case 'group_id':
			$query = 'INSERT INTO `'.$db_name.'`.`documents_groups` (`doc_id`, `grp_id`) VALUES ('.intval($id).', '.intval($v).')';
			$db->Query($query);
			break;
		default:
			break;
	}
  }

  return json_encode([ 'id' => $id ]);
}
