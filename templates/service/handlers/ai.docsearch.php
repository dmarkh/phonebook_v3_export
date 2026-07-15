<?php

#
# Do vector similarity search across document titles and abstracts
# NOTE: not scalable!
#

use function Codewithkyrian\Transformers\Pipelines\pipeline;
use Codewithkyrian\Transformers\Transformers;

Transformers::setup()->setCacheDir('/tmp/.transformers-cache/models');

function dot_product($a, $b) {
    $result = array_map(function($x, $y) {
       return $x * $y;
    }, $a, $b);
   return array_sum($result);
}

function ai_docsearch_handler($params) {

  $TWEIGHT = 1.0; // title weight factor
  $AWEIGHT = 1.0; // abstract weight factor

  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( empty($params['search']) ) { return json_encode(false); }
  $search = strval( $params['search'] );

  $mode = [ 'title' => 1, 'abstract' => 1 ];
  if ( !empty($params['mode']) ) {
	$tmp = explode('+',$params['mode']);
	$mode = [];
	foreach( $tmp as $k => $v ) {
		$mode[$v] = 1;
	}
  }

  if ( empty($mode) ) { return json_encode([]); }

  $limit = 5;
  if ( !empty($params['limit']) ) { $limit = intval($params['limit']); }

  $query = 'SELECT `id`,`status` FROM `'.$db_name.'`.`documents` WHERE `status` = "active"';
  $dres = $db->Query($query);
  $activedocs = [];
  foreach( $dres as $k => $v ) {
	$activedocs[ strval($v['id']) ] = 1;
  }

  $query = 'SELECT `id`, `name_fixed` FROM `'.$db_name.'`.`documents_fields` WHERE `name_fixed` = "title" OR `name_fixed` = "abstract"';
  $fres = $db->Query($query);
  $fids = [];
  foreach ( $fres as $k => $v ) {
	$fids[ $v['name_fixed'] ] = intval( $v['id'] );
  }

  $docs = [];
  $extractor = pipeline('embeddings', 'Xenova/all-MiniLM-L6-v2');

  	$query = 'SELECT `documents_id`, `value` FROM `'.$db_name.'`.`documents_data_strings` WHERE `documents_fields_id` = '.$fids['title'];
  	$rtitle = $db->Query($query);

  	foreach( $rtitle as $k => $v ) {
		if ( empty($v['value']) ) { continue; }
		$v['documents_id'] = strval($v['documents_id']);
		if ( !isset($activedocs[$v['documents_id']]) ) { continue; }
		if ( empty($docs[ $v['documents_id'] ]) ) { $docs[ $v['documents_id'] ] = [ 'id' => $v['documents_id'] ]; }

		$docs[ $v['documents_id'] ]['title_val'] = $v['value'];

	    if ( !empty($mode['title']) ) {
			$emb = $extractor( $v['value'], normalize: true, pooling: 'mean' );
			$docs[ $v['documents_id'] ]['title'] = $emb[0];
  	    }
  	}

    $query = 'SELECT `documents_id`, `value` FROM `'.$db_name.'`.`documents_data_texts` WHERE `documents_fields_id` = '.$fids['abstract'];
    $rabstract = $db->Query($query);

    foreach( $rabstract as $k => $v ) {
		if ( empty($v['value']) ) { continue; }
		$v['documents_id'] = strval($v['documents_id']);
		if ( !isset($activedocs[$v['documents_id']]) ) { continue; }
		if ( empty($docs[ $v['documents_id'] ]) ) { $docs[ $v['documents_id'] ] = [ 'id' => $v['documents_id'] ]; }

		$docs[ $v['documents_id'] ]['abstract_val'] = $v['value'];

	    if ( !empty($mode['abstract']) ) {
			$emb = $extractor( $v['value'], normalize: true, pooling: 'mean' );
			$docs[ $v['documents_id'] ]['abstract'] = $emb[0];
		}
  	}

  $src = $extractor( $search, normalize: true, pooling: 'mean' );
  $src = $src[0];

  foreach( $docs as $k => $v ) {
	if ( empty( $v['sim'] ) ) { $docs[$k]['sim'] = 0; }
	if ( !empty($v['title']) ) {
		$docs[$k]['sim'] += ( $TWEIGHT * dot_product( $v['title'], $src ) );
	}
	if ( !empty($v['abstract']) ) {
		$docs[$k]['sim'] += ( $AWEIGHT * dot_product( $v['abstract'], $src ) );
	}
  }

  usort( $docs, function($a, $b) {
    return $b['sim'] <=> $a['sim'];
  });

  if ( count($docs) > $limit ) {
	$docs = array_slice( $docs, 0, $limit);
  }

  $docs = array_filter( $docs, function($v, $k) { return $v['sim'] > 0; }, ARRAY_FILTER_USE_BOTH );

  return json_encode( $docs );
}
