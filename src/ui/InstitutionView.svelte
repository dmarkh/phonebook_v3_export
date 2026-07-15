<script>

import {router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';

import { getMembers, getInstitution, getInstitutions, getInstitutionFields, getInstitutionFieldgroups } from '../utils/pnb-api.js';
import { convertMembers, convertInstitution } from '../utils/pnb-convert.js';

import { screen, institution_mode, institution_id, auth } from '../store.js';
import { downloadInstitution, listInstitutions } from '../utils/pnb-download.js';
import { find_field_id } from '../utils/pnb-search.js';

let title = '', subtitle = '';

let members = false;
let institution_ids_sorted = [];
let virtual_fid = false, virtual_val = false;
let associated_inst_data = [];

const locateInstitutionName = ( inst_id ) => {
	for( const [k,v] of institution_ids_sorted ) {
		if ( k == inst_id ) { return v; }
	}
	return '';
}

const handleRowClick = ( e ) => {
    $institution_mode = 'view';
    $institution_id = e.target.dataset.entryId;
    $screen = 'institution';
    router.goto('/institution/' + $institution_id + '/view');
}

const findMember = ( id ) => {
	return members.find( m => m.id == id ) || false;
}

const fetchInstitution = async ( id ) => {

    let mem = await getMembers();
    members = await convertMembers( mem );

	let data = [],
		i = await downloadInstitution( id );

	let ifields = i.institution_fields;

    let associated_fid = find_field_id( ifields, 'associated_id' );

    virtual_fid = find_field_id( ifields, 'is_virtual' );
    virtual_val = virtual_fid ? i.cinstitution[ i.institution_fields[ virtual_fid ].name_fixed ] : false;

	institution_ids_sorted = await listInstitutions();

    if ( id && i.cinstitution) {
        title = i.cinstitution.name_full || 'N/A';
        subtitle = i.cinstitution.country || 'COUNTRY NOT SET';
    }

	for ( const fid of i.institution_fields_ordered ) {
		if ( i.institution_fields[ fid ].is_enabled != 'y' ) { continue; }
		if ( i.institution_fields[ fid ].privacy !== 'public' && !( $auth['role'] == 'ADMIN' || $auth['role'] == 'EDITOR' ) ) { continue; }
		if ( virtual_val === 'Yes' && !['name_full', 'is_virtual'].includes( i.institution_fields[ fid ].name_fixed ) ) { continue; }
		if ( i.institution_fields[ fid ].name_fixed == 'associated_id' ) {
	       	data.push({
    	       	id: parseInt(fid),
        	    desc: i.institution_fields[fid].name_desc,
            	value: locateInstitutionName( i.cinstitution[ i.institution_fields[fid].name_fixed ] ),
	            group: i.institution_groups[ i.institution_fields[fid].group ].name_full
    	    });
		} else if ( i.institution_fields[ fid ].name_fixed == 'council_representative' ) {
			const m = findMember( i.cinstitution[ i.institution_fields[fid].name_fixed ] );
	       	data.push({
    	       	id: parseInt(fid),
        	    desc: i.institution_fields[fid].name_desc,
            	value: ( m ? ( m.name_first + ' ' + m.name_last ) : 'N/A' ),
	            group: i.institution_groups[ i.institution_fields[fid].group ].name_full
    	    });
		} else {
	       	data.push({
    	       	id: parseInt(fid),
        	    desc: i.institution_fields[fid].name_desc,
            	value: i.cinstitution[ i.institution_fields[fid].name_fixed ] || '',
	            group: i.institution_groups[ i.institution_fields[fid].group ].name_full
    	    });
		}
    }

	// display a list of dependent institutions
	if ( associated_fid ) {
		let associated_inst_ids = [];
    	let name_full_fid = find_field_id( ifields, 'name_full' );
		let insts = await getInstitutions();
		for ( const [iid, inst] of Object.entries(insts) ) {
			if ( !inst.fields[associated_fid] ) { continue; }
			if ( inst.fields[associated_fid] == id ) {
				associated_inst_ids.push( parseInt(iid) );
			}
		}
		if ( associated_inst_ids.length ) {
			for ( const aid of associated_inst_ids ) {
				associated_inst_data.push({
					"id": aid,
					"name_full": insts[aid].fields[name_full_fid]
				});
			}
		}
	}

	return data;
}

</script>

{#await fetchInstitution( $institution_id )}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
<div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>

<Paper>
<DataTable table$aria-label="Institution Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="field" style="width: 20%; text-align: left;">
                <Label>FIELD</Label>
            </Cell>
            <Cell columnId="value" style="width: 60%; text-align: left;">
                <Label>VALUE</Label>
            </Cell>
            <Cell columnId="group" style="width: 20%; text-align: left;">
                <Label>GROUP</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each data as item (item.id)}
      <Row data-entry-id="{item.id}">
        <Cell style="width: 20%; font-weight: bold; white-space: normal;">{item.desc}</Cell>
        <Cell style="width: 60%; white-space: normal;">{item.value}</Cell>
        <Cell style="width: 20%; white-space: normal;">{item.group}</Cell>
      </Row>
    {/each}
    </Body>
</DataTable>
</Paper>

{#if associated_inst_data.length}
<Paper>
<div style="text-align: center;" class="mdc-typography--headline4">Associated Institutions</div>
<DataTable table$aria-label="Institution Data" style="width: 100%;" on:SMUIDataTableRow:click={handleRowClick}>
    <Head>
        <Row>
            <Cell columnId="value" style="width: 100%; text-align: left;">
                <Label>NAME</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each associated_inst_data as iinst (iinst.id)}
      <Row data-entry-id="{iinst.id}">
        <Cell style="width: 100%;">{iinst.name_full}</Cell>
      </Row>
    {/each}
    </Body>
</DataTable>
</Paper>
{/if}

{/await}

<style>

</style>