<script>

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';

import AccessDenied from './AccessDenied.svelte';

import { getMembers } from '../utils/pnb-api.js';
import { convertMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';
import { getEvents } from '../utils/pnb-api.js';
import { convertEvents } from '../utils/pnb-convert.js';

import { find_field_id } from '../utils/pnb-search.js';

import { getTaskHistory } from '../utils/pnb-api.js';
import { getTask, getTaskFields } from '../utils/pnb-api.js';
import { convertTask, convertField } from '../utils/pnb-convert.js';
import { task_id } from '../store.js';
import { auth } from '../store.js';

let title = '', subtitle = '';

let members = false, events = false;
let members_fid, event_fid;

const find_member = ( mid ) => {
    for ( const m of members ) {
        if ( m.id == mid ) { return m.name_first + ' ' + m.name_last; }
    }
    return 'N/A';
}

const find_event = ( eid ) => {
    for ( const e of events ) {
        if ( e.id == eid ) { return e.name; }
    }
    return 'N/A';
}

const downloadTask = async ( id ) => {

/*
	// FIXME: members, groups
    const mem = await getMembers();
    members = await convertMembers( mem );
    sortConvertedMembers( members );

    const evt = await getEvents();
    events = await convertEvents( evt );
    events = [ { id: 0, name: 'No Event' }, ...events ];
*/

    let data = [];
    let idata = await getTask( id );
    let ifields = await getTaskFields();
    let cidata = await convertTask( idata );
    title = cidata.title;
	let hidata = await getTaskHistory( id );

/*
	// FIXME:
    members_fid = find_field_id( ifields, 'member_ids' );
    event_fid   = find_field_id( ifields, 'event_id' );
*/
	let idctr = 0;
	for ( const [k,v] of Object.entries(hidata) ) {
		v['id'] = ++idctr;
		v['field'] = ifields[ v['tasks_fields_id'] ]['name_desc'] || '';
		if ( v['value_to_string'] || v['value_from_string'] ) {
			v['old_value'] = convertField( ifields[ v['tasks_fields_id'] ], v['value_from_string'] );
			v['new_value'] = convertField( ifields[ v['tasks_fields_id'] ], v['value_to_string'] );
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

{#if $auth['grants']['tasks-history']}

{#await downloadTask( $task_id )}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
<div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>
<Paper>
<DataTable table$aria-label="Task History Data" style="width: 100%;">
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
        <Cell style="width: 16%; font-weight: bold;">{item.date}</Cell>
        <Cell style="width: 14%;">{item.field}</Cell>
        <Cell style="width: 14%;">
            {#if item.tasks_fields_id == members_fid}
                { item.old_value.split(',').reduce( (acc,cur) => { acc.push( find_member(cur) ); return acc; }, [] ).join(', ') }
            {:else if item.tasks_fields_id == event_fid}
                { find_event( item.old_value ) }
            {:else}
				{item.old_value}
			{/if}
		</Cell>
        <Cell style="width: 14%;"> => </Cell>
        <Cell style="width: 14%;">
            {#if item.tasks_fields_id == members_fid}
                { item.new_value.split(',').reduce( (acc,cur) => { acc.push( find_member(cur) ); return acc; }, [] ).join(', ') }
            {:else if item.tasks_fields_id == event_fid}
                { find_event( item.new_value ) }
            {:else}
				{item.new_value}
			{/if}
		</Cell>
        <Cell style="width: 14%;">{item.user} {item.ip}</Cell>
      </Row>
    {/each}
    </Body>
</DataTable>
</Paper>
{/await}

{:else}
	<AccessDenied />
{/if}

