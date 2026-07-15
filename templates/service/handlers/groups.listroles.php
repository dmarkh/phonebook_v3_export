<?php

function groups_listroles_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $query = 'SELECT * FROM `'.$db_name.'`.`groups_roles` ORDER BY `weight` ASC;';
  $res = $db->Query($query);

  return json_encode( $res );
}
