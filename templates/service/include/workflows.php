<?php

//------------------------------- math number scrambler -------------------------------------------

function wfl_num_to_str( $num, $alphabet = 'BCDFGHJKLMNPQRSTVWXYZ', $multiplier = 5011, $modulus = 131807699, $minLength = 6 ) {
	// converts numbers like 1234 into strings like CNVSSY, so we can form a DocID like "ePIC-PUB-2025-CNVSSY"
    $scrambledNum = ($num * $multiplier) % $modulus;
    $base = strlen($alphabet);
    $result = '';
    if ($scrambledNum === 0) {
        return str_repeat($alphabet[0], $minLength);
    }
    $currentNum = $scrambledNum;
    while ($currentNum > 0) {
        $remainder = $currentNum % $base;
        $result = $alphabet[$remainder] . $result;
        $currentNum = floor($currentNum / $base);
    }
    while (strlen($result) < $minLength) {
        $result = $alphabet[0] . $result;
    }
    return $result;
}

//------------------------------- invenio ---------------------------------------------------------

function invenio_get_community_id_from_slug( $community_slug ) {
	$cnf =& ServiceConfig::Instance();
    $url = $cnf->Get('settings','invenio-url');
    $api_token = $cnf->Get('settings', 'invenio-token');
    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer ".$api_token
    ];

	$api_url = $url.'/api/communities/'.$community_slug;
    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ( $status != 200 && $status != 201 ) {
        return false; // bad request
    }

    $response = json_decode( $json_response, true );
	if ( $response && !empty($response) && $response['id'] ) {
		return $response['id'];
	}

    return true;
}

function invenio_approve_request( $url ) {
	$cnf =& ServiceConfig::Instance();
    $api_token = $cnf->Get('settings', 'invenio-token'); // 'sy85iGakzBxzXo8yg6zFiuuo5bHOi4xtp3EmGcsfdRtr505S0X45w8LcXOTz';

    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer ".$api_token
    ];

    $data_json = '{"payload":{"content":"accepted","format":"html"}}';

    $curl = curl_init( $url );
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);

    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ( $status != 200 && $status != 201 ) {
        return false; // bad request
    }

    $response = json_decode( $json_response, true );
    return $response;
}

function invenio_get_communities_for_record( $record_id ) {
	$cnf =& ServiceConfig::Instance();
    $url = $cnf->Get('settings','invenio-url');
    $api_token = $cnf->Get('settings', 'invenio-token');

    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer ".$api_token
    ];

	$api_url = $url.'/api/records/'.$record_id.'/communities';
    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ( $status != 200 && $status != 201 ) {
        return false; // bad request
    }

    $response = json_decode( $json_response, true );
	if ( $response && !empty($response) && $response['hits'] && $response['hits']['hits'] ) {
		$communities = [];
		foreach($response['hits']['hits'] as $k => $v ) {
			$communities[ $v['slug'] ] = $v['id'];
		}
		return $communities;
	}

	return false;
}

function invenio_remove_community_from_record( $record_id, $community_id ) { // $community_id = hex version!
	$cnf =& ServiceConfig::Instance();
    $url = $cnf->Get('settings','invenio-url');
    $api_token = $cnf->Get('settings', 'invenio-token');

    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer ".$api_token
    ];

    $api_url = $url.'/api/records/'.$record_id.'/communities';
    $data = [
        'communities' => [
            [ 'id' => $community_id ]
        ]
    ];

    $data_json = json_encode($data);

    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);

    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ( $status != 200 && $status != 201 ) {
        return false; // bad request
    }

    return true;
}

function invenio_add_community_to_record( $record_id, $community_id ) { // community_id = hex version
	$cnf =& ServiceConfig::Instance();
    $url = $cnf->Get('settings','invenio-url');
    $api_token = $cnf->Get('settings', 'invenio-token');

    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer ".$api_token
    ];
    $api_url = $url.'/api/records/'.$record_id.'/communities';
    $data = [
        'communities' => [
            [ 'id' => $community_id ]
        ]
    ];

    $data_json = json_encode($data);

    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);

    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ( $status != 200 && $status != 201 ) {
        echo '[add community] return status != 200, it is: '.$status."\n";
        return false; // bad request
    }

    $response = json_decode( $json_response, true );
    if ( $response['processed'] ) {
		if ( !empty($response['processed'][0]['request']['links']['actions']['accept']) ) {
        	$approve_url = $response['processed'][0]['request']['links']['actions']['accept'];
	        if ( !empty($approve_url) ) {
    	        $rc = invenio_approve_request( $approve_url );
				if ( !$rc) { return false; }
        	}
		}
    } else {
        return false;
    }

    return true;
}

function invenio_switch_record_to_public( $record_id ) {
	$cnf =& ServiceConfig::Instance();
	$public_community_slug = strtolower( $cnf->Get('settings','collaboration') ).'-public';
	$public_community_id = invenio_get_community_id_from_slug( $public_community_slug );

	if ( empty($public_community_id) ) { return false; }

	$rc = invenio_add_community_to_record( $record_id, $public_community_id );
	if ( !$rc ) { return false; }

	$comms = invenio_get_communities_for_record( $record_id );
	foreach( $comms as $k => $v ) {
		if ( $k !== $public_community_slug ) {
			$rc = invenio_remove_community_from_record( $record_id, $v );
		}
	}

	return true;
}

function invenio_switch_record_to_internal( $record_id ) {
	$cnf =& ServiceConfig::Instance();
	$internal_community_slug = strtolower( $cnf->Get('settings','collaboration') ).'-internal';
	$internal_community_id = invenio_get_community_id_from_slug( $internal_community_slug );
	if ( empty($internal_community_id) ) {
		$internal_community_slug = strtolower( $cnf->Get('settings','collaboration') ).'-private';
		$internal_community_id = invenio_get_community_id_from_slug( $internal_community_slug );
	}
	if ( empty($internal_community_id) ) { return false; }

	$rc = invenio_add_community_to_record( $record_id, $internal_community_id );
	if ( !$rc ) { return false; }

	$comms = invenio_get_communities_for_record( $record_id );
	foreach( $comms as $k => $v ) {
		if ( $k !== $internal_community_slug ) {
			$rc = invenio_remove_community_from_record( $record_id, $v );
		}
	}

	return true;
}

function invenio_switch_record_to_group( $record_id, $group_name ) {

	$group_name = trim($group_name);
	$group_name = preg_replace("/[^A-Za-z0-9]+/", "_", $group_name);
	$group_name = trim($group_name, '_');

	$cnf =& ServiceConfig::Instance();
	$community_slug = strtolower( $cnf->Get('settings','collaboration').'-'.$group_name );
	$community_id = invenio_get_community_id_from_slug( $community_slug );

	if ( empty($community_id) ) {
		return false;
	}

	$rc = invenio_add_community_to_record( $record_id, $community_id );
	if ( !$rc ) {
		return false;
	}

	$comms = invenio_get_communities_for_record( $record_id );
	foreach( $comms as $k => $v ) {
		if ( $k !== $community_slug ) {
			$rc = invenio_remove_community_from_record( $record_id, $v );
		}
	}

	return true;
}

//------------------------------- utils ---------------------------------------------------------

function wfl_send_email( $to, $subject, $message ) {
	$headers = array(
		'From: no-reply@'. gethostname(),
	    'Reply-To: no-reply@'. gethostname(),
	    'X-Mailer: PHP/' . phpversion()
	);
	return mail( $to, $subject, $message, implode("\r\n", $headers) );
}

function wfl_close( $document_id, $workflow_id ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'UPDATE `'.$db_name.'`.`workflows_documents` SET `status` = 1 WHERE `document_id` = '.intval($document_id).' AND `workflow_id` = '.intval($workflow_id).' LIMIT 1';
	$db->Query($query);
}

function wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'INSERT INTO `'.$db_name.'`.`workflows_progress` (`workflow_id`, `document_id`, `step_id`, `member_id`, `member_name`, `operation`, `metadata`)'
		.' VALUES ('.intval($workflow_id).', '.intval($document_id).', '.intval($step_id).', '.intval($member_id)
		.', "'.$db->Escape($member_name).'", '
		.'"'.$db->Escape($operation).'", '
		.'"'. $db->Escape($metadata).'")';

	$db->Query($query);
	$id = $db->LastID();
	if ( empty($id) ) {
		return false;
	}
	return true;
}

