<?php

# 
# Update group details
#
# /groups/update
#
# JSON body: 
# "data": {
#	`<id>`: N,
#	`<field>` : <new_value>,
#	...
# }

function groups_update_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) || empty($data['id']) ) { return json_encode(false); }

  // update group
  $query = 'UPDATE `'.$db_name.'`.`groups` SET `parent` = '.intval($data['parent']).', `status` = "'.$db->Escape($data['status']).'", `private` = "'.$db->Escape($data['private']).'", `name` = "'.$db->Escape($data['name']).'", `desc` = "'.$db->Escape($data['desc']).'", `category` = "'.$db->Escape($data['category']).'", `email` = "'.$db->Escape($data['email']).'", `url` = "'.$db->Escape($data['url']).'" WHERE `id` = '.intval($data['id']).' LIMIT 1';
  $db->Query($query);

  return json_encode(true);
}
