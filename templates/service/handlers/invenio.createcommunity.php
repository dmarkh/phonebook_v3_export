<?php

function invenio_createcommunity_handler($params) {

  if ( empty($params['slug']) ) { return json_encode(false); }
  if ( empty($params['name']) ) { return json_encode(false); }

  $slug = $params['slug'];
  $name = $params['name'];

  return json_encode( [ 'data' => [ 'invenioCreateCommunity' => my_invenio_create_community( $name, $slug ) ] ] );
}