function wfl_get_map( $document_id ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'SELECT * FROM `'.$db_name.'`.`workflows_documents` WHERE `document_id` = '.intval($document_id).' LIMIT 1';
	$docb = $db->Query($query);
	if ( empty($docb) ) { return false; }
	return $docb[0];
}

function wfl_get_config( $workflow_id ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'SELECT * FROM `'.$db_name.'`.`workflows_configs` WHERE `workflow_id` = '.intval($workflow_id).' ORDER BY `step_id` ASC';
	$doc = $db->Query($query);
	return $doc;
}

function wfl_get_blocks() {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'SELECT * FROM `'.$db_name.'`.`workflows_blocks` WHERE `status` = "active" ORDER BY `weight` ASC';
	$doc = $db->Query($query);
	return $doc;
}

function wfl_get_progress( $document_id, $workflow_id ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'SELECT * FROM `'.$db_name.'`.`workflows_progress` WHERE `document_id` = '.intval( $document_id ).' AND `workflow_id` = '.intval( $workflow_id ).' ORDER BY `id` ASC';
	$docb = $db->Query($query);
	return $docb;
}


function wfl_update_step( $document_id, $workflow_id, $step_id ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'UPDATE `'.$db_name.'`.`workflows_documents` SET `current_step_id` = '.intval($step_id).' WHERE `document_id` = '.intval($document_id).' AND `workflow_id` = '.intval($workflow_id).' LIMIT 1';
	$db->Query($query);
	return wfl_add_progress( $document_id, $workflow_id, $step_id, 0, 'WORKFLOW-ENGINE', 'step-started', 'starting step: '.$step_id );
}

function wfl_rollback_progress( $document_id, $workflow_id, $step_id ) {
	// roll-back to previous step
    $blocks = wfl_get_blocks();
	$blocks_lookup = array();
	foreach ( $blocks as $k => $v ) {   // setup [id] => [block] lookup map
		$blocks_lookup[ $v['id'] ] = $v;
	}
	$config = wfl_get_config( $workflow_id );
	$updated = false;
	for ( $i = $step_id - 1; $i >= 1; $i++ ) {
		if ( !empty( $blocks_lookup[ $config[$i - 1]['block_id'] ] ) ) {
			$block = $blocks_lookup[ $config[$i - 1]['block_id'] ];
			if ( $block['block_type_id'] >= 30 || $block['block_type_id'] <= 36 ) {
				wfl_update_step( $document_id, $workflow_id, $i );
				$updated = true;
				break;
			}
		}
	}
	if ( !$updated ) {
		wfl_update_step( $document_id, $workflow_id, 1 );
    }
	return true;
}

function wfl_get_document_owner_id( $document_id ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'SELECT * FROM `'.$db_name.'`.`documents_owners` WHERE `doc_id` = '.intval($document_id).' LIMIT 1';
	$doc = $db->Query($query);
	if ( empty($doc) ) { return false; }
	return intval( $doc[0]['mem_id'] );
}

function wfl_get_document_author_ids( $document_id ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'SELECT mem_id FROM `'.$db_name.'`.`documents_authors` WHERE `doc_id` = '.intval($document_id);
	$doc = $db->Query($query);
	if ( empty($doc) || empty($doc['mem_id']) ) { return false; }
	return $doc['mem_id'];
}

function wfl_get_document_reviewer_ids( $document_id ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'SELECT mem_id FROM `'.$db_name.'`.`documents_reviewers` WHERE `doc_id` = '.intval($document_id);
	$doc = $db->Query($query);
	if ( empty($doc) || empty($doc['mem_id']) ) { return false; }
	return $doc['mem_id'];
}


function wfl_get_document_field_id_by_fixed ( $name_fixed ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'SELECT `id` FROM `'.$db_name.'`.`documents_fields` WHERE `name_fixed` = "'.$db->Escape( $name_fixed ).'" LIMIT 1';
	$doc = $db->Query($query);
	if ( empty($doc) ) { return false; }
	return intval( $doc['id'][0] );
}

function wfl_get_member_field_id_by_fixed ( $name_fixed ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'SELECT `id` FROM `'.$db_name.'`.`members_fields` WHERE `name_fixed` = "'.$db->Escape( $name_fixed ).'" LIMIT 1';
	$doc = $db->Query($query);
	if ( empty($doc) ) { return false; }
	return intval( $doc['id'][0] );
}

function wfl_get_document_field_by_id ( $document_id, $field_id ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'SELECT `value` FROM `'.$db_name.'`.`documents_data_strings` where `documents_id` = '.intval( $document_id ).' AND `documents_fields_id` = '.intval( $field_id );
	$doc = $db->Query($query);
	if ( empty($doc) ) { return false; }
	return $doc['value'][0];
}

function wfl_get_document_int_field_by_id ( $document_id, $field_id ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'SELECT `value` FROM `'.$db_name.'`.`documents_data_ints` where `documents_id` = '.intval( $document_id ).' AND `documents_fields_id` = '.intval( $field_id );
	$doc = $db->Query($query);
	if ( empty($doc) ) { return false; }
	return $doc['value'][0];
}

function wfl_get_member_field_by_id ( $member_id, $field_id ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'SELECT `value` FROM `'.$db_name.'`.`members_data_strings` where `members_id` = '.intval( $member_id ).' AND `members_fields_id` = '.intval( $field_id );
	$doc = $db->Query($query);
	if ( empty($doc) ) { return false; }
	return $doc['value'][0];
}

function wfl_get_member_field( $member_id, $name ) {
	$field_id = wfl_get_member_field_id_by_fixed( $name );
	if ( !$field_id ) { return false; }
	$value = wfl_get_member_field_by_id( $member_id, $field_id );
	if ( !$value ) { return false; }
	return $value;
}

function wfl_get_document_field( $document_id, $name ) {
	$field_id = wfl_get_document_field_id_by_fixed( $name );
	if ( !$field_id ) { return false; }
	$value = wfl_get_document_field_by_id( $document_id, $field_id );
	if ( !$value ) { return false; }
	return $value;
}

function wfl_get_member_name_email( $member_id ) {
	$email = wfl_get_member_field( $member_id, 'email' );
	$name_first = wfl_get_member_field( $member_id, 'name_first' );
	$name_last = wfl_get_member_field( $member_id, 'name_last' );
	$output = array( 'email' => $email, 'name_first' => $name_first, 'name_last' => $name_last );
	return $output;
}

function wfl_get_group_by_id( $group_id ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'SELECT `id`, `name`, `email` FROM `'.$db_name.'`.`groups` where `id` = '.intval( $group_id ).' LIMIT 1';
	$res = $db->Query($query);
	if ( empty($res) ) { return false; }
	return $res[0];
}

function wfl_get_group_managers( $group_id ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');

    $query = 'SELECT gm.`member_id` AS member_id FROM `'.$db_name.'`.`groups_members` gm, `'.$db_name.'`.`members` m, `'.$db_name.'`.`groups_roles` gr WHERE gm.group_id = '
        .intval( $group_id ).' AND gm.member_id = m.id AND m.status = "active" AND gm.`role_id` > 0 AND gm.`role_id` = gr.`id` AND LOWER(gr.role) != "member"';

	$res = $db->Query($query);
	if ( empty($res) ) { return false; }
	return $res['member_id'];
}

function wfl_get_group_members_by_roles( $group_id, $role_ids ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	// $query = 'SELECT `member_id` FROM `'.$db_name.'`.`groups_members` where `group_id` = '.intval( $group_id ).' AND `role_id` IN ('.$db->Escape( $role_ids ).')';
	$query = 'SELECT gm.`member_id` FROM `'.$db_name.'`.`groups_members` gm, `members` m where gm.group_id = '.intval( $group_id ).' AND gm.member_id = m.id AND m.status = "active" AND gm.`role_id` IN ('.$db->Escape( $role_ids ).')';
	$res = $db->Query($query);
	if ( empty($res) ) { return false; }
	return $res['member_id'];
}

