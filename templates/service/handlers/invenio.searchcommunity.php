<?php

function invenio_searchcommunity_handler($params) {

  if ( empty($params['slug']) ) { return json_encode(false); }
  $slug = $params['slug'];

  return json_encode([ 'data' => [ 'invenioSearchCommunity' => my_invenio_search_community( $slug ) ] ]);
}
