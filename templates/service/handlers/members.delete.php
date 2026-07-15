<?php

#
# Deletes member
#
# /members/delete/id:[X]
#

/*
function members_delete_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $id = intval($params['id']);
  $query = 'DELETE FROM `'.db_name.'`.`members` WHERE `id` = '.$id;
//  $res = $db->Query($query);
  $query = 'DELETE FROM `'.db_name.'`.`members_data_dates` WHERE `members_id` = '.$id;
//  $res = $db->Query($query);
  $query = 'DELETE FROM `'.$db_name.'`.`members_data_ints` WHERE `members_id` = '.$id;
//  $res = $db->Query($query);
  $query = 'DELETE FROM `'.$db_name.'`.`members_data_strings` WHERE `members_id` = '.$id;
//  $res = $db->Query($query);
  $query = 'DELETE FROM `'.$db_name.'`.`members_history` WHERE `members_id` = '.$id;
//  $res = $db->Query($query);
  return json_encode(true);
}
*/