function wfl_get_parent_group_from_group( $group_id ) {
	$group_id = intval($group_id);
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'SELECT `parent` FROM `'.$db_name.'`.`groups` where `id` = '.intval( $group_id );
	$res = $db->Query($query);
	return intval($res['parent'][0]);
}

function wfl_get_document_origgroup( $document_id ) {
	$group_field_id = wfl_get_document_field_id_by_fixed ( 'group_id' );
	$group_id = wfl_get_document_int_field_by_id ( $document_id, $group_field_id );
	if ( empty($group_id) ) { return false; } // orig group is not set??
	$group = wfl_get_group_by_id( $group_id );
	return $group;
}

function wfl_get_document_parentgroup( $document_id ) {
	$group_field_id = wfl_get_document_field_id_by_fixed ( 'group_id' );
	$group_id = intval( wfl_get_document_int_field_by_id ( $document_id, $group_field_id ) );
	if ( empty($group_id) ) { return false; } // orig group is not set
	$parentgroup_id = wfl_get_parent_group_from_group( $group_id );
	if ( empty($parentgroup_id) ) { return false; }
	$parentgroup = wfl_get_group_by_id( $parentgroup_id );
	if ( empty($parentgroup) ) { return false; }
	return $parentgroup;
}

// email to owner
function wfl_email_to_owner( $document_id, $subject, $message ) {
	if ( empty($subject) || empty($message) ) { return false; }

	$owner_id = wfl_get_document_owner_id( $document_id );
	if ( empty($owner_id) ) { return false; }
	$owner = wfl_get_member_name_email( $owner_id );
	if ( empty($owner) || empty($owner['email']) ) { return false; }

    $to = '"'.$owner['name_first'].' '.$owner['name_last'].'" <'.$owner['email'].'>';
    if ( empty($to) ) { return false; }

	wfl_send_email( $to, $subject, $message );

	return true;
}

//------------------------------- actions ---------------------------------------------------------

// action 10
function wfl_action_notify_owner( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();

	// prepare document data:

	$document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/comments';
	$document_title = wfl_get_document_field( $document_id, 'title' );

	$owner_id = wfl_get_document_owner_id( $document_id );
	if ( !$owner_id ) { return false; }

	$owner = wfl_get_member_name_email( $owner_id );
	if ( empty($owner) ) { return false; }

	// email to owner:

	$to = '"'.$owner['name_first'].' '.$owner['name_last'].'" <'.$owner['email'].'>';
	if ( empty($to) ) { return false; }

	$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] comment on document: '.$document_title;
	$message = 'Dear '.$owner['name_first'].' '.$owner['name_last']."\n\n"
		.'The following document is available for comments: '."\n"
		.$document_title."\n"
		.$document_url."\n\n"
		.'Automated CRISP Workflow Engine'."\n"
		.'Please do not reply to this email'."\n";

	$rc = wfl_send_email( $to, $subject, $message );

	// update workflow action log

	if ( $rc ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[owner] email notification sent to '.$to;
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
	}

	return $rc;
}

// action 11
function wfl_action_notify_authors( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();

	// prepare document data:

	$document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/comments';
	$document_title = wfl_get_document_field( $document_id, 'title' );

	$author_ids = wfl_get_document_author_ids( $document_id );
	if ( empty($author_ids) || !$author_ids ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[authors] WARNING: document has no authors! Please set Authors to proceed further.';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false; // document has no authors, so there is no-one to notify
	}

	$to = array();
	foreach( $author_ids as $k => $v ) {
		$author = wfl_get_member_name_email( intval($v) );
		if ( !empty($author) ) {
			$to[] = '"'.$author['name_first'].' '.$author['name_last'].'" <'.$author['email'].'>';
		}
	}

	// email to authors
	$to = implode(', ', $to);
	if ( empty($to) ) { return false; }

	$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] comment on document: '.$document_title;

	$message = 'Dear Authors,'."\n\n"
		.'The following document is available for comments: '."\n"
		.$document_title."\n"
		.$document_url."\n\n"
		.'Automated CRISP Workflow Engine'."\n"
		.'Please do not reply to this email'."\n";

	$rc = wfl_send_email( $to, $subject, $message );

	// update workflow action log

	if ( $rc ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[authors] email notification sent to '.$to;
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
	}

	return $rc;
}

// action 12
function wfl_action_notify_reviewers( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();

	// prepare document data:

	$document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/comments';
	$document_title = wfl_get_document_field( $document_id, 'title' );

	$reviewer_ids = wfl_get_document_reviewer_ids( $document_id );
	if ( empty($reviewer_ids) || !$reviewer_ids ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[reviewers] WARNING: document has no reviewers! Please assign reviewers to proceed further.';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false; // document has no reviewers, so there is no-one to notify
	}

	$to = array();
	foreach( $reviewer_ids as $k => $v ) {
		$reviewer = wfl_get_member_name_email( intval($v) );
		if ( !empty($reviewer) ) {
			$to[] = '"'.$reviewer['name_first'].' '.$reviewer['name_last'].'" <'.$reviewer['email'].'>';
		}
	}

	// email to reviewers
	$to = implode(', ', $to);
	if ( empty($to) ) { return false; } // bad list of members

	$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] comment on document: '.$document_title;

	$message = 'Dear Reviewers,'."\n\n"
		.'The following document is available for comments: '."\n"
		.$document_title."\n"
		.$document_url."\n\n"
		.'Automated CRISP Workflow Engine'."\n"
		.'Please do not reply to this email'."\n";

	$rc = wfl_send_email( $to, $subject, $message );

	// update workflow action log

	if ( $rc ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[reviewers] email notification sent to '.$to;
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
	}

	return $rc;
}

// action 13
function wfl_action_notify_members( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();

	// prepare document data:

	$document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/comments';
	$document_title = wfl_get_document_field( $document_id, 'title' );

	$member_ids = $current_block['member_ids'];

	if ( empty($member_ids) || !$member_ids ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[members] ERROR: workflow block has no members assigned. Please notify the Phonebook Admin.';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false; // document has no members assigned, so there is no-one to notify
	}

	$member_ids = explode(',', $member_ids);

	$to = array();
	foreach( $member_ids as $k => $v ) {
		$member = wfl_get_member_name_email( intval($v) );
		if ( !empty($member) ) {
			$to[] = '"'.$member['name_first'].' '.$member['name_last'].'" <'.$member['email'].'>';
		}
	}

	// email to members
	$to = implode(', ', $to);
	if ( empty($to) ) { return false; } // bad list of members

	$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] comment on document: '.$document_title;

	$message = 'Dear Collaboration Members,'."\n\n"
		.'The following document is available for comments: '."\n"
		.$document_title."\n"
		.$document_url."\n\n"
		.'Automated CRISP Workflow Engine'."\n"
		.'Please do not reply to this email'."\n";

	$rc = wfl_send_email( $to, $subject, $message );

	// update workflow action log

	if ( $rc ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[members] email notification sent to '.$to;
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
	}

	return $rc;
}

// action 14
function wfl_action_notify_origgroup( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();

	// prepare document data:

	$document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/comments';
	$document_title = wfl_get_document_field( $document_id, 'title' );

	$group = wfl_get_document_origgroup( $document_id );

	if ( empty($group) ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-maillist';
		$metadata = '[orig.group] WARNING: document has no orig.group. Please assign the Originating Group to the document to proceed further.';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	// email to group
	$to = '"'.$group['name'].'" <'.$group['email'].'>';
	if ( empty($to) ) { return false; } // bad group email

	$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] comment on document: '.$document_title;

	$message = 'Dear Members of the Group: '.$group['name'].','."\n\n"
		.'The following document is available for comments: '."\n"
		.$document_title."\n"
		.$document_url."\n\n"
		.'Automated CRISP Workflow Engine'."\n"
		.'Please do not reply to this email'."\n";

	$rc = wfl_send_email( $to, $subject, $message );

	// update workflow action log
	if ( $rc ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-maillist';
		$metadata = '[orig.group] email notification sent to '.$to;
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
	}

	return $rc;
}

