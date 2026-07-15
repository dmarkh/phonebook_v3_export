
import { getMember, getMembers, getMemberFields, getMemberFieldgroups, getDocument, getDocumentFields, getTask, getTaskFields, getEvent, getEventFields } from './pnb-api.js';
import { getInstitutions, getInstitution, getInstitutionFields, getInstitutionFieldgroups } from './pnb-api.js';
import { convertInstitution, convertMember, convertDocument, convertTask, convertEvent } from './pnb-convert.js';
import { getCountries } from './pnb-api.js';
import { find_field_id } from './pnb-search.js';

export const orderKeys = (o, f) => {
    let os=[], ks=[];
    for ( let i in o ) {
        os.push([i, o[i]]);
    }
    os.sort( (a,b) => f(a[1],b[1]) );
    for (let i = 0; i < os.length; i++ ) {
        ks.push( os[i][0] );
    }
    return ks;
}

export const listInstitutions = async() => {
	let i = await getInstitutions({ details: 'name' });
	let ifields = await getInstitutionFields();
	let fid = find_field_id( ifields, 'name_full' );
	let data = Object.entries(i).sort( (a,b) => a[1].fields[fid].localeCompare(b[1].fields[fid]) ).map( ([k,v]) => [ parseInt(k), v.fields[fid] ] );
	return data;
}

export const listMembers = async() => {
	let m = await getMembers();
	let mfields = await getMemberFields();
	let fidl = find_field_id( mfields, 'name_last'  );
	let fidf = find_field_id( mfields, 'name_first' );
	let fidi = find_field_id( mfields, 'institution_id' );
	let data = Object.entries(m).sort( (a,b) => ( a[1].fields[fidl] + ', ' + a[1].fields[fidf] ).localeCompare( b[1].fields[fidl] + ', ' + b[1].fields[fidf] ) )
		.map( ([k,v]) => [ parseInt(k), v.fields[fidl] + ', ' + v.fields[fidf], v.fields[fidi] ] );
	return data;
}

export const listCountries = async() => {
	let c = await getCountries();
	return Object.entries(c).sort( (a,b) => a[1].localeCompare(b[1]) );
}

export const downloadEvent = async ( id = false ) => {
    let ifields = await getEventFields();

	for( const [k,v] of Object.entries(ifields) ) {
		if ( v.options.length ) {
			ifields[k].decoded_options = {};
			v.options.split(',')
				.map( t => { const tmp = t.split(':'); return [ [tmp[0].trim()], tmp[1].trim() ]; })
				.reduce( (acc,cval) => { acc[ cval[0] ] = cval[1]; return acc; }, ifields[k].decoded_options );
		}
	}

    let idata = id ? await getEvent( id ) : { "fields": {} };

    let cidata = await convertEvent( idata, ifields );

    return {
		event: idata,
		cevent: cidata,
		event_fields: ifields,
		event_fields_ordered: orderKeys( ifields, (a,b) => ( a.weight - b.weight ) )
	};
}

export const downloadDocument = async ( id = false ) => {
    let ifields = await getDocumentFields();

	for( const [k,v] of Object.entries(ifields) ) {
		if ( v.options.length ) {
			ifields[k].decoded_options = {};
			v.options.split(',')
				.map( t => { const tmp = t.split(':'); return [ [tmp[0].trim()], tmp[1].trim() ]; })
				.reduce( (acc,cval) => { acc[ cval[0] ] = cval[1]; return acc; }, ifields[k].decoded_options );
		}
	}

    let idata = id ? await getDocument( id ) : { "fields": {} };

	for( const [k,v] of Object.entries(idata.fields) ) {
		if ( ifields[k].type === 'date' && v && v.length ) {
            let tmp = v.split(' ');
            if ( tmp.length > 1 ) {
                idata.fields[k] = tmp[0];
            } else {
				tmp  = v.split('T');
	            if ( tmp.length > 1 ) {
    	            idata.fields[k] = tmp[0];
				}
			}
		}
	}

    let cidata = await convertDocument( idata, ifields );

    return {
		document: idata,
		cdocument: cidata,
		document_fields: ifields,
		document_fields_ordered: orderKeys( ifields, (a,b) => ( a.weight - b.weight ) )
	};
}

