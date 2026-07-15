<script>

import { tran } from '../utils/tran.js';

import { onMount } from 'svelte';
import LinearProgress from '@smui/linear-progress';
import Fab, { Label, Icon } from '@smui/fab';

import { getInstitutions, getInstitutionsTS, getInstitutionFields } from '../utils/pnb-api.js';
import { convertInstitutions } from '../utils/pnb-convert.js';
import { sortConvertedInstitutions } from '../utils/pnb-sort.js';

import { getMembers, getMembersTS, getMemberFields } from '../utils/pnb-api.js';
import { convertMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';

import { find_field_id, find_field_name } from '../utils/pnb-search.js';
import { strtotime } from '../vendor/strtotime.js';
import { non_breaking_space } from '../utils/non-breaking-space.js';

import FileSaver from '../vendor/FileSaver.js';

let author_list_data = '';

export let unix_ts;

const saveAuthorListFile = () => {
	var blob = new Blob([ author_list_data ], {type: "text/plain;charset=utf-8"});
	const filename = unix_ts ? ( window.pnb.inspire.collaboration_id + "-author-list-inspire-" + unix_ts + ".xml" ) : ( window.pnb.inspire.collaboration_id + "-author-list-inspire-" + ( new Date().toISOString().slice(0, 10) ) + ".xml" );
	FileSaver.saveAs( blob, filename );
}

const getAuthorListInspire = async () => {

	let result = '';
	let now = unix_ts ? strtotime( unix_ts ) : Math.round(+new Date()/1000); // unixtime

    let institutions = unix_ts ? ( await getInstitutionsTS({ unix_ts }) ) : ( await getInstitutions() );

	let ifields = await getInstitutionFields();
    let convertedInstitutions = await convertInstitutions( institutions, ifields );
    sortConvertedInstitutions( convertedInstitutions );

    let members = unix_ts ? ( await getMembersTS({ unix_ts }) ) : ( await getMembers() );
	let mfields = await getMemberFields();
    let convertedMembers = await convertMembers( members, mfields );
    sortConvertedMembers( convertedMembers );

		result += '<?xml version="1.0" encoding="UTF-8"?>' + "\n"
			+ '<!DOCTYPE collaborationauthorlist SYSTEM "author.dtd">' + "\n"
			+ '<collaborationauthorlist' + "\n"
			+ 'xmlns:foaf="http://xmlns.com/foaf/0.1/"' + "\n"
			+ 'xmlns:cal="http://inspirehep.net/info/HepNames/tools/authors_xml/">' + "\n"
			+ '<cal:creationDate>' + (new Date(now*1000)).toISOString().split('T')[0] + '</cal:creationDate>' + "\n"
			+ '<cal:publicationReference></cal:publicationReference>' + "\n"
			+ '<cal:collaborations>' + "\n"
			+ ' <cal:collaboration id="'+ window.pnb.inspire.collaboration_id +'">' + "\n"
			+ '  <foaf:name>'+ window.pnb.inspire.foafName + '</foaf:name>' + "\n"
			+ '  <cal:experimentNumber>' + window.pnb.inspire.experimentNumber + '</cal:experimentNumber>' + "\n"
			+ ' </cal:collaboration>' + "\n"
			+ '</cal:collaborations>' + "\n";

		result += '<cal:organizations>' + "\n";

        for ( const i of convertedInstitutions ) {

            if ( i.leave_date !== undefined &&
                 i.leave_date !== '0000-00-00 00:00:00' &&
                 i.leave_date !== 0 &&
                 i.leave_date !== '0' &&
                 i.leave_date !== false &&
                 i.leave_date !== '' &&
				 Math.floor( Date.parse( i.leave_date ) / 1000 ) < now ) {
              continue;
            }

			if ( i.is_virtual && i.is_virtual.toLowerCase() == 'yes' ) { continue; }

			const address = [];
			if ( i.address_line_1 ) { address.push( i.address_line_1.replace('&','&amp;') ); }
			if ( i.address_line_2 ) { address.push( i.address_line_2.replace('&','&amp;') ); }
			if ( i.city ) { address.push( i.city.replace('&','&amp;') ); }
			if ( i.state ) { address.push( i.state.replace('&','&amp;') ); }
			if ( i.country ) { address.push( i.country.replace('&','&amp;') ); }
			if ( i.postcode ) { address.push( i.postcode ); }

			result += '<foaf:Organization id="a'+ i.id +'">' + "\n"
				+' <foaf:name>'+ i.name_short +'</foaf:name>' + "\n"
				+ ( i.INSPIRE_ID ? ( ' <cal:orgName source="INSPIRE">'+ i.INSPIRE_ID +'</cal:orgName>' + "\n" ) : '' )
				+ ( i.ror_id ? ( ' <cal:orgName source="ROR">'+ i.ror_id +'</cal:orgName>' + "\n" ) : '' )
				+' <cal:orgStatus collaborationid="'+ window.pnb.inspire.collaboration_id +'">Member</cal:orgStatus>' + "\n"
				+' <cal:orgAddress>'+ address.join(', ') +'</cal:orgAddress>' + "\n"
				+'</foaf:Organization>' + "\n";
		}

		result += '</cal:organizations>' + "\n";

		result += '<cal:authors>' + "\n";

        for ( const m of convertedMembers ) {

            if ( m.leave_date !== undefined &&
                 m.leave_date !== '0000-00-00 00:00:00' &&
                 m.leave_date !== 0 &&
                 m.leave_date !== '0' &&
                 m.leave_date !== false &&
                 m.leave_date !== '' &&
				 Math.floor( Date.parse( m.leave_date ) / 1000 ) < now ) {
              continue;
            }

            if ( m.is_author && ( m.is_author.toLowerCase() != 'yes' ) ) { continue; }

			if ( m.institution_id === undefined || m.institution_id == 0 ) { continue; }

			result += '<foaf:Person>' + "\n"
				+' <foaf:name>B.E. Aboona</foaf:name>' + "\n"
				+' <foaf:givenName>'+ m.name_first +'</foaf:givenName>' + "\n"
				+' <foaf:familyName>'+ m.name_last +'</foaf:familyName>' + "\n"
				+' <cal:authorNamePaper>' + ( m.name_initials ? m.name_initials : m.name_first[0].toUpperCase() ) + ' '+ m.name_last +'</cal:authorNamePaper>' + "\n"
				+' <cal:authorCollaboration collaborationid="'+ window.pnb.inspire.collaboration_id +'"/>' + "\n"
				+' <cal:authorAffiliations>' + "\n"
					+'  <cal:authorAffiliation organizationid="a'+ m.institution_id +'"/>' + "\n"
				+' </cal:authorAffiliations>' + "\n"
				+' <cal:authorids>' + "\n"
					+ ( m.orcid_id ? ( '  <cal:authorid source="ORCID">'+ m.orcid_id +'</cal:authorid>' + "\n" ) : '' )
					+ ( m.inspire_id ? ( '  <cal:authorid source="INSPIRE">INSPIRE-' + m.inspire_id + '</cal:authorid>' + "\n" ) : '' )
				+' </cal:authorids>' + "\n"
				+'</foaf:Person>' + "\n";
        }

		result += '</cal:authors>' + "\n";

		result += '</collaborationauthorlist>' + "\n";

	author_list_data = result;

	return result;
}

</script>

{#await getAuthorListInspire()}
	<LinearProgress indeterminate />
{:then data}
{#if unix_ts}
	TIMESTAMP: {unix_ts}
{:else}
	TIMESTAMP: {(new Date()).toISOString().split('T')[0]}
{/if}
<br/>
<pre style="overflow-x: scroll;">
{data}
</pre>

<div class="save-button">
	<Fab color="primary" on:click={() => { saveAuthorListFile(); }} extended>
		<Icon class="material-icons">save</Icon>
		<Label>{tran('_download_')}</Label>
    </Fab>
</div>

{/await}

<style>
.save-button {
    position: absolute;
    bottom: 2vmin;
    right: 2vmin;
}
</style>