// action 18
function wfl_action_notify_parentgroup( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();

	// prepare document data:

	$document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/comments';
	$document_title = wfl_get_document_field( $document_id, 'title' );

	$group = wfl_get_document_parentgroup( $document_id );

	if ( empty($group) ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-maillist';
		$metadata = '[parent.group] WARNING: document has no parent.group. Please assign the Originating Group that has Parent Group to the document to proceed further.';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	// email to group
	$to = '"'.$group['name'].'" <'.$group['email'].'>';
	if ( empty($to) ) { return false; } // bad group email

	$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] comment on document: '.$document_title;

	$message = 'Dear Members of the Group: '.$group['name'].','."\n\n"
		.'The following document is available for comments: '."\n"
		.$document_title."\n"
		.$document_url."\n\n"
		.'Automated CRISP Workflow Engine'."\n"
		.'Please do not reply to this email'."\n";

	$rc = wfl_send_email( $to, $subject, $message );

	// update workflow action log
	if ( $rc ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-maillist';
		$metadata = '[parent.group] email notification sent to '.$to;
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
	}

	return $rc;
}

// action 15
function wfl_action_notify_origgroup_management( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();

	// prepare document data:
	$document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/comments';
	$document_title = wfl_get_document_field( $document_id, 'title' );

	$group = wfl_get_document_origgroup( $document_id );

	if ( empty($group) ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[orig.group management] WARNING: document has no orig.group! Please assign the Originating Group to the document to proceed further.';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	$member_ids = wfl_get_group_managers( $group['id'] );

	if ( empty($member_ids) ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[orig.group management] ERROR: originating group has no managers. Cannot proceed.';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}


	$to = array();
	foreach( $member_ids as $k => $v ) {
		$member = wfl_get_member_name_email( intval($v) );
		if ( !empty($member) ) {
			$to[] = '"'.$member['name_first'].' '.$member['name_last'].'" <'.$member['email'].'>';
		}
	}

	// email to members
	$to = implode(', ', $to);
	if ( empty($to) ) { return false; } // bad list of members

	$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] comment on document: '.$document_title;

	$message = 'Dear Managers of the Group: '.$group['name'].','."\n\n"
		.'The following document is available for comments: '."\n"
		.$document_title."\n"
		.$document_url."\n\n"
		.'Automated CRISP Workflow Engine'."\n"
		.'Please do not reply to this email'."\n";

	$rc = wfl_send_email( $to, $subject, $message );

	// update workflow action log
	if ( $rc ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[orig.group management] email notification sent to '.$to;
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
	}

	return $rc;
}

// action 19
function wfl_action_notify_parentgroup_management( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();
	$rc = false;

	// prepare document data:
	$document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/comments';
	$document_title = wfl_get_document_field( $document_id, 'title' );

	$group = wfl_get_document_parentgroup( $document_id );

	if ( empty($group) ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[parent.group management] WARNING: document has no parent.group! Please assign the Originating Group that has Parent Group to the document to proceed further.';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	$member_ids = wfl_get_group_managers( $group['id'] );

	if ( empty($member_ids) ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[parent.group management] ERROR: parent group has no managers. Cannot proceed.';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	$to = array();
	foreach( $member_ids as $k => $v ) {
		$member = wfl_get_member_name_email( intval($v) );
		if ( !empty($member) ) {
			$to[] = '"'.$member['name_first'].' '.$member['name_last'].'" <'.$member['email'].'>';
		}
	}

	// email to members
	$to = implode(', ', $to);
	if ( empty($to) ) { return false; } // bad list of members

	$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] comment on document: '.$document_title;

	$message = 'Dear Managers of the Group: '.$group['name'].','."\n\n"
		.'The following document is available for comments: '."\n"
		.$document_title."\n"
		.$document_url."\n\n"
		.'Automated CRISP Workflow Engine'."\n"
		.'Please do not reply to this email'."\n";

	$rc = wfl_send_email( $to, $subject, $message );

	// update workflow action log
	if ( $rc ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[parent.group management] email notification sent to '.$to;
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
	}

	return $rc;
}

// action 16
function wfl_action_notify_group( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();

	// prepare document data:

	$document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/comments';
	$document_title = wfl_get_document_field( $document_id, 'title' );

	$group_id = intval($current_block['group_id']);
	if ( empty($group_id) ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-maillist';
		$metadata = '[group] ERROR: workflow block has no group assigned. Please notify the Phonebook Admin.';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	$group = wfl_get_group_by_id( $group_id );
	if ( empty($group) ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-maillist';
		$metadata = '[group] ERROR: there is no active group with this id. Please notify the Phonebook Admin.';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	// email to group
	$to = '"'.$group['name'].'" <'.$group['email'].'>';
	if ( empty($to) ) { return false; } // bad group email

	$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] comment on document: '.$document_title;

	$message = 'Dear Members of the Group: '.$group['name'].','."\n\n"
		.'The following document is available for comments: '."\n"
		.$document_title."\n"
		.$document_url."\n\n"
		.'Automated CRISP Workflow Engine'."\n"
		.'Please do not reply to this email'."\n";

	$rc = wfl_send_email( $to, $subject, $message );

	// update workflow action log
	if ( $rc ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-maillist';
		$metadata = '[group] email notification sent to '.$to;
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
	}

	return $rc;
}

// action 17
function wfl_action_notify_group_by_role( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();

	// prepare document data:

	$document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/comments';
	$document_title = wfl_get_document_field( $document_id, 'title' );

	$group_id = intval($current_block['group_id']);
	if ( empty($group_id) ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[group] ERROR: group_id does not exist. Please notify the Phonebook Admin.';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	$group = wfl_get_group_by_id( $group_id );

	if ( empty($group) ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[group] ERROR: group does not exist. Please notify the Phonebook Admin.';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	$role_ids = $current_block['group_role_ids'];
	$member_ids = wfl_get_group_members_by_roles( $group_id, $role_ids );

	if ( empty($member_ids) ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[group-by-role] ERROR: group has no members with specified role. Please notify the Phonebook Admin.';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	$to = array();
	foreach( $member_ids as $k => $v ) {
		$member = wfl_get_member_name_email( intval($v) );
		if ( !empty($member) ) {
			$to[] = '"'.$member['name_first'].' '.$member['name_last'].'" <'.$member['email'].'>';
		}
	}

	// email to members
	$to = implode(', ', $to);
	if ( empty($to) ) { return false; } // bad list of members

	$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] comment on document: '.$document_title;

	$message = 'Dear Members of the Group: '.$group['name'].','."\n\n"
		.'The following document is available for comments: '."\n"
		.$document_title."\n"
		.$document_url."\n\n"
		.'Automated CRISP Workflow Engine'."\n"
		.'Please do not reply to this email'."\n";

	$rc = wfl_send_email( $to, $subject, $message );

	// update workflow action log
	if ( $rc ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[group-by-role] email notification sent to '.$to;
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
	}

	return $rc;
}

// action 30
function wfl_action_request_approval_from_owner( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();
	$rc = false;

    $owner_id = wfl_get_document_owner_id( $document_id );
    if ( !$owner_id ) { return false; } // no owner???

	$progress = wfl_get_progress( $document_id, $workflow_id );
	$progress = array_reverse($progress);

	$stats = array();

	foreach( $progress as $k => $v ) {
		if ( $v['step_id'] != $step_id || $v['operation'] == 'step-started' ) { break; }
		if ( !isset( $stats[ $v['operation'] ] ) ) { $stats[ $v['operation'] ] = array(); }
		$stats[ $v['operation'] ][] = $v;
	}

	if ( empty( $stats['notify-member'] ) ) {
		// send out notifications
	    $owner = wfl_get_member_name_email( $owner_id );
    	if ( empty($owner) ) { return false; }

	    $document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/review';
    	$document_title = wfl_get_document_field( $document_id, 'title' );

		$to = '"'.$owner['name_first'].' '.$owner['name_last'].'" <'.$owner['email'].'>';
		if ( empty($to) ) { return false; }

		$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] review document: '.$document_title;
		$message = 'Dear '.$owner['name_first'].' '.$owner['name_last']."\n\n"
        	.'The following document is available for review: '."\n"
	        .$document_title."\n"
    	    .$document_url."\n\n"
        	.'Automated CRISP Workflow Engine'."\n"
	        .'Please do not reply to this email'."\n";

	    $rc = wfl_send_email( $to, $subject, $message );

		if ( $rc ) {
        	$member_id = 0;
	        $member_name = 'WORKFLOW-ENGINE';
    	    $operation = 'notify-member';
        	$metadata = '[owner] email notification sent to '.$to;
	        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
    	}
		$rc = false; // need to wait for the owner's review

	} else if ( !empty( $stats['accept'] ) ) {
		foreach( $stats['accept'] as $k => $v ) {
			if ( $v['member_id'] == $owner_id ) {
				$rc = true; // positive review received, proceed with workflow
				break;
			}
		}

	} else if ( !empty( $stats['decline'] ) ) {
		foreach( $stats['decline'] as $k => $v ) {
			if ( $v['member_id'] == $owner_id ) {
				wfl_rollback_progress( $document_id, $workflow_id, $step_id );
				$rc = false;
				break;
			}
		}

	} else {
		$rc = false; // awaiting action from owner
	}

	return $rc;
}

// action 31
function wfl_action_request_approval_from_authors( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();
	$rc = false;

	$author_ids = wfl_get_document_author_ids( $document_id );
	if ( empty($author_ids) || !$author_ids ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[authors] WARNING: document has no authors. Please assign Authors!';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false; // document has no authors, so there is no-one to notify
	}

	$progress = wfl_get_progress( $document_id, $workflow_id );
	$progress = array_reverse($progress);

	$stats = array();

	foreach( $progress as $k => $v ) {
		if ( $v['step_id'] != $step_id || $v['operation'] == 'step-started' ) { break; }
		if ( !isset( $stats[ $v['operation'] ] ) ) { $stats[ $v['operation'] ] = array(); }
		$stats[ $v['operation'] ][] = $v;
	}

	if ( empty( $stats['notify-member'] ) ) {
		// send out notifications

	    $document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/review';
    	$document_title = wfl_get_document_field( $document_id, 'title' );

		$to = array();
		foreach( $author_ids as $k => $v ) {
			$author = wfl_get_member_name_email( intval($v) );
			if ( !empty($author) ) {
				$to[] = '"'.$author['name_first'].' '.$author['name_last'].'" <'.$author['email'].'>';
			}
		}

		// email to authors
		$to = implode(', ', $to);
		if ( empty($to) ) { return false; }

		$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] review document: '.$document_title;

		$message = 'Dear Authors,'."\n\n"
			.'The following document is available for review: '."\n"
			.$document_title."\n"
			.$document_url."\n\n"
			.'Automated CRISP Workflow Engine'."\n"
			.'Please do not reply to this email'."\n";

			$rc = wfl_send_email( $to, $subject, $message );

		// update workflow action log

		if ( $rc ) {
			$member_id = 0;
			$member_name = 'WORKFLOW-ENGINE';
			$operation = 'notify-member';
			$metadata = '[authors] email notification sent to '.$to;
			$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		}

		$rc = false; // need to wait for the owner's review

	} else if ( !empty( $stats['accept'] ) && count( $stats['accept'] ) >= count( $author_ids ) ) {
		$ctr = 0;
		foreach( $stats['accept'] as $k => $v ) {
			if ( in_array( $v['member_id'], $author_ids ) ) {
				$ctr += 1;
			}
		}

		if ( $ctr >= count($author_ids) ) {
			$rc = true; // all approvals received
		} else {
			$rc = false; // still waiting for approvals
		}

	} else if ( !empty( $stats['decline'] ) ) {
		foreach( $stats['decline'] as $k => $v ) {
			// single decline is enough to roll back the workflow
			if ( in_array( $v['member_id'], $author_ids ) ) {
				wfl_rollback_progress( $document_id, $workflow_id, $step_id );
				$rc = false;
				break;
			}
		}

	} else {
		$rc = false; // awaiting action from authors
	}

	return $rc;
}

// action 32
function wfl_action_request_approval_from_reviewers( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();
	$rc = false;

	$reviewer_ids = wfl_get_document_reviewer_ids( $document_id );
	if ( empty($reviewer_ids) || !$reviewer_ids ) {
		$member_id = 0;
		$member_name = 'WORKFLOW-ENGINE';
		$operation = 'notify-member';
		$metadata = '[reviewers] WARNING: document has no reviewers. Please assign reviewers!';
		$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false; // document has no reviewers, so there is no-one to notify
	}

	$progress = wfl_get_progress( $document_id, $workflow_id );
	$progress = array_reverse($progress);

	$stats = array();

	foreach( $progress as $k => $v ) {
		if ( $v['step_id'] != $step_id || $v['operation'] == 'step-started' ) { break; }
		if ( !isset( $stats[ $v['operation'] ] ) ) { $stats[ $v['operation'] ] = array(); }
		$stats[ $v['operation'] ][] = $v;
	}

	if ( empty( $stats['notify-member'] ) ) {
		// send out notifications

	    $document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/review';
    	$document_title = wfl_get_document_field( $document_id, 'title' );

		$to = array();
		foreach( $reviewer_ids as $k => $v ) {
			$reviewer = wfl_get_member_name_email( intval($v) );
			if ( !empty($reviewer) ) {
				$to[] = '"'.$reviewer['name_first'].' '.$reviewer['name_last'].'" <'.$reviewer['email'].'>';
			}
		}

		// email to reviewers
		$to = implode(', ', $to);
		if ( empty($to) ) { return false; } // bad list of members

		$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] review document: '.$document_title;

		$message = 'Dear Reviewers,'."\n\n"
			.'The following document is available for review: '."\n"
			.$document_title."\n"
			.$document_url."\n\n"
			.'Automated CRISP Workflow Engine'."\n"
			.'Please do not reply to this email'."\n";

		$rc = wfl_send_email( $to, $subject, $message );

		// update workflow action log

		if ( $rc ) {
			$member_id = 0;
			$member_name = 'WORKFLOW-ENGINE';
			$operation = 'notify-member';
			$metadata = '[reviewers] email notification sent to '.$to;
			$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		}

		$rc = false; // need to wait for the owner's review

	} else if ( !empty( $stats['accept'] ) && count( $stats['accept'] ) >= count( $reviewer_ids ) ) {
		$ctr = 0;
		foreach( $stats['accept'] as $k => $v ) {
			if ( in_array( $v['member_id'], $reviewer_ids ) ) {
				$ctr += 1;
			}
		}

		if ( $ctr >= count($reviewer_ids) ) {
			$rc = true; // all approvals received
		} else {
			$rc = false; // still waiting for approvals
		}

	} else if ( !empty( $stats['decline'] ) ) {
		foreach( $stats['decline'] as $k => $v ) {
			// single decline is enough to roll back the workflow
			if ( in_array( $v['member_id'], $reviewer_ids ) ) {
				wfl_rollback_progress( $document_id, $workflow_id, $step_id );
				$rc = false;
				break;
			}
		}

	} else {
		$rc = false; // awaiting action from reviewers
	}

	return $rc;
}

// action 33
function wfl_action_request_approval_from_members( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();
	$rc = false;

    $member_ids = $current_block['member_ids'];
    if ( empty($member_ids) || !$member_ids ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'notify-member';
        $metadata = '[members] ERROR: workflow block has no members assigned. Please notify the Phonebook Admin.';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
        return false; // document has no members assigned, so there is no-one to notify
    }
    $member_ids = explode(',', $member_ids);


	$progress = wfl_get_progress( $document_id, $workflow_id );
	$progress = array_reverse($progress);

	$stats = array();

	foreach( $progress as $k => $v ) {
		if ( $v['step_id'] != $step_id || $v['operation'] == 'step-started' ) { break; }
		if ( !isset( $stats[ $v['operation'] ] ) ) { $stats[ $v['operation'] ] = array(); }
		$stats[ $v['operation'] ][] = $v;
	}

	if ( empty( $stats['notify-member'] ) ) {
		// send out notifications

	    $document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/review';
    	$document_title = wfl_get_document_field( $document_id, 'title' );

		$to = array();
		foreach( $member_ids as $k => $v ) {
			$member = wfl_get_member_name_email( intval($v) );
			if ( !empty($member) ) {
				$to[] = '"'.$member['name_first'].' '.$member['name_last'].'" <'.$member['email'].'>';
			}
		}

		// email to members
		$to = implode(', ', $to);
		if ( empty($to) ) { return false; } // bad list of members

		$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] review document: '.$document_title;

		$message = 'Dear Collaboration Members,'."\n\n"
			.'The following document is available for review: '."\n"
			.$document_title."\n"
			.$document_url."\n\n"
			.'Automated CRISP Workflow Engine'."\n"
			.'Please do not reply to this email'."\n";

		$rc = wfl_send_email( $to, $subject, $message );

		// update workflow action log

		if ( $rc ) {
			$member_id = 0;
			$member_name = 'WORKFLOW-ENGINE';
			$operation = 'notify-member';
			$metadata = '[request approval from group members] email notification sent to '.$to;
			$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		}

		$rc = false; // need to wait for the owner's review

	} else if ( !empty( $stats['accept'] ) && count( $stats['accept'] ) >= count( $member_ids ) ) {
		$ctr = 0;
		foreach( $stats['accept'] as $k => $v ) {
			if ( in_array( $v['member_id'], $member_ids ) ) {
				$ctr += 1;
			}
		}

		if ( $ctr >= count($member_ids) ) {
			$rc = true; // all approvals received
		} else {
			$rc = false; // still waiting for approvals
		}

	} else if ( !empty( $stats['decline'] ) ) {
		foreach( $stats['decline'] as $k => $v ) {
			// single decline is enough to roll back the workflow
			if ( in_array( $v['member_id'], $member_ids ) ) {
				wfl_rollback_progress( $document_id, $workflow_id, $step_id );
				$rc = false;
				break;
			}
		}

	} else {
		$rc = false; // awaiting action from members
	}

	return $rc;
}

// action 34
function wfl_action_request_approval_from_origgroup_management( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();
	$rc = false;

    $group = wfl_get_document_origgroup( $document_id );

    if ( empty($group) ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'notify-member';
        $metadata = '[orig.group management] WARNING: document has no orig.group. Please assign the originating group!';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
        return false;
    }

    $member_ids = wfl_get_group_managers( $group['id'] );

    if ( empty($member_ids) ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'notify-member';
        $metadata = '[orig.group management] ERROR: group has no managers! Please notify the Phonebook Admin.';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
        return false;
    }

	$progress = wfl_get_progress( $document_id, $workflow_id );
	$progress = array_reverse($progress);

	$stats = array();

	foreach( $progress as $k => $v ) {
		if ( $v['step_id'] != $step_id || $v['operation'] == 'step-started' ) { break; }
		if ( !isset( $stats[ $v['operation'] ] ) ) { $stats[ $v['operation'] ] = array(); }
		$stats[ $v['operation'] ][] = $v;
	}

	if ( empty( $stats['notify-member'] ) ) {
		// send out notifications

	    $document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/review';
    	$document_title = wfl_get_document_field( $document_id, 'title' );

		$to = array();
		foreach( $member_ids as $k => $v ) {
			$member = wfl_get_member_name_email( intval($v) );
			if ( !empty($member) ) {
				$to[] = '"'.$member['name_first'].' '.$member['name_last'].'" <'.$member['email'].'>';
			}
		}

		// email to members
		$to = implode(', ', $to);
		if ( empty($to) ) { return false; } // bad list of members

		$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] review document: '.$document_title;

		$message = 'Dear Member(s),'."\n\n"
			.'The following document is available for review: '."\n"
			.$document_title."\n"
			.$document_url."\n\n"
			.'Automated CRISP Workflow Engine'."\n"
			.'Please do not reply to this email'."\n";

		$rc = wfl_send_email( $to, $subject, $message );

		// update workflow action log

		if ( $rc ) {
			$member_id = 0;
			$member_name = 'WORKFLOW-ENGINE';
			$operation = 'notify-member';
			$metadata = '[request approval from group managers] email notification sent to '.$to;
			$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		}

		$rc = false; // need to wait for the owner's review

	} else if ( !empty( $stats['accept'] ) && count( $stats['accept'] ) >= count( $member_ids ) ) {
		$ctr = 0;
		foreach( $stats['accept'] as $k => $v ) {
			if ( in_array( $v['member_id'], $member_ids ) ) {
				$ctr += 1;
			}
		}

		if ( $ctr >= count($member_ids) ) {
			$rc = true; // all approvals received
		} else {
			$rc = false; // still waiting for approvals
		}

	} else if ( !empty( $stats['decline'] ) ) {
		foreach( $stats['decline'] as $k => $v ) {
			// single decline is enough to roll back the workflow
			if ( in_array( $v['member_id'], $member_ids ) ) {
				wfl_rollback_progress( $document_id, $workflow_id, $step_id );
				$rc = false;
				break;
			}
		}

	} else {
		$rc = false; // awaiting action from members
	}

	return $rc;
}

