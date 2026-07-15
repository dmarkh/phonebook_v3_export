<?php

# 
# Get institutions history for institution <id>
#
# /institutions/history/id:[N]
#
#

function institutions_history_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $history = [];

  if ( isset($params['id']) ) {
	  $id = intval($params['id']);
	  $query = 'SELECT * FROM `'.$db_name.'`.`institutions_history` WHERE `institutions_id` = '.$id.' ORDER BY `date` DESC';
	  $history = $db->Query($query);
  } else {
	  $query = 'SELECT * FROM `'.$db_name.'`.`institutions_history` WHERE 1 ORDER BY `date` DESC LIMIT 1000;';
	  $history = $db->Query($query);
  }

  return json_encode($history);
}
