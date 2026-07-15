
import { getInstitution, getInstitutions, getInstitutionFields, getMemberFields, getDocumentFields, getTaskFields, getEventFields } from './pnb-api.js';

export const convertEvents = async ( data, ifields = false ) => {
	if ( !ifields) {
		ifields = await getEventFields();
	}
	let items = [], item = {}, opt, tmp1, tmp2;
	if ( data && typeof data === 'object' ) {
    for ( const [k,v] of Object.entries( data ) ) {
		item = { id: parseInt(k) };
		for ( const [k2,v2] of Object.entries(v.fields) ) {
			if ( ifields[k2].options.length ) {
				opt = {};
				tmp1 = ifields[k2].options.split(',');
				tmp1.forEach( e => { tmp2 = e.split(':'); opt[tmp2[0]] = tmp2[1]; });
				item[ ifields[k2].name_fixed.toLowerCase() ] = opt[v2];
			} else {
				item[ ifields[k2].name_fixed.toLowerCase() ] = v2;
			}
		}
		items.push( item );
    }
	}
	return items;
}

export const convertDocuments = async ( data, ifields = false ) => {
	if ( !ifields) {
		ifields = await getDocumentFields();
	}
	let items = [], item = {}, opt, tmp1, tmp2;
	if ( data && typeof data === 'object' ) {
    for ( const [k,v] of Object.entries( data ) ) {
		item = { id: parseInt(k) };
		for ( const [k2,v2] of Object.entries(v.fields) ) {
			if ( ifields[k2].options.length ) {
				opt = {};
				tmp1 = ifields[k2].options.split(',');
				tmp1.forEach( e => { tmp2 = e.split(':'); opt[tmp2[0]] = tmp2[1]; });
				item[ ifields[k2].name_fixed.toLowerCase() ] = opt[v2];
			} else {
				item[ ifields[k2].name_fixed.toLowerCase() ] = v2;
			}
		}
		items.push( item );
    }
	}
	return items;
}

export const convertTasks = async ( data, ifields = false ) => {
	if ( !ifields) {
		ifields = await getTaskFields();
	}
	let items = [], item = {}, opt, tmp1, tmp2;
	if ( data && typeof data === 'object' ) {
    for ( const [k,v] of Object.entries( data ) ) {
		item = { id: parseInt(k) };
		for ( const [k2,v2] of Object.entries(v.fields) ) {
			if ( ifields[k2].options.length ) {
				opt = {};
				tmp1 = ifields[k2].options.split(',');
				tmp1.forEach( e => { tmp2 = e.split(':'); opt[tmp2[0]] = tmp2[1]; });
				item[ ifields[k2].name_fixed.toLowerCase() ] = opt[v2];
			} else {
				item[ ifields[k2].name_fixed.toLowerCase() ] = v2;
			}
		}
		items.push( item );
    }
	}
	return items;
}

export const convertInstitutions = async ( data, ifields = false ) => {
	if ( !ifields) {
		ifields = await getInstitutionFields();
	}
	let items = [], item = {}, opt, tmp1, tmp2;
    for ( const [k,v] of Object.entries( data ) ) {
		item = { id: parseInt(k) };
		for ( const [k2,v2] of Object.entries(v.fields) ) {
			if ( ifields[k2].options.length ) {
				opt = {};
				tmp1 = ifields[k2].options.split(',');
				tmp1.forEach( e => { tmp2 = e.split(':'); opt[tmp2[0]] = tmp2[1]; });
				item[ ifields[k2].name_fixed ] = opt[v2];
			} else {
				item[ ifields[k2].name_fixed ] = v2;
			}
		}
		items.push( item );
    }
	return items;
}

export const convertEvent = async ( data, ifields = false ) => {
	if ( !ifields) {
		ifields = await getEventFields();
	}
	let opt, tmp1, tmp2;
	let item = { id: data.event ? parseInt(data.event.id) : false };
	for ( const [k2,v2] of Object.entries(data.fields) ) {
		if ( ifields[k2].options.length ) {
			opt = {};
			tmp1 = ifields[k2].options.split(',');
			tmp1.forEach( e => { tmp2 = e.split(':'); opt[tmp2[0]] = tmp2[1]; });
			item[ ifields[k2].name_fixed.toLowerCase() ] = opt[v2];
		} else {
			item[ ifields[k2].name_fixed.toLowerCase() ] = v2;
		}
	}
	return item;
}

export const convertDocument = async ( data, ifields = false ) => {
	if ( !ifields) {
		ifields = await getDocumentFields();
	}
	let opt, tmp1, tmp2;
	let item = { id: data.document ? parseInt(data.document.id) : false };
	for ( const [k2,v2] of Object.entries(data.fields) ) {
		if ( ifields[k2].options.length ) {
			opt = {};
			tmp1 = ifields[k2].options.split(',');
			tmp1.forEach( e => { tmp2 = e.split(':'); opt[tmp2[0]] = tmp2[1]; });
			item[ ifields[k2].name_fixed.toLowerCase() ] = opt[v2];
		} else {
			item[ ifields[k2].name_fixed.toLowerCase() ] = v2;
		}
	}
	return item;
}