// action 36
function wfl_action_request_approval_from_parentgroup_management( $document_id, $workflow_id, $current_block, $step_id ) {

	$cnf =& ServiceConfig::Instance();
	$rc = false;

    $group = wfl_get_document_parentgroup( $document_id );

    if ( empty($group) ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'notify-member';
        $metadata = '[parent.group management] WARNING: document has no parent.group. Please assign the originating group that has parent group!';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
        return false;
    }

    $member_ids = wfl_get_group_managers( $group['id'] );

    if ( empty($member_ids) ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'notify-member';
        $metadata = '[parent.group management] ERROR: group has no managers! Please notify the Phonebook Admin.';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
        return false;
    }

	$progress = wfl_get_progress( $document_id, $workflow_id );
	$progress = array_reverse($progress);

	$stats = array();

	foreach( $progress as $k => $v ) {
		if ( $v['step_id'] != $step_id || $v['operation'] == 'step-started' ) { break; }
		if ( !isset( $stats[ $v['operation'] ] ) ) { $stats[ $v['operation'] ] = array(); }
		$stats[ $v['operation'] ][] = $v;
	}

	if ( empty( $stats['notify-member'] ) ) {
		// send out notifications

	    $document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/review';
    	$document_title = wfl_get_document_field( $document_id, 'title' );

		$to = array();
		foreach( $member_ids as $k => $v ) {
			$member = wfl_get_member_name_email( intval($v) );
			if ( !empty($member) ) {
				$to[] = '"'.$member['name_first'].' '.$member['name_last'].'" <'.$member['email'].'>';
			}
		}

		// email to members
		$to = implode(', ', $to);
		if ( empty($to) ) { return false; } // bad list of members

		$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] review document: '.$document_title;

		$message = 'Dear Member(s),'."\n\n"
			.'The following document is available for review: '."\n"
			.$document_title."\n"
			.$document_url."\n\n"
			.'Automated CRISP Workflow Engine'."\n"
			.'Please do not reply to this email'."\n";

		$rc = wfl_send_email( $to, $subject, $message );

		// update workflow action log

		if ( $rc ) {
			$member_id = 0;
			$member_name = 'WORKFLOW-ENGINE';
			$operation = 'notify-member';
			$metadata = '[request approval from parent group managers] email notification sent to '.$to;
			$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		}

		$rc = false; // need to wait for the owner's review

	} else if ( !empty( $stats['accept'] ) && count( $stats['accept'] ) >= count( $member_ids ) ) {
		$ctr = 0;
		foreach( $stats['accept'] as $k => $v ) {
			if ( in_array( $v['member_id'], $member_ids ) ) {
				$ctr += 1;
			}
		}

		if ( $ctr >= count($member_ids) ) {
			$rc = true; // all approvals received
		} else {
			$rc = false; // still waiting for approvals
		}

	} else if ( !empty( $stats['decline'] ) ) {
		foreach( $stats['decline'] as $k => $v ) {
			// single decline is enough to roll back the workflow
			if ( in_array( $v['member_id'], $member_ids ) ) {
				wfl_rollback_progress( $document_id, $workflow_id, $step_id );
				$rc = false;
				break;
			}
		}

	} else {
		$rc = false; // awaiting action from members
	}

	return $rc;
}

