<?php

function orcid_member_get_fields() {
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

function orcid_member_get( $mid ) {
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
 

function check_orcid_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( !isset($params['id']) || empty($params['id']) ) { return json_encode([]); }
  $orcid = $params['id'];

  $flds = orcid_member_get_fields();

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

  // search for member_id by orcid field descriptor id:
  $query = 'SELECT m.id as members_id FROM `'.$db_name.'`.`members` as m, `'.$db_name.'`.`members_data_strings` as mds'
    .' WHERE m.id = mds.members_id AND mds.members_fields_id = '.intval($orcid_fid).' AND mds.value = "'.$db->Escape($orcid).'"';

  $mid = $db->Query($query);

  if ( empty($mid) || !isset($mid['members_id']) ) {
	return json_encode([]);
  }

  $data = [];

  if ( is_array($mid['members_id']) ) {

    foreach( $mid['members_id'] as $k => $mid ) {
      $mem = orcid_member_get($mid);
      $data[] = [
        'status' => $mem['member']['status'],
        'mid' => intval($mid),
        'orcid' => $orcid,
        'email' => $mem['fields'][ $email_fid ],
        'name_first' => $mem['fields'][ $namef_fid ],
        'name_last'  => $mem['fields'][ $namel_fid ],
        'member_role' => $member_role_fid ? $mem['fields'][ $member_role_fid ] : false
      ];
    }

  } else {

  }

  return json_encode( $data );
}
