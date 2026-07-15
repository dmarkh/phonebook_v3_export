<script>

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';

import AccessDenied from './AccessDenied.svelte';

import { getInstitutionHistory } from '../utils/pnb-api.js';
import { getInstitution, getInstitutionFields, getInstitutionFieldgroups } from '../utils/pnb-api.js';
import { convertInstitution, convertField } from '../utils/pnb-convert.js';
import { listInstitutions } from '../utils/pnb-download.js';
import { institution_id } from '../store.js';
import { auth } from '../store.js';

let title = '', subtitle = '';

let institution_ids_sorted = [];

const locateInstitutionName = ( inst_id ) => {
    for( const [k,v] of institution_ids_sorted ) {
        if ( k == inst_id ) { return v; }
    }
    return '';
}

const downloadInstitution = async ( id ) => {
    let data = [];
    let idata = await getInstitution( id );
    let ifields = await getInstitutionFields();
    let igroups = await getInstitutionFieldgroups();
    let cidata = await convertInstitution( idata );
    title = cidata.name_full;
	subtitle = cidata.country || 'COUNTRY NOT SET';
	let hidata = await getInstitutionHistory( id );

	institution_ids_sorted = await listInstitutions();

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
		if ( ifields[ v['institutions_fields_id'] ]['name_fixed'] == 'associated_id' ) {
			v['old_value'] = locateInstitutionName( v['value_from_int'] );
			v['new_value'] = locateInstitutionName( v['value_to_int'] );

		} else if ( v['value_to_string'] || v['value_from_string'] ) {
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

    return hidata;
}

</script>

{#if $auth['grants']['institutions-history']}

{#await downloadInstitution( $institution_id )}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
<div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>
<Paper>
<DataTable table$aria-label="Institution History Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="date" style="width: 16%; text-align: left;">
                <Label>DATE</Label>
            </Cell>
            <Cell columnId="field" style="width: 14%; text-align: left;">
                <Label>FIELD</Label>
            </Cell>
            <Cell columnId="old-value" style="width: 14%; text-align: left;">
                <Label>OLD VALUE</Label>
            </Cell>
            <Cell columnId="empty" style="width: 14%; text-align: left;">
                <Label></Label>
            </Cell>
            <Cell columnId="new-value" style="width: 14%; text-align: left;">
                <Label>NEW VALUE</Label>
            </Cell>
            <Cell columnId="metadata" style="width: 14%; text-align: left;">
                <Label>METADATA</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each data as item (item.id)}
      <Row>
        <Cell style="width: 16%; font-weight: bold; white-space: normal;">{item.date}</Cell>
        <Cell style="width: 14%; white-space: normal;">{item.field}</Cell>
        <Cell style="width: 14%; white-space: normal;">{item.old_value}</Cell>
        <Cell style="width: 14%; white-space: normal;"> => </Cell>
        <Cell style="width: 14%; white-space: normal;">{item.new_value}</Cell>
        <Cell style="width: 14%; white-space: normal;">{item.user} {item.ip}</Cell>
      </Row>
    {/each}
    </Body>
</DataTable>
</Paper>
{/await}

{:else}
	<AccessDenied />
{/if}

