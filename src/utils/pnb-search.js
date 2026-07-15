
import { levenshtein } from './levenshtein.js';
import { soundex } from './soundex.js';

export const find_field_id = ( fields, name ) => {
    for ( const [,v] of Object.entries( fields ) ) {
        if ( v.name_fixed == name ) { return v.id; }
    }
    return false;
}

export const find_field_name = ( fields, id ) => {
    for ( const [,v] of Object.entries( fields ) ) {
        if ( v.id == id ) { return v.name_fixed; }
    }
    return false;
}

export const fuzzySearchConvertedInstitutions = ( items, keyword, max_levenshtein = 2 ) => {
	let sndx = soundex( keyword ), seq;
	return items.filter( item => {
		for ( const [,v] of Object.entries(item) ) {
			if ( !( typeof v === 'string' || v instanceof String ) ) { continue; }
			seq = v.split(' ');
			if ( seq.length > 1 ) {
				for( const word of seq ) {
					if ( sndx == soundex( word ) ) { return true; }
					if ( levenshtein( word, keyword ) <= max_levenshtein ) { return true; }
				}
			} else {
				if ( sndx == soundex( v ) ) { return true; }
				if ( levenshtein( v, keyword ) <= max_levenshtein ) { return true; }
			}
		}
		return false;
	});
}

export const fuzzySearchConvertedMembers = ( items, keyword, max_levenshtein = 2 ) => {
	let sndx = soundex( keyword ), seq;
	return items.filter( item => {
		for ( const [,v] of Object.entries(item) ) {
			if ( !( typeof v === 'string' || v instanceof String ) ) { continue; }
			seq = v.split(' ');
			if ( seq.length > 1 ) {
				for( const word of seq ) {
					if ( sndx == soundex( word ) ) { return true; }
					if ( levenshtein( word, keyword ) <= max_levenshtein ) { return true; }
				}
			} else {
				if ( sndx == soundex( v ) ) { return true; }
				if ( levenshtein( v, keyword ) <= max_levenshtein ) { return true; }
			}
		}
		return false;
	});
}

