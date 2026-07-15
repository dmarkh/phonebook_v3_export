<?php

# 
# Get member details by orcid
#

require_once( dirname(__FILE__).'/include/ServiceConfig.class.php');
require_once( dirname(__FILE__).'/include/ServiceDb.class.php');

function member_get_fields() {
   $cnf =& ServiceConfig::Instance();
   $db =& ServiceDb::Instance('phonebook_api');
   $db_name = $cnf->Get('phonebook_api','database');
   $query = 'SELECT t1.* FROM `'.$db_name.'`.`members_fields` t1, `'.$db_name.'`.`members_fields_groups` t2 WHERE t1.group = t2.id ORDER BY t2.weight ASC, t1.`weight` ASC';
   $res = $db->Query($query);
   $fields = array();
   foreach($res as $k => $v) {
       $fields[$v['id']] = $v;
   }
   return $fields;
}

function member_get( $mid ) {
	$cnf =& ServiceConfig::Instance();
	$db =& ServiceDb::Instance('phonebook_api');
	$db_name = $cnf->Get('phonebook_api','database');

	$id = intval($mid);

	$query = 'SELECT * FROM `'.$db_name.'`.`members` WHERE `id` = '.$id.' LIMIT 1';
	$memb = $db->Query($query);
	if (!empty($memb)) {
		$memb = $memb[0];
	}

	$query = 'SELECT * FROM `'.$db_name.'`.`members_fields`';
	$fields_res = $db->Query($query);
	$fields = array();
	foreach($fields_res as $k => $v) {
		$fields[$v['id']] = $v;
	}

	$memb_fields = array();
	foreach(array('string','int','date','text') as $k => $v) {
		$query = 'SELECT members_fields_id as field_id, value as field_value FROM `'.$db_name.'`.`members_data_'.$v.'s` WHERE members_id = '.$id;
		$res = $db->Query($query);
		if (empty($res)) continue;
		foreach($res as $k2 => $v2) {
			if ($v == 'int') { $v2['field_value'] = intval($v2['field_value']); }
			$memb_fields[$v2['field_id']] = $v2['field_value'];
		}
	}
	return array('member' => $memb, 'fields' => $memb_fields);
}

function member_lookup_by_orcid( $id, $status = 'active' ) {

  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $orcid = strval($id);

  $flds = member_get_fields();

  $orcid_fid = array_filter( $flds, function( $v ) {
	return( substr( $v['name_fixed'], 0, 5 ) == "orcid" );
  });
  $orcid_fid = array_keys($orcid_fid)[0];

  $namef_fid = array_filter( $flds, function( $v ) {
	return( $v['name_fixed'] == 'name_first' );
  });
  $namef_fid = array_keys($namef_fid)[0];

  $namel_fid = array_filter( $flds, function( $v ) {
	return( $v['name_fixed'] == 'name_last' );
  });
  $namel_fid = array_keys($namel_fid)[0];

  $email_fid = array_filter( $flds, function( $v ) {
	return( $v['name_fixed'] == 'email' );
  });
  $email_fid = array_keys($email_fid)[0];

  $member_role_fid = array_filter( $flds, function( $v ) {
	return( $v['name_fixed'] == 'member_role' );
  });

  $member_role_fid = ( !empty($member_role_fid) ) ? array_keys($member_role_fid)[0] : false;

  // search for member_id by orcid field descriptor id:
  $query = 'SELECT m.id as members_id FROM `'.$db_name.'`.`members` as m, `'.$db_name.'`.`members_data_strings` as mds'
	.' WHERE m.id = mds.members_id AND mds.members_fields_id = '.intval($orcid_fid).' AND mds.value = "'.$db->Escape($orcid).'"';

  if ( !empty($status) && ( $status == 'active' || $status == 'inactive' ) ) {
	$query .= ' AND m.status = "'.$db->Escape($status).'"';
  }

  $mid = $db->Query($query);

  // member could be deactivated yet have the same ORCID ID => multiple mid entries returned

  if (!empty($mid) && $mid['members_id']) {
  	$mid = $mid['members_id'][0];
  } else {
	return false;
  }

  $mem = member_get($mid);

  $data = array(
	'status' => $mem['member']['status'],
    'mid' => intval($mid),
    'orcid' => $orcid,
	'email' => $mem['fields'][ $email_fid ],
	'name_first' => $mem['fields'][ $namef_fid ],
	'name_last'  => $mem['fields'][ $namel_fid ],
	'member_role' => $member_role_fid ? $mem['fields'][ $member_role_fid ] : false
  );

  return $data;
}


