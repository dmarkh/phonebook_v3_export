import { get } from 'svelte/store';
import { localization } from '../store.js';
import { parse_localizations } from './parse-localizations.js';

let localizations = {};
let default_cache = {};
let cache = {};

let languages = [];

export const tran = ( word ) => {
	return cache[word] ?? default_cache[word] ?? word;
}

export const load_localizations = async () => {
	localizations = await parse_localizations();
	default_cache = localizations.en;
	languages = ( Object.entries(localizations) ).reduce( (a,v) => { a[v[0]] = v[1]['_language_']; return a; }, {} );
}

export const merge_localizations = async ( dataArrayBuffer = false ) => {
	if ( !dataArrayBuffer ) { return false; }
	const locales = await parse_localizations( dataArrayBuffer );
	for ( const [k,v] of Object.entries(locales) ) {
		if ( !localizations[k] ) { localizations[k] = {}; }
		for ( const [k2,v2] of Object.entries(v) ) {
			localizations[k][k2] = v2;
		}
	}
	const langs = ( Object.entries(locales) ).reduce( (a,v) => { a[v[0]] = v[1]['_language_']; return a; }, {} );
	for ( const [k,v] of Object.entries(langs) ) {
		languages[k] = v;
	}
}

export const check_language = () => {
	const curlang = get(localization);
	if ( curlang && localizations[curlang] ) { return; } // no changes, language exists
	set_language('en');
}

export const set_language = ( id ) => {
	cache = localizations[id] || {};
	localization.set( id );
}

export const get_languages = () => {
	return languages;
}

export const get_language_codes = () => {
	return [...new Set( ['en', ...Object.keys(languages)] )];
}