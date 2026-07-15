<?php

function get_phonebook_data( $ts = null ) {

	$data = [
		'members' 		=> [],
		'institutions'	=> [],
		'documents'		=> [],
		'events'		=> [],
		'tasks'			=> [],
		'assigned_tasks' => []
	];

	$m = !empty($ts) ? get_members_ts( $ts ) : get_members();
	$i = !empty($ts) ? get_institutions_ts( $ts ) : get_institutions();

	$d = get_documents();
	$e = get_events();
	$t = get_tasks();

	$data['groups'] = get_groups();
	$data['groups_members_roles'] = get_groups_members_roles();
	$data['groups_roles'] = get_groups_roles();
	$data['assigned_tasks'] = get_assigned_tasks();

	foreach( $m as $k => $v ) {
		// check for institution id - it must present
		if ( !isset( $v['fields_decoded']['institution_id'] ) || intval( $v['fields_decoded']['institution_id'] ) === 0 ) { continue; }
		// check for institution - it must be active
		if ( !isset( $i[ $v['fields_decoded']['institution_id'] ] ) ) { continue; }
		foreach( $v['fields_decoded'] as $k2 => $v2) { if ( is_string($v2) ) { $v['fields_decoded'][$k2] = trim($v2); } }
		$data['members'][$k] = $v['fields_decoded'];
	}
	foreach( $i as $k => $v ) {
		foreach( $v['fields_decoded'] as $k2 => $v2) { if ( is_string($v2) ) { $v['fields_decoded'][$k2] = trim($v2); } }
		$data['institutions'][$k] = $v['fields_decoded'];
	}
	foreach( $d as $k => $v ) {
		foreach( $v['fields_decoded'] as $k2 => $v2) { if ( is_string($v2) ) { $v['fields_decoded'][$k2] = trim($v2); } }
		$data['documents'][$k] = $v['fields_decoded'];
	}
	foreach( $e as $k => $v ) {
		foreach( $v['fields_decoded'] as $k2 => $v2) { if ( is_string($v2) ) { $v['fields_decoded'][$k2] = trim($v2); } }
		$data['events'][$k] = $v['fields_decoded'];
	}
	foreach( $t as $k => $v ) {
		foreach( $v['fields_decoded'] as $k2 => $v2) { if ( is_string($v2) ) { $v['fields_decoded'][$k2] = trim($v2); } }
		$data['tasks'][$k] = $v['fields_decoded'];
	}
	return $data;
}