// action 35
function wfl_action_request_approval_from_group_by_role( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();
	$rc = false;

    $group = wfl_get_document_origgroup( $document_id );

    $group_id = intval($current_block['group_id']);
    if ( empty($group_id) ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'notify-member';
        $metadata = '[group] ERROR: group_id does not exist. Please notify the Phonebook Admin.';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
        return false;
    }

    $group = wfl_get_group_by_id( $group_id );

    if ( empty($group) ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'notify-member';
        $metadata = '[group] ERROR: group does not exist. Please notify the Phonebook admin.';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
        return false;
    }

    $role_ids = $current_block['group_role_ids'];
    $member_ids = wfl_get_group_members_by_roles( $group_id, $role_ids );

    if ( empty($member_ids) ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'notify-member';
        $metadata = '[group-by-role] ERROR: group has no members with specified role. Please notify the Phonebook Admin.';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
        return false;
    }

	$progress = wfl_get_progress( $document_id, $workflow_id );
	$progress = array_reverse($progress);

	$stats = array();

	foreach( $progress as $k => $v ) {
		if ( $v['step_id'] != $step_id || $v['operation'] == 'step-started' ) { break; }
		if ( !isset( $stats[ $v['operation'] ] ) ) { $stats[ $v['operation'] ] = array(); }
		$stats[ $v['operation'] ][] = $v;
	}

	if ( empty( $stats['notify-member'] ) ) {
		// send out notifications

	    $document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/review';
    	$document_title = wfl_get_document_field( $document_id, 'title' );

		$to = array();
		foreach( $member_ids as $k => $v ) {
			$member = wfl_get_member_name_email( intval($v) );
			if ( !empty($member) ) {
				$to[] = '"'.$member['name_first'].' '.$member['name_last'].'" <'.$member['email'].'>';
			}
		}

		// email to members
		$to = implode(', ', $to);
		if ( empty($to) ) { return false; } // bad list of members

		$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] review document: '.$document_title;

		$message = 'Dear Member(s),'."\n\n"
			.'The following document is available for review: '."\n"
			.$document_title."\n"
			.$document_url."\n\n"
			.'Automated CRISP Workflow Engine'."\n"
			.'Please do not reply to this email'."\n";

		$rc = wfl_send_email( $to, $subject, $message );

		// update workflow action log

		if ( $rc ) {
			$member_id = 0;
			$member_name = 'WORKFLOW-ENGINE';
			$operation = 'notify-member';
			$metadata = '[members] email notification sent to '.$to;
			$rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		}

		$rc = false; // need to wait for the owner's review

	} else if ( !empty( $stats['accept'] ) && count( $stats['accept'] ) >= count( $member_ids ) ) {
		$ctr = 0;
		foreach( $stats['accept'] as $k => $v ) {
			if ( in_array( $v['member_id'], $member_ids ) ) {
				$ctr += 1;
			}
		}

		if ( $ctr >= count($member_ids) ) {
			$rc = true; // all approvals received
		} else {
			$rc = false; // still waiting for approvals
		}

	} else if ( !empty( $stats['decline'] ) ) {
		foreach( $stats['decline'] as $k => $v ) {
			// single decline is enough to roll back the workflow
			if ( in_array( $v['member_id'], $member_ids ) ) {
				wfl_rollback_progress( $document_id, $workflow_id, $step_id );
				$rc = false;
				break;
			}
		}

	} else {
		$rc = false; // awaiting action from members
	}

	return $rc;
}