export const downloadTask = async ( id = false ) => {
    let ifields = await getTaskFields();

	for( const [k,v] of Object.entries(ifields) ) {
		if ( v.options.length ) {
			ifields[k].decoded_options = {};
			v.options.split(',')
				.map( t => { const tmp = t.split(':'); return [ [tmp[0].trim()], tmp[1].trim() ]; })
				.reduce( (acc,cval) => { acc[ cval[0] ] = cval[1]; return acc; }, ifields[k].decoded_options );
		}
	}

    let idata = id ? await getTask( id ) : { "fields": {} };

	for( const [k,v] of Object.entries(idata.fields) ) {
		if ( ifields[k].type === 'date' && v && v.length ) {
            let tmp = v.split(' ');
            if ( tmp.length > 1 ) {
                idata.fields[k] = tmp[0];
            } else {
				tmp  = v.split('T');
	            if ( tmp.length > 1 ) {
    	            idata.fields[k] = tmp[0];
				}
			}
		}
	}

    let cidata = await convertTask( idata, ifields );

    return {
		task: idata,
		ctask: cidata,
		task_fields: ifields,
		task_fields_ordered: orderKeys( ifields, (a,b) => ( a.weight - b.weight ) )
	};
}


export const downloadInstitution = async ( id = false ) => {
    let ifields = await getInstitutionFields();
    let igroups = await getInstitutionFieldgroups();
	let countries = await listCountries();

	for( const [k,v] of Object.entries(ifields) ) {
		if ( v.options.length ) {
			ifields[k].decoded_options = {};
			v.options.split(',')
				.map( t => { const tmp = t.split(':'); return [ [tmp[0].trim()], tmp[1].trim() ]; })
				.reduce( (acc,cval) => { acc[ cval[0] ] = cval[1]; return acc; }, ifields[k].decoded_options );
		}
	}

    let idata = id ? await getInstitution( id ) : { "fields": {} };

	for( const [k,v] of Object.entries(idata.fields) ) {
		if ( ifields[k].type === 'date' && v && v.length ) {
            let tmp = v.split(' ');
            if ( tmp.length > 1 ) {
                idata.fields[k] = tmp[0];
            } else {
				tmp  = v.split('T');
	            if ( tmp.length > 1 ) {
    	            idata.fields[k] = tmp[0];
				}
			}
		}
	}

    let cidata = await convertInstitution( idata, ifields );

    return {
		institution: idata,
		cinstitution: cidata,
		institution_fields: ifields,
		institution_groups: igroups,
		institution_fields_ordered: orderKeys( ifields, (a,b) => a.group == b.group ? ( a.weight - b.weight ) : igroups[a.group].weight - igroups[b.group].weight ),
		institution_fieldgroups_ordered: orderKeys( idata, (a,b) => a.weight - b.weight ),
		country_ids_sorted: countries,
		country_codes_sorted: countries.map( c => [c[0], c[0]] ).sort( (a,b) => a[0].localeCompare(b[0]) )
	};
}

export const downloadMember = async ( id = false, inst_id = false ) => {

	// member data
    let mfields = await getMemberFields();
    let mgroups = await getMemberFieldgroups();

    let mdata = id ? await getMember(id) : { "fields": {} };
	if ( !id && inst_id ) {
		let fid = find_field_id( mfields, 'institution_id' );
		mdata.fields[fid] = inst_id;
	}

	for( const [k,v] of Object.entries(mfields) ) {
		if ( v.options.length ) {
			mfields[k].decoded_options = {};
			v.options.split(',')
				.map( t => { const tmp = t.split(':'); return [ [tmp[0].trim()], tmp[1].trim() ]; })
				.reduce( (acc,cval) => { acc[ cval[0] ] = cval[1]; return acc; }, mfields[k].decoded_options );
		}
	}

	for( const [k,v] of Object.entries(mdata.fields) ) {
		if ( mfields[k].type === 'date' && v && v.length ) {
			let tmp = v.split(' ');
			if ( tmp.length > 1 ) {
				mdata.fields[k] = tmp[0];
			} else {
                tmp  = v.split('T');
                if ( tmp.length > 1 ) {
                    mdata.fields[k] = tmp[0];
                }
            }
		}
	}

    let cmdata = await convertMember( mdata, mfields );

	// institution data
	let inst = {};
	if ( inst_id ) {
		inst = await downloadInstitution( inst_id );
	} else if ( cmdata.institution_id ) {
		inst = await downloadInstitution( cmdata.institution_id );
	}
	let dinst = await listInstitutions();
	let countries = await listCountries();

	return {
		member: mdata,
		cmember: cmdata,
		member_fields: mfields,
		member_groups: mgroups,
		member_fields_ordered: orderKeys( mfields, (a,b) => a.group == b.group ? ( a.weight - b.weight ) : mgroups[a.group].weight - mgroups[b.group].weight ),
		member_fieldgroups_ordered: orderKeys( mdata, (a,b) => a.weight - b.weight ),
		...inst,
		institution_ids_sorted: dinst,
		country_ids_sorted: countries
	};
}
