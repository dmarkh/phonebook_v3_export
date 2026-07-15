<script>

import {router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';

import AccessDenied from './AccessDenied.svelte';

import { getAllInstitutions } from '../utils/pnb-api.js';
import { convertInstitutions } from '../utils/pnb-convert.js';
import { sortConvertedInstitutions } from '../utils/pnb-sort.js';

import { find_field_id } from '../utils/pnb-search.js';
import { convertField } from '../utils/pnb-convert.js';

import { screen, institution_id, institution_mode } from '../store.js';
import { auth } from '../store.js';

let days = 30;
let lastentries = 50;
let mstatus = 0;

let title = false, subtitle = false;
let extra_institutions_field_id = false,
	institution_name_field_id = false;

let inst, ifields, institutions, recent_institutions, changed_institutions;

const handleRowClick = ( e ) => {
    $institution_mode = 'view';
    $institution_id = e.target.dataset.entryId;
    $screen = 'institution';
    router.goto('/institution/' + $institution_id + '/view');
}

const getFieldId = ( name, fields ) => {
    let field = Object.values( fields ).find( f => f.name_fixed == name );
    if ( field ) { return field.id; }
    return false;
}

const trim_ellipsis = function( str, length ) {
  return str.length > length ? str.substring(0, length) + "..." : str;
}

const getMemberName = ( mid ) => {
	if ( !mem[mid] ) { return 'N/A'; }
	return trim_ellipsis( mem[mid].fields[ m_name_first_id ] + ' ' + mem[mid].fields[ m_name_last_id ], 50 );
}

const downloadHistory = async ( mstatus, days, lastentries ) => {
    let data = [];

	inst = await getAllInstitutions();
    institutions = await convertInstitutions( inst );

    sortConvertedInstitutions( institutions );

	for( const i of institutions ) {
		i['__status'] = inst[ i.id ]['status'];
		i['__status_change_date'] = inst[ i.id ]['status_change_date'];
		i['__status_change_date_ts'] = Date.parse( inst[ i.id ]['status_change_date'] );
	}

	// sort by status change timestamp ASC
	institutions.sort( ( a, b ) => {
		if ( a['__status_change_date_ts'] < b['__status_change_date_ts'] ) {
			return 1;
		} else if ( a['__status_change_date_ts'] > b['__status_change_date_ts'] ) {
			return -1;
		}
		return 0;
	});

	const cutoff = Date.now() - days * 24 * 60 * 60 * 1000; // ms for the past 30 days

    if ( mstatus == 0 ) {
		recent_institutions = institutions.filter( m => m['__status_change_date_ts'] >= cutoff );
    } else if ( mstatus == 1 ) {
		recent_institutions = institutions.filter( m => m['__status_change_date_ts'] >= cutoff && m['__status'] == 'active' );
    } else if ( mstatus == 2 ) {
		recent_institutions = institutions.filter( m => m['__status_change_date_ts'] >= cutoff && m['__status'] != 'active' );
    }

	changed_institutions = institutions.slice(0, lastentries); // last 100 changed entries

    subtitle = 'RECENT CHANGES: INSTITUTIONS';

	return institutions;
}

</script>

{#if $auth['grants']['institutions-history']}

{#key days}
{#key lastentries}
{#key mstatus}

{#await downloadHistory(mstatus,days,lastentries)}

<LinearProgress indeterminate />

{:then data}

{#if title}
	<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
{/if}
{#if subtitle}
    <div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>
{/if}
<Paper>
	<p style="font-weight: bold;">
      <Select bind:value={mstatus} noLabel>
        <Option value={0}>ALL CHANGES</Option>
        <Option value={1}>ACTIVATIONS</Option>
        <Option value={2}>DEACTIVATIONS</Option>
      </Select>
        HAPPENED IN THE PAST
      <Select bind:value={days} noLabel>
        <Option value={7}>7 DAYS</Option>
        <Option value={30}>30 DAYS</Option>
        <Option value={90}>3 MONTHS</Option>
        <Option value={180}>6 MONTHS</Option>
        <Option value={365}>12 MONTHS</Option>
        <Option value={730}>24 MONTHS</Option>
        <Option value={1095}>36 MONTHS</Option>
      </Select>
    ( found {recent_institutions.length} records ):</p>
	{#if recent_institutions.length}
	    <DataTable table$aria-label="Member List" style="width: 100%;" on:SMUIDataTableRow:click={handleRowClick} >
    	<Head>
        	<Row>
	            <Cell columnId="m_date" style="text-align: center;">
    	            <Label>DATE</Label>
        	    </Cell>
            	<Cell columnId="m_inst" style="text-align: center;">
                	<Label>INSTITUTION</Label>
	            </Cell>
	            <Cell columnId="m_status" style="text-align: center;">
    	            <Label>STATUS</Label>
        	    </Cell>
	        </Row>
    	</Head>
	    <Body>
		{#each recent_institutions as m}
			<Row data-entry-id="{m.id}">
				<Cell>{m['__status_change_date']}</Cell>
				<Cell>{m['name_full']}</Cell>
				<Cell>{m['__status'] == 'inactive' ? 'deactivated' : 'created/activated'} </Cell>
			</Row>
		{/each}
		</Body>
		</DataTable>
	{:else}
		<p> NO RECENT CHANGES </p>
	{/if}

	{#if changed_institutions.length}
        <p style="font-weight: bold;">LATEST CHANGES -
          <Select bind:value={lastentries} noLabel>
            <Option value={50}>50 ENTRIES</Option>
            <Option value={100}>100 ENTRIES</Option>
            <Option value={250}>250 ENTRIES</Option>
            <Option value={1000}>1000 ENTRIES</Option>
          </Select>
        :</p>
	    <DataTable table$aria-label="Member List" style="width: 100%;" on:SMUIDataTableRow:click={handleRowClick} >
    	<Head>
        	<Row>
	            <Cell columnId="m_date" style="text-align: center;">
    	            <Label>DATE</Label>
        	    </Cell>
            	<Cell columnId="m_inst" style="text-align: center;">
                	<Label>INSTITUTION</Label>
	            </Cell>
	            <Cell columnId="m_status" style="text-align: center;">
    	            <Label>STATUS</Label>
        	    </Cell>
	        </Row>
    	</Head>
	    <Body>
		{#each changed_institutions as m}
			<Row data-entry-id="{m.id}">
				<Cell>{m['__status_change_date']}</Cell>
				<Cell>{m['name_full']}</Cell>
				<Cell>{m['__status'] == 'inactive' ? 'deactivated' : 'created/activated'} </Cell>
			</Row>
		{/each}
		</Body>
		</DataTable>
	{/if}

</Paper>
{/await}

{/key}
{/key}
{/key}

{:else}
	<AccessDenied />
{/if}