// action 80
function wfl_action_assign_internal_docid( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();
	$collaboration = $cnf->Get('settings', 'collaboration');
	$docid = $collaboration.'-INT-'.date("Y").'-'.wfl_num_to_str( $document_id ); // strtoupper(dechex( $document_id + 128 ));

	$docid_field_id = wfl_get_document_field_id_by_fixed( 'reference_id' );

	require_once( __DIR__ . '/../handlers/documents.update.php' );

	$params = array();
	$params['data'] = array();
	$params['data'][$document_id] = array();
	$params['data'][$document_id][$docid_field_id] = $docid;

	$rc = documents_update_handler($params);
	$rc = json_decode($rc);

    if ( $rc ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'assign-docid';
        $metadata = '[docid] DocID assigned = '.$docid;
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
    }

	return $rc;
}

// action 81
function wfl_action_assign_external_docid( $document_id, $workflow_id, $current_block, $step_id ) {
	$cnf =& ServiceConfig::Instance();
	$collaboration = $cnf->Get('settings', 'collaboration');
	$docid = $collaboration.'-PUB-'.date("Y").'-'.wfl_num_to_str( $document_id ); // strtoupper(dechex( $document_id + 128 ));
	$docid_field_id = wfl_get_document_field_id_by_fixed( 'reference_id' );

	require_once( __DIR__ . '/../handlers/documents.update.php' );

	$params = array();
	$params['data'] = array();
	$params['data'][$document_id] = array();
	$params['data'][$document_id][$docid_field_id] = $docid;

	$rc = documents_update_handler($params);
	$rc = json_decode($rc);

    if ( $rc ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'assign-docid';
        $metadata = '[docid] DocID assigned = '.$docid;
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
    }

	return $rc;
}

