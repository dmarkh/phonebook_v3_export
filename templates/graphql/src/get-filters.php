<?php

function get_filters( $data, $filters ) {

	if ( !empty($filters) ) {
		foreach ($data as $k => $v ) {
			foreach ( $filters as $k2 => $v2 ) {
				$pass = true;
				if ( !isset($v[ $v2['field'] ] ) ) { continue; }
				switch( $v2['op'] ) {
					case 'equals':
						$pass = ( strtolower( strval( $v[ $v2['field'] ] ) ) === strtolower( strval( $v2['val'] ) ) );
						break;
					case 'not_equals':
						$pass = !( strtolower( strval( $v[ $v2['field'] ] ) ) === strtolower( strval( $v2['val'] ) ) );
						break;
					case 'contains':
						$pass = str_contains( strtolower( strval( $v[ $v2['field'] ] ) ), strtolower( strval( $v2['val'] ) ) );
						break;
					case 'not_contains':
						$pass = !str_contains( strtolower( strval( $v[ $v2['field'] ] ) ), strtolower( strval( $v2['val'] ) ) );
						break;
					case 'starts_with':
						$pass = str_starts_with( strtolower( strval( $v[ $v2['field'] ] ) ), strtolower( strval( $v2['val'] ) ) );
						break;
					case 'ends_with':
						$pass = str_ends_with( strtolower( strval( $v[ $v2['field'] ] ) ), strtolower( strval( $v2['val'] ) ) );
						break;
					case 'not_starts_with':
						$pass = !str_starts_with( strtolower( strval( $v[ $v2['field'] ] ) ), strtolower( strval( $v2['val'] ) ) );
						break;
					case 'not_ends_with':
						$pass = !str_ends_with( strtolower( strval( $v[ $v2['field'] ] ) ), strtolower( strval( $v2['val'] ) ) );
						break;
					case 'empty':
						$pass = empty( $v[ $v2['field'] ] );
						break;
					case 'not_empty':
						$pass = !empty( $v[ $v2['field'] ] );
						break;
					case 'soundex':
						$pass = soundex( strtolower( strval( $v[ $v2['field'] ] ) ) ) ===  soundex( strtolower( strval( $v2['val'] ) ) );
						break;
					case 'not_soundex':
						$pass = !soundex( strtolower( strval( $v[ $v2['field'] ] ) ) ) ===  soundex( strtolower( strval( $v2['val'] ) ) );
						break;
					case 'levenshtein':
						$pass = levenshtein( strtolower( strval( $v[ $v2['field'] ] ) ), strtolower( strval( $v2['val'] ) ) ) <= 2;
						break;
					case 'not_levenshtein':
						$pass = !levenshtein( strtolower( strval( $v[ $v2['field'] ] ) ), strtolower( strval( $v2['val'] ) ) ) <= 2;
						break;
					case 'fuzzy':
						$pass =  !empty( $v[ $v2['field'] ] ) && (
								soundex( strtolower( strval( $v[ $v2['field'] ] ) ) ) ===  soundex( strtolower( strval( $v2['val'] ) ) )
							|| levenshtein( strtolower( strval( $v[ $v2['field'] ] ) ), strtolower( strval( $v2['val'] ) ) ) <= 2
							|| str_contains( strtolower( strval( $v[ $v2['field'] ] ) ), strtolower( strval( $v2['val'] ) ) )
						);
						break;
				}
				if ( !$pass ) {
					unset( $data[$k] );
					break;
				}
			}
		}
	}

	return $data;
}