export const convertTask = async ( data, ifields = false ) => {
	if ( !ifields) {
		ifields = await getTaskFields();
	}
	let opt, tmp1, tmp2;
	let item = { id: data.task ? parseInt(data.task.id) : false };
	for ( const [k2,v2] of Object.entries(data.fields) ) {
		if ( ifields[k2].options.length ) {
			opt = {};
			tmp1 = ifields[k2].options.split(',');
			tmp1.forEach( e => { tmp2 = e.split(':'); opt[tmp2[0]] = tmp2[1]; });
			item[ ifields[k2].name_fixed.toLowerCase() ] = opt[v2];
		} else {
			item[ ifields[k2].name_fixed.toLowerCase() ] = v2;
		}
	}
	return item;
}

export const convertInstitution = async ( data, ifields = false ) => {
	if ( !ifields) {
		ifields = await getInstitutionFields();
	}
	let opt, tmp1, tmp2;
	let item = { id: data.institution ? parseInt(data.institution.id) : false };
	for ( const [k2,v2] of Object.entries(data.fields) ) {
		if ( ifields[k2].options.length ) {
			opt = {};
			tmp1 = ifields[k2].options.split(',');
			tmp1.forEach( e => { tmp2 = e.split(':'); opt[tmp2[0]] = tmp2[1]; });
			item[ ifields[k2].name_fixed ] = opt[v2];
		} else {
			item[ ifields[k2].name_fixed ] = v2;
		}
	}
	return item;
}

export const convertMembers = async ( data, mfields = false ) => {
	if ( !mfields) {
		mfields = await getMemberFields();
	}
	let items = [], item = {}, opt, tmp1, tmp2, first_option = false;

    for ( const [k,v] of Object.entries( data ) ) { // iterate over members
		item = { id: parseInt(k) };

		for ( const [k2,] of Object.entries( mfields ) ) { // iterate over mfield data [id, field descriptor]
			if ( mfields[k2].options.length ) {
				first_option = false;
				opt = {};
				tmp1 = mfields[k2].options.split(',');
				tmp1.forEach( e => {
					tmp2 = e.split(':');
					opt[tmp2[0]] = tmp2[1];
					if ( first_option == false ) { first_option = tmp2[1]; }
				});
				item[ mfields[k2].name_fixed.toLowerCase() ] = opt[ v.fields[k2] ] ? opt[ v.fields[k2] ] : first_option;
			} else if ( v.fields[k2] ) {
				item[ mfields[k2].name_fixed.toLowerCase() ] = v.fields[k2];
			}
		}
		items.push( item );
    }
	return items;
}

export const convertField = ( field_descriptor, val ) => {
	if ( !field_descriptor.options ) { return val; }
	let opt = {}, tmp1, tmp2;
	tmp1 = field_descriptor.options.split(',');
	tmp1.forEach( e => { tmp2 = e.split(':'); opt[ tmp2[0] ] = tmp2[1]; });
	return opt[val] ? opt[val] : '';
}

export const convertMember = async ( data, mfields = false ) => {
	if ( !mfields ) {
		mfields = await getMemberFields();
	}
	let opt, tmp1, tmp2;
	let item = { id: data.member ? parseInt(data.member.id) : false };
	for ( const [k2,v2] of Object.entries(data.fields) ) {
		if ( mfields[k2].options.length ) {
			opt = {};
			tmp1 = mfields[k2].options.split(',');
			tmp1.forEach( e => { tmp2 = e.split(':'); opt[tmp2[0]] = tmp2[1]; });
			item[ mfields[k2].name_fixed.toLowerCase() ] = opt[v2];
		} else {
			item[ mfields[k2].name_fixed.toLowerCase() ] = v2;
		}
	}
	return item;
}

export const addInstitutionsToConvertedMembers = async ( data, ifields = false ) => {
	let inst = await getInstitutions();
	if ( !ifields ) {
		ifields = await getInstitutionFields();
	}
	let cinst = await convertInstitutions( inst, ifields );
	for ( const member of data ) {
		let idata = cinst.find( e => e.id == member['institution_id'] ) || {};
		for ( const [ k, v ] of Object.entries( idata ) ) {
			member['institution__' + k.toLowerCase() ] = v;
		}
	}
	return data;
}

export const addInstitutionToConvertedMember = async ( data, ifields = false ) => {
	let inst  = await getInstitution( data.institution_id );
	if ( !ifields ) {
		ifields = await getInstitutionFields();
	}
	let cinst = await convertInstitution( inst, ifields );
	for ( const [ k, v ] of Object.entries( cinst ) ) {
		data['institution__' + k.toLowerCase() ] = v;
	}
	return data;
}