// action 90
function wfl_action_release_document_to_internal( $document_id, $workflow_id, $current_block, $step_id ) {
	$field_id = wfl_get_document_field_id_by_fixed ( 'url' );
	$url = wfl_get_document_field_by_id ( $document_id, $field_id );

	if ( empty($url) ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'comment';
        $metadata = '[url] WARNING: document has no URL assigned';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	$tmp = explode('/', $url);
	$tmp = array_reverse($tmp);
	if ( count($tmp) < 3 || $tmp[1] !== 'records' ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'comment';
        $metadata = '[url] WARNING: bad URL provided. Expecting Invenio links like https://<hostname>/records/vf661-skg63';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}
	$record_id = trim($tmp[0]);

	$rc = invenio_switch_record_to_internal( $record_id );

	if ( $rc ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'comment';
        $metadata = 'Document has been switched to Internal successfully';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
	} else {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'comment';
        $metadata = 'WARNING: failed to switch the document to Internal';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	return !!$rc;
}

// action 91
function wfl_action_release_document_to_external( $document_id, $workflow_id, $current_block, $step_id ) {
	$field_id = wfl_get_document_field_id_by_fixed ( 'url' );
	$url = wfl_get_document_field_by_id ( $document_id, $field_id );

	if ( empty($url) ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'comment';
        $metadata = '[url] WARNING: document has no URL assigned';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	$tmp = explode('/', $url);
	$tmp = array_reverse($tmp);
	if ( count($tmp) < 3 || $tmp[1] !== 'records' ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'comment';
        $metadata = '[url] WARNING: bad URL provided. Expecting Invenio links like https://<hostname>/records/vf661-skg63';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}
	$record_id = trim($tmp[0]);

	$rc = invenio_switch_record_to_public( $record_id );

	if ( $rc ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'comment';
        $metadata = 'Document has been switched to Public successfully';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
	} else {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'comment';
        $metadata = 'WARNING: failed to switch the document to Public';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	return !!$rc;
}

// action 92
function wfl_action_unrelease_document_to_origgroup( $document_id, $workflow_id, $current_block, $step_id ) {

	$field_id = wfl_get_document_field_id_by_fixed ( 'url' );
	$url = wfl_get_document_field_by_id ( $document_id, $field_id );

	if ( empty($url) ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'comment';
        $metadata = '[url] WARNING: document has no URL assigned';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	$tmp = explode('/', $url);
	$tmp = array_reverse($tmp);
	if ( count($tmp) < 3 || $tmp[1] !== 'records' ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'comment';
        $metadata = '[url] WARNING: bad URL provided. Expecting Invenio links like https://<hostname>/records/vf661-skg63';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}
	$record_id = trim($tmp[0]);

	$group = wfl_get_document_origgroup( $document_id );

	if ( empty($group) ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'comment';
        $metadata = 'WARNING: ORIG.GROUP is not set for the document';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	$rc = invenio_switch_record_to_group( $record_id, $group['name'] );
	if ( $rc ) {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'comment';
        $metadata = 'Document has been switched to '.$group['name'].' successfully';
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
	} else {
        $member_id = 0;
        $member_name = 'WORKFLOW-ENGINE';
        $operation = 'comment';
        $metadata = 'Warning: failed to switch the document to '.$group['name'];
        $rc = wfl_add_progress( $document_id, $workflow_id, $step_id, $member_id, $member_name, $operation, $metadata );
		return false;
	}

	return true;
}

//-------------------------------------------------------------------------------------------------------

function workflows_advance_once( $document_id ) {

	$map = wfl_get_map( $document_id );
	if ( !$map ) { return false; }
	if ( $map['status'] != 0 ) { return false; } // workflow is closed, no action needed

	$workflow_id  = intval( $map['workflow_id'] );
	$current_step = intval( $map['current_step_id'] );

	$config = wfl_get_config( $workflow_id );

	if ( count($config) == 0 ) { return false; } // no steps configured, nothing to do

	if ( $current_step > count($config) ) {
		wfl_close( $document_id, $workflow_id );

		$cnf =& ServiceConfig::Instance();
		$collaboration = $cnf->Get('settings', 'collaboration');
        $document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/view';
        $document_title = wfl_get_document_field( $document_id, 'title' );
		$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] workflow completed for '.$document_title;
		$message = 'Hello,'."\n"
			.'CRISP workflow has been successfully completed for '."\n\n"
			.$document_title."\n"
			.$document_url."\n\n"
            .'Automated CRISP Workflow Engine'."\n"
            .'Please do not reply to this email'."\n";
		wfl_email_to_owner( $document_id, $subject, $message );

		return false; // workflow is completed, no further actions needed
	}

	if ( $current_step == 0 ) { // advance to first step
		$current_step = 1;
		wfl_update_step( $document_id, $workflow_id, 1 );
	}

	if ( !$config[ $current_step - 1 ] ) { return false; } // ERROR: no block configured with current_step_id
	$current_step_block = $config[ $current_step - 1 ];

	$current_block_id = $current_step_block['block_id'];

	$blocks = wfl_get_blocks();
	$blocks_lookup = array();
	foreach ( $blocks as $k => $v ) {   // setup [id] => [block] lookup map
		$blocks_lookup[ $v['id'] ] = $v;
	}

	if ( empty( $blocks_lookup[ $current_block_id ] ) ) { return false; } // current step has non-existing block_id number
	$current_block = $blocks_lookup[ $current_block_id ];

	$current_block['member_ids'] = $current_step_block['member_ids'] ? $current_step_block['member_ids'] : '';
	$current_block['group_id'] = $current_step_block['group_id'] ? $current_step_block['group_id'] : 0;
	$current_block['group_role_ids'] = $current_step_block['group_role_ids'] ? $current_step_block['group_role_ids'] : '';

	$rc = false;
	switch( $current_block['block_type_id'] ) {
		case 10:
			$rc = wfl_action_notify_owner( $document_id, $workflow_id, $current_block, $current_step );
			break;
		case 11:
			$rc = wfl_action_notify_authors( $document_id, $workflow_id, $current_block, $current_step );
			break;
		case 12:
			$rc = wfl_action_notify_reviewers( $document_id, $workflow_id, $current_block, $current_step );
			break;
		case 13:
			$rc = wfl_action_notify_members( $document_id, $workflow_id, $current_block, $current_step );
			break;
		case 14:
			$rc = wfl_action_notify_origgroup( $document_id, $workflow_id, $current_block, $current_step );
			break;
		case 18:
			$rc = wfl_action_notify_parentgroup( $document_id, $workflow_id, $current_block, $current_step ); // NEW
			break;
		case 15:
			$rc = wfl_action_notify_origgroup_management( $document_id, $workflow_id, $current_block, $current_step );
			break;
		case 19:
			$rc = wfl_action_notify_parentgroup_management( $document_id, $workflow_id, $current_block, $current_step ); // NEW
			break;
		case 16:
			$rc = wfl_action_notify_group( $document_id, $workflow_id, $current_block, $current_step );
			break;
		case 17:
			$rc = wfl_action_notify_group_by_role( $document_id, $workflow_id, $current_block, $current_step );
			break;

		case 30:
			$rc = wfl_action_request_approval_from_owner( $document_id, $workflow_id, $current_block, $current_step );
			break;
		case 31:
			$rc = wfl_action_request_approval_from_authors( $document_id, $workflow_id, $current_block, $current_step );
			break;
		case 32:
			$rc = wfl_action_request_approval_from_reviewers( $document_id, $workflow_id, $current_block, $current_step );
			break;
		case 33:
			$rc = wfl_action_request_approval_from_members( $document_id, $workflow_id, $current_block, $current_step );
			break;
		case 34:
			$rc = wfl_action_request_approval_from_origgroup_management( $document_id, $workflow_id, $current_block, $current_step );
			break;
		case 36:
			$rc = wfl_action_request_approval_from_parentgroup_management( $document_id, $workflow_id, $current_block, $current_step ); // NEW
			break;
		case 35:
			$rc = wfl_action_request_approval_from_group_by_role( $document_id, $workflow_id, $current_block, $current_step );
			break;

		case 80:
			$rc = wfl_action_assign_internal_docid( $document_id, $workflow_id, $current_block, $current_step );
			break;
		case 81:
			$rc = wfl_action_assign_external_docid( $document_id, $workflow_id, $current_block, $current_step );
			break;

		case 90:
			$rc = wfl_action_release_document_to_internal( $document_id, $workflow_id, $current_block, $current_step );
			break;
		case 91:
			$rc = wfl_action_release_document_to_external( $document_id, $workflow_id, $current_block, $current_step );
			break;
		case 92:
			$rc = wfl_action_unrelease_document_to_origgroup( $document_id, $workflow_id, $current_block, $current_step );
			break;

		default:
			break;
	}

	if ( $rc && ( $current_step + 1 ) > count($config) ) {
		wfl_close( $document_id, $workflow_id );

		$cnf =& ServiceConfig::Instance();
		$collaboration = $cnf->Get('settings', 'collaboration');
        $document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/view';
        $document_title = wfl_get_document_field( $document_id, 'title' );
		$subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] workflow completed for '.$document_title;
		$message = 'Hello,'."\n"
			.'CRISP workflow has been successfully completed for '."\n\n"
			.$document_title."\n"
			.$document_url."\n\n"
            .'Automated CRISP Workflow Engine'."\n"
            .'Please do not reply to this email'."\n";
		wfl_email_to_owner( $document_id, $subject, $message );

	} else if ( $rc ) {
		$rc = wfl_update_step( $document_id, $workflow_id, $current_step + 1 );
	}

	return $rc; // true = processed step, false = no action taken or needed
}

function workflows_lock( $document_id, $workflow_id ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'INSERT INTO `'.$db_name.'`.`workflows_locks` ( `document_id`, `workflow_id` ) VALUES ( '.intval($document_id).', '.intval($workflow_id).' )';
	$db->Query($query);
	$id = $db->LastID();
	return $id;
}

function workflows_unlock( $document_id, $workflow_id ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');
	$query = 'DELETE FROM `'.$db_name.'`.`workflows_locks` WHERE `document_id` = '.intval($document_id).' AND `workflow_id` = '.intval($workflow_id);
	$db->Query($query);
}

function workflows_advance( $document_id ) {
	$map = wfl_get_map( $document_id );
	if ( !$map ) { return false; }
	if ( $map['status'] != 0 ) { return false; } // workflow is closed, no action needed

	$lock = workflows_lock( intval( $document_id ), intval( $map['workflow_id'] ) );
	if ( empty($lock) ) { // workflow is already being processed, wait for two seconds and try again
		sleep(2);
		$lock = workflows_lock( intval( $document_id ), intval( $map['workflow_id'] ) );
		if ( empty($lock) ) {
			return false;
		}
	}

	$rc = false;
	do {
		$rc = workflows_advance_once( $document_id );
	} while ( $rc );

	workflows_unlock( intval( $document_id ), intval( $map['workflow_id'] ) );
	return $rc;
}

