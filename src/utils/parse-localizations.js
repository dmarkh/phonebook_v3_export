
import { get_binary_file } from './get-binary-file.js';

let worker = false;

const setup_worker = () => {
	if ( !worker ) {
		worker = new Worker('js/worker.20250613.js');
	}
}

const parseXLSXdata = async ( file ) => {
	if ( !worker ) { setup_worker(); }

    return new Promise((resolve => {
		worker.onmessage = (e) => {
			resolve(e.data.jsondata);
		};
		worker.onerror = (e) => {
			console.log('WORKER ERROR', e );
		};
        worker.postMessage({ file });
    }));
}

export const parse_localizations = async ( dataArrayBuffer = false ) => {

	const file = dataArrayBuffer ? dataArrayBuffer : await get_binary_file('locales/localization.xlsx');
	const jsdata = await parseXLSXdata( file );
	if ( !jsdata || !Array.isArray(jsdata) ) {
		console.log('ERROR: localization data is not an array');
		return;
	}
	const localizations = {};
	const header = jsdata.shift(); // header
	const pct    = jsdata.shift(); // drop percentages

	if ( !pct ) { console.log('no percentages?'); }

	// create entries per language
	for( let i = 1; i < header.length; i++ ) {
		localizations[ header[i] ] = {};
	}

	// parse data
	for ( const line of jsdata ) {
		for ( let i = 1, ilen = header.length; i < ilen; i++ ) {
			if ( line[i] && line[i].length ) {
				localizations[ header[i] ][ line[0] ] = line[i];
			}
		}
	}

	return localizations;
}