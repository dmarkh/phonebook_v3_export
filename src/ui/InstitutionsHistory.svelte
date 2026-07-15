<script>

import {router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';

import AccessDenied from './AccessDenied.svelte';

import { getInstitutionsHistory } from '../utils/pnb-api.js';
import { getInstitutions, getInstitutionFields } from '../utils/pnb-api.js';
import { convertField } from '../utils/pnb-convert.js';

import { screen, institution_id, institution_mode } from '../store.js';
import { auth } from '../store.js';

let title = '', subtitle = '';

let inst, ifields;
let i_name_full;

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

const getInstitutionName = ( iid ) => {
    if ( !inst[iid] ) { return 'N/A'; }

    return trim_ellipsis( inst[iid].fields[ i_name_full ], 50 );
}

const downloadHistory = async ( id ) => {
    let data = [];

	inst = await getInstitutions();
    ifields = await getInstitutionFields();
	let hidata = await getInstitutionsHistory();

	i_name_full = getFieldId( 'name_full', ifields );

	let idctr = 0;
	for ( const [k,v] of Object.entries(hidata) ) {
		v['id'] = ++idctr;
        if ( !v['institutions_fields_id'] || !ifields[ v['institutions_fields_id'] ] ) {
            v['field'] = 'deactive field';
            v['old_value'] = 'N/A';
            v['new_value'] = 'N/A';
            continue;
        }
		v['field'] = ifields[ v['institutions_fields_id'] ]['name_desc'] || '';
		if ( v['value_to_string'] || v['value_from_string'] ) {
			v['old_value'] = convertField( ifields[ v['institutions_fields_id'] ], v['value_from_string'] );
			v['new_value'] = convertField( ifields[ v['institutions_fields_id'] ], v['value_to_string'] );
		} else if ( 
			( v['value_from_date'] && v['value_from_date'] != '0000-00-00 00:00:00' && v['value_from_date'] != '0000-00-00' )
			|| ( v['value_to_date'] && v['value_to_date'] != '0000-00-00 00:00:00' && v['value_to_date'] != '0000-00-00' ) ) {
			v['old_value'] = v['value_from_date'];
			v['new_value'] = v['value_to_date'];
		} else {
			v['old_value'] = v['value_from_int'];
			v['new_value'] = v['value_to_int'];
		}
	}

    title = 'LATEST CHANGES: INSTITUTIONS';
    subtitle = 'LAST 1000 ENTRIES';

    return hidata;
}

</script>

{#if $auth['grants']['institutions-history']}

{#await downloadHistory()}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
<div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>
<Paper>
<DataTable table$aria-label="Institutions History Data" style="width: 100%;" on:SMUIDataTableRow:click={handleRowClick} >
    <Head>
        <Row>
            <Cell columnId="date" style="width: 10%; text-align: left;">
                <Label>DATE</Label>
            </Cell>
            <Cell columnId="field" style="width: 20%; text-overflow: ellipsis; text-align: left;">
                <Label>INSTITUTION</Label>
            </Cell>
            <Cell columnId="field" style="text-align: left;">
                <Label>FIELD</Label>
            </Cell>
            <Cell columnId="old-value" style="text-align: left;">
                <Label>OLD VALUE</Label>
            </Cell>
            <Cell columnId="empty" style="text-align: left;">
                <Label></Label>
            </Cell>
            <Cell columnId="new-value" style="text-align: left;">
                <Label>NEW VALUE</Label>
            </Cell>
            <Cell columnId="metadata" style="text-align: left;">
                <Label>METADATA</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each data as item (item.id)}
      <Row data-entry-id="{item.institutions_id}">
        <Cell style="width: 10%;font-weight: bold;">{item.date}</Cell>
        <Cell style="width: 20%;">{getInstitutionName(item.institutions_id)}</Cell>
        <Cell style="">{item.field}</Cell>
        <Cell style="">{item.old_value}</Cell>
        <Cell style=""> => </Cell>
        <Cell style="">{item.new_value}</Cell>
        <Cell style="">{item.user} {item.ip}</Cell>
      </Row>
    {/each}
    </Body>
</DataTable>
</Paper>
{/await}

{:else}
	<AccessDenied />
{/if}

