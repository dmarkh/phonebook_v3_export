<?php

#
# Get members list, timestamped to reproduce collaboration at time X:

# /export/diffs/ts1:[unixtime]/ts2:[unixtime]

function institutions_diffs($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $ts1 = intval($params['ts1']);
  $ts2 = intval($params['ts2']);

  $query = 'SELECT * FROM `'.$db_name.'`.`institutions` WHERE `join_date` <= FROM_UNIXTIME('.$ts2.')';
  $ins = $db->Query($query);

  $institutions = array();
  foreach($ins as $k => $v) {
    $institutions[$v['id']]['status'] = $v['status'];
    $institutions[$v['id']]['status_change_date'] = $v['status_change_date'];
    $institutions[$v['id']]['status_change_reason'] = $v['status_change_reason'];
    $institutions[$v['id']]['last_update'] = $v['last_update'];
    $institutions[$v['id']]['join_date'] = $v['join_date'];
  }

  // 1.2 select institution field names
  $query = 'SELECT * FROM `'.$db_name.'`.`institutions_fields` WHERE 1';
  $ins_fld = $db->Query($query);
  $ifldn = array();
  $ifldi = array();
  $always_latest = array();
  $options = [];
  foreach($ins_fld as $k => $v) {
	if ( !$institutions[ $v['id'] ] ) { continue; }
    $ifldn[ $v['id'] ] = $v['name_fixed']; // field name lookup by id
    $ifldi[ $v['name_fixed'] ] = $v['id']; // field id lookup by name
	if ( $v['always_latest'] === 'y' ) { $always_latest[] = $v['id']; }
	if ( !empty($v['options']) ) {
		if ( !isset($options[ $v['id'] ] ) ) { $options[ $v['id'] ] = []; }
		$tmp = explode(',', $v['options']);
		if ( count($tmp) > 0 ) {
			foreach($tmp as $k2 => $v2 ) {
				$tmp2 = explode(':', $v2);
				if ( count($tmp2) == 2 ) {
					$options[ $v['id'] ][ $tmp2[0] ] = $tmp2[1];
				}
			}
		}
	}
  }
  unset($ins_fld);

  // 1.3 select institution data:
  $query = 'SELECT *, UNIX_TIMESTAMP(`value`) as uts FROM `'.$db_name.'`.`institutions_data_dates` WHERE 1';
  $ins_dat = $db->Query($query);
  foreach($ins_dat as $k => $v) {
	if ( !$institutions[ $v['institutions_id'] ] ) { continue; }
    $institutions[ $v['institutions_id'] ]['fields'][ $v['institutions_fields_id'] ] = $v['uts'];
  }
  unset($ins_dat);

  $query = 'SELECT * FROM `'.$db_name.'`.`institutions_data_ints` WHERE 1';
  $ins_int = $db->Query($query);
  foreach($ins_int as $k => $v) {
	if ( !$institutions[ $v['institutions_id'] ] ) { continue; }
    $institutions[ $v['institutions_id'] ]['fields'][ $v['institutions_fields_id'] ] = intval($v['value']);
  }
  unset($ins_int);

  $query = 'SELECT * FROM `'.$db_name.'`.`institutions_data_strings` WHERE 1';
  $ins_str = $db->Query($query);
  foreach($ins_str as $k => $v) {
	if ( !$institutions[ $v['institutions_id'] ] ) { continue; }
    $institutions[ $v['institutions_id'] ]['fields'][ $v['institutions_fields_id'] ] = $v['value'];
  }
  unset($ins_str);

  // 2. select and apply historical records based on ts

  $query = 'SELECT *, UNIX_TIMESTAMP(`date`) as dt, UNIX_TIMESTAMP(`value_to_date`) AS uvtd, UNIX_TIMESTAMP(`value_from_date`) AS uvfd FROM `'.$db_name
	.'`.`institutions_history` WHERE UNIX_TIMESTAMP(`date`) > '.$ts1.' ORDER BY dt DESC';
  $hist = $db->Query($query);

  foreach($hist as $k => $v) {
	if ( !$institutions[ $v['institutions_id'] ] ) { continue; }
	if ( in_array( $v['institutions_fields_id'], $always_latest ) ) { continue; }
	if ( !isset($institutions[ $v['institutions_id'] ]['changes'] ) ) { $institutions[ $v['institutions_id'] ]['changes'] = []; }
    $val  = ':::';
	$nval = ':::';
    if ( !empty($v['value_from_string']) || !empty($v['value_to_string']) || strlen($v['value_to_string']) != 0 ) {
		$nval = $v['value_to_string'];
		$val = $v['value_from_string'];
    } else if ( !empty($v['uvtd']) || !empty($v['uvfd']) ) {
		$nval = $v['value_to_date'];
		$val  = $v['value_from_date'];
    } else if ( !empty($v['value_from_int']) || !empty($v['value_to_int']) || strlen($v['value_to_int']) != 0 ) {
		$nval = intval($v['value_to_int']);
		$val = intval($v['value_from_int']);
    } else {
		continue; // ERROR: no change recorded?
    }
	if ( $v['dt'] > $ts1 && $v['dt'] <= $ts2 && $nval != $val ) {
		$institutions[ $v['institutions_id'] ]['changes'][] = [
			'timestamp' => $v['date'],
			'unixtime' => $v['dt'],
			'field_id' => $v['institutions_fields_id'],
			'field' => $ifldn[ $v['institutions_fields_id'] ],
			'old_value' => $val,
			'new_value' => $nval
		];
	}
    $institutions[ $v['institutions_id'] ]['fields'][ $v['institutions_fields_id'] ] = $val;
  }

  // remove institutions which has left by $ts:
  foreach($institutions as $k => $v) {
    if ( !empty($v['fields'][ $ifldi['date_leave'] ])
	&& ( $ts >= ( $v['fields'][ $ifldi['date_leave'] ]) ) ) {
		unset($institutions[$k]);
    } else if ( isset($v['fields'][ $ifldi['date_joined'] ]) &&
		$ts < $v['fields'][ $ifldi['date_joined'] ] ) {
		unset($institutions[$k]);
    }
  }

  // decode field names
  foreach( $institutions as $k => $v ) {
	if ( !isset($v['decoded_fields']) ) { $institutions[$k]['decoded_fields'] = []; }
	foreach( $v['fields'] as $k2 => $v2 ) {
		$institutions[$k]['decoded_fields'][ $ifldn[ $k2 ] ] = isset( $options[$k2] ) ? $options[$k2][$v2] : $v2;
		if ( $ifldn[ $k2 ] == 'date_joined' || $ifldn[ $k2 ] == 'date_leave' ) {
			if ( $institutions[$k]['decoded_fields'][ $ifldn[ $k2 ] ] != "0" ) {
				$institutions[$k]['decoded_fields'][ $ifldn[ $k2 ] ] = date("Y-m-d H:i:s", $institutions[$k]['decoded_fields'][ $ifldn[ $k2 ] ]);
			}
		}
	}
	unset($institutions[$k]['fields']);
	if ( !empty($v['changes']) ) {
		foreach ( $v['changes'] as $k2 => $v2 ) {
			if ( isset( $options[ $v2['field_id'] ] ) ) {
				if ( !empty( $institutions[$k]['changes'][$k2]['old_value'] ) ) {
					$institutions[$k]['changes'][$k2]['old_value'] = $options[ $v2['field_id'] ][ $institutions[$k]['changes'][$k2]['old_value'] ];
				}
				if ( !empty( $institutions[$k]['changes'][$k2]['new_value'] ) ) {
					$institutions[$k]['changes'][$k2]['new_value'] = $options[ $v2['field_id'] ][ $institutions[$k]['changes'][$k2]['new_value'] ];
				}
			}
		}
	}
  }

  return $institutions;
}

function members_diffs($params) {

  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $ts1 = intval($params['ts1']);
  $ts2 = intval($params['ts2']);

  // 1. select all members no matter their status;

  // 1.1 select member headers:
  $query = 'SELECT * FROM `'.$db_name.'`.`members` WHERE `join_date` <= FROM_UNIXTIME('.$ts2.')';
  $mem = $db->Query($query);

  $members = array();
  foreach($mem as $k => $v) {
    $members[$v['id']]['status'] = $v['status'];
    $members[$v['id']]['status_change_date'] = $v['status_change_date'];
    $members[$v['id']]['status_change_reason'] = $v['status_change_reason'];
    $members[$v['id']]['last_update'] = $v['last_update'];
    $members[$v['id']]['join_date'] = $v['join_date'];
  }

  // 1.2 select member field names
  $query = 'SELECT * FROM `'.$db_name.'`.`members_fields` WHERE 1';
  $mem_fld = $db->Query($query);
  $mfldn = array();
  $mfldi = array();
  $always_latest = array();
  $options = [];
  foreach($mem_fld as $k => $v) {
    $mfldn[ $v['id'] ] = $v['name_fixed']; // field name lookup by id
    $mfldi[ $v['name_fixed'] ] = $v['id']; // field id lookup by name
    if ( $v['always_latest'] === 'y' ) { $always_latest[] = $v['id']; }
	if ( !empty($v['options']) ) {
		if ( !isset($options[ $v['id'] ] ) ) { $options[ $v['id'] ] = []; }
		$tmp = explode(',', $v['options']);
		if ( count($tmp) > 0 ) {
			foreach($tmp as $k2 => $v2 ) {
				$tmp2 = explode(':', $v2);
				if ( count($tmp2) == 2 ) {
					$options[ $v['id'] ][ $tmp2[0] ] = $tmp2[1];
				}
			}
		}
	}
  }

  unset($mem_fld);

  // 1.3 select member data (CURRENT!):
  $query = 'SELECT *, UNIX_TIMESTAMP(`value`) as uts FROM `'.$db_name.'`.`members_data_dates` WHERE 1';
  $mem_dat = $db->Query($query);
  foreach($mem_dat as $k => $v) {
	if ( $members[ $v['members_id'] ] ) {
    	$members[ $v['members_id'] ]['fields'][ $v['members_fields_id'] ] = $v['uts'];
	}
  }
  unset($mem_dat);

  $query = 'SELECT * FROM `'.$db_name.'`.`members_data_ints` WHERE 1';
  $mem_int = $db->Query($query);
  foreach($mem_int as $k => $v) {
	if ( $members[ $v['members_id'] ] ) {
	    $members[ $v['members_id'] ]['fields'][ $v['members_fields_id'] ] = intval($v['value']);
	}
  }
  unset($mem_int);

  $query = 'SELECT * FROM `'.$db_name.'`.`members_data_strings` WHERE 1';
  $mem_str = $db->Query($query);
  foreach($mem_str as $k => $v) {
	if ( $members[ $v['members_id'] ] ) {
	    $members[ $v['members_id'] ]['fields'][ $v['members_fields_id'] ] = $v['value'];
	}
  }
  unset($mem_str);

  // MAJOR LOGIC UPDATE: now it selects updates happened _after_ TIMESTAMP, then undo changes

  // 2. select historical records based on ts

  $query = 'SELECT *, UNIX_TIMESTAMP(`date`) as dt, UNIX_TIMESTAMP(`value_to_date`) AS uvtd, UNIX_TIMESTAMP(`value_from_date`) AS uvfd FROM `'.$db_name.'`.`members_history` WHERE UNIX_TIMESTAMP(`date`) > '.$ts1.' ORDER BY dt DESC';

  $hist = $db->Query($query);

  foreach( $hist as $k => $v ) {
	if ( !$members[ $v['members_id'] ] ) { continue; }
    if ( in_array( $v['members_fields_id'], $always_latest ) ) { continue; }
	if ( !isset($members[ $v['members_id'] ]['changes'] ) ) { $members[ $v['members_id'] ]['changes'] = []; }
    $val = ':::';
	$nval = ':::';
    if ( !empty($v['value_from_string']) || !empty($v['value_to_string']) || strlen($v['value_to_string']) != 0 ) {
		$nval = $v['value_to_string'];
		$val = $v['value_from_string'];
    } else if ( !empty($v['uvtd']) || !empty($v['uvfd']) ) {
		$nval = $v['value_to_date'];
		$val  = $v['value_from_date'];
    } else if ( !empty($v['value_from_int']) || !empty($v['value_to_int']) || strlen($v['value_to_int']) != 0 ) {
		$nval = intval($v['value_to_int']);
		$val = intval($v['value_from_int']);
    } else {
		continue; // ERROR: no change recorded?
    }
	if ( $v['dt'] > $ts1 && $v['dt'] <= $ts2 && $nval != $val ) {
		$members[ $v['members_id'] ]['changes'][] = [
			'timestamp' => $v['date'],
			'unixtime' => $v['dt'],
			'field_id' => $v['members_fields_id'],
			'field' => $mfldn[ $v['members_fields_id'] ],
			'old_value' => $val,
			'new_value' => $nval
		];
	}
	$members[ $v['members_id'] ]['fields'][ $v['members_fields_id'] ] = $val;
  }

  // remove members who left by $ts:
  foreach($members as $k => $v) {
    if ( isset( $v['fields'][ $mfldi['date_leave'] ] ) && ( $v['fields'][ $mfldi['date_leave'] ] != 0 )
		&& ( $ts2 >= ($v['fields'][ $mfldi['date_leave'] ] + 6*30*24*60*60) ) ) { // +6 months after leave
		unset($members[$k]);
    }
  }

  // decode field names
  foreach( $members as $k => $v ) {
	if ( !isset($v['decoded_fields']) ) { $members[$k]['decoded_fields'] = []; }
	foreach( $v['fields'] as $k2 => $v2 ) {
		$members[$k]['decoded_fields'][ $mfldn[ $k2 ] ] = isset( $options[$k2] ) ? $options[$k2][$v2] : $v2;
		if ( $mfldn[ $k2 ] == 'date_joined' || $mfldn[ $k2 ] == 'date_leave' ) {
			if ( $members[$k]['decoded_fields'][ $mfldn[ $k2 ] ] != "0" ) {
				$members[$k]['decoded_fields'][ $mfldn[ $k2 ] ] = date("Y-m-d H:i:s", $members[$k]['decoded_fields'][ $mfldn[ $k2 ] ]);
			}
		}
	}
	unset($members[$k]['fields']);
	if ( !empty($v['changes']) ) {
		foreach ( $v['changes'] as $k2 => $v2 ) {
			if ( isset( $options[ $v2['field_id'] ] ) ) {
				if ( !empty( $members[$k]['changes'][$k2]['old_value'] ) ) {
					$members[$k]['changes'][$k2]['old_value'] = $options[ $v2['field_id'] ][ $members[$k]['changes'][$k2]['old_value'] ];
				}
				if ( !empty( $members[$k]['changes'][$k2]['new_value'] ) ) {
					$members[$k]['changes'][$k2]['new_value'] = $options[ $v2['field_id'] ][ $members[$k]['changes'][$k2]['new_value'] ];
				}
			}
		}
	}
  }

  return $members;
}

function export_diffs_handler($params) {
	$ts1 = intval($params['ts1']);
	$ts2 = intval($params['ts2']);

	$ts1 = intval($params['ts1']);
	if ( !isset($ts1) || ($ts1 <= 0) ) { return; } // ERROR, no ts1 = start
	$ts2 = intval($params['ts2']);
	if ( !isset($ts2) || ($ts2 <= 0) ) { return; } // ERROR, no ts2 = end
	if ($ts2 <= $ts1 ) { return; } // ERROR: end time <= start time

	$inst = institutions_diffs($params);
	$mems = members_diffs($params);

	$members = [];
	$institutions = [];

	foreach($mems as $k => $v ) {
		$members[] = [ 'id' => $k, 'joined' => $v['join_date'], ...$v['decoded_fields'], 'changes' => ( empty($v['changes']) ? [] : $v['changes'] ) ];
	}
	foreach($inst as $k => $v ) {
		$institutions[] = [ 'id' => $k, 'joined' => $v['join_date'], ...$v['decoded_fields'], 'changes' => ( empty($v['changes']) ? [] : $v['changes'] ) ];
	}

	return json_encode([ 'members' => $members, 'institutions' => $institutions ]);
}
