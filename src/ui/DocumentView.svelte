<script>

import {meta, router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import Button, { Icon as ButtonIcon, Label as ButtonLabel } from '@smui/button';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';

import { getDocument, getDocumentFields } from '../utils/pnb-api.js';
import { convertDocument } from '../utils/pnb-convert.js';

import { getMembers } from '../utils/pnb-api.js';
import { convertMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';
import { getEvents, getGroups } from '../utils/pnb-api.js';
import { convertEvents } from '../utils/pnb-convert.js';
import { find_field_id } from '../utils/pnb-search.js';
import { getWorkflowMap, getWorkflow } from '../utils/pnb-api.js';

import { document_id, auth } from '../store.js';
import { downloadDocument } from '../utils/pnb-download.js';

let title = '', subtitle = '';

let members = false, events = false, groups;
let members_fid, reviewers_fid, event_fid, author_fid, group_fid, url_fid;
let wmap = false, workflow = false;

const find_member = ( mid ) => {
    for ( const m of members ) {
        if ( m.id == mid ) { return m.name_first + ' ' + m.name_last; }
    }
    return 'N/A';
}

const find_group = ( gid ) => {
    for ( const g of groups ) {
        if ( g.id == gid ) { return g.name; }
    }
    return 'N/A';
}

const find_event = ( eid ) => {
    for ( const e of events ) {
        if ( e.id == eid ) { return e.name; }
    }
    return 'N/A';
}

const fetchDocument = async ( id ) => {

    const mem = await getMembers();
    members = await convertMembers( mem );
    sortConvertedMembers( members );

	groups = await getGroups();

    wmap = await getWorkflowMap( id );
    if ( wmap.map ) { wmap = wmap.map; }
    if ( wmap ) {
        workflow = await getWorkflow( wmap.workflow_id );
        if ( workflow.workflow ) { workflow = workflow.workflow; }
    }

	if ( workflow ) {
		subtitle = 'Assigned workflow: ' + workflow.name;
	} else {
		subtitle = 'Workflow is not assigned';
	}

    const evt = await getEvents();
    events = await convertEvents( evt );
    events = [ { id: 0, name: 'No Event' }, ...events ];

	let data = [],
		i = await downloadDocument( id );

    if ( id && i.cdocument) {
        title = i.cdocument.title || 'N/A';
    }

    members_fid = find_field_id( i.document_fields, 'member_ids' );
    reviewers_fid = find_field_id( i.document_fields, 'reviewer_ids' );
    event_fid   = find_field_id( i.document_fields, 'event_id' );
    author_fid  = find_field_id( i.document_fields, 'author_id' );
    group_fid   = find_field_id( i.document_fields, 'group_id' );
	url_fid 	= find_field_id( i.document_fields, 'url' );

	for ( const id of i.document_fields_ordered ) {
		if ( i.document_fields[ id ].is_enabled != 'y' ) { continue; }
		if ( i.document_fields[ id ].privacy !== 'public' && !( $auth['role'] == 'ADMIN' || $auth['role'] == 'EDITOR' ) ) { continue; }

       	data.push({
           	id: parseInt(id),
            desc: i.document_fields[id].name_desc,
            value: i.cdocument[ i.document_fields[id].name_fixed ] || ''
        });
    }

	return data;
}

</script>

{#await fetchDocument( $document_id )}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
<div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>

<Paper>
<DataTable table$aria-label="Document Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="field" style="width: 20%; text-align: left;">
                <Label>FIELD</Label>
            </Cell>
            <Cell columnId="value" style="width: 60%; text-align: left;">
                <Label>VALUE</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each data as item (item.id)}
      <Row data-entry-id="{item.id}">
        <Cell style="width: 30%; font-weight: bold;">{item.desc}</Cell>
        <Cell style="width: 70%; white-space: normal;">
            {#if item.id == members_fid }
				{#each item.value.split(',') as mem}
					{#if mem.length}
	                <Button color="primary" variant="unelevated" style="margin-right: 1vmin; margin-bottom: 1vmin;" on:click={() => { router.goto('/member/' + mem + '/view'); }}>
						<ButtonIcon class="material-icons">link</ButtonIcon>
						<ButtonLabel> { find_member( mem ) } </ButtonLabel>
					</Button>
					&nbsp;
					{/if}
				{/each}

            {:else if item.id == reviewers_fid }
				{#each item.value.split(',') as mem}
					{#if mem.length}
	                <Button color="secondary" variant="unelevated" style="margin-right: 1vmin; margin-bottom: 1vmin;" on:click={() => { router.goto('/member/' + mem + '/view'); }}>
						<ButtonIcon class="material-icons">link</ButtonIcon>
						<ButtonLabel> { find_member( mem ) } </ButtonLabel>
					</Button>
					&nbsp;
					{/if}
				{/each}
            {:else if item.id == url_fid }
				{#if item.value && item.value.length}
	                <Button color="primary" variant="unelevated" href="{item.value}" target="_blank">
						<ButtonIcon class="material-icons">open_in_new</ButtonIcon>
						<ButtonLabel> {item.value} </ButtonLabel>
					</Button>
				{:else}
					NOT PROVIDED
				{/if}
			{:else if item.id == author_fid && item.value }
                <Button color="primary" variant="unelevated" on:click={() => { router.goto('/member/' + item.value + '/view'); }}>
					<ButtonIcon class="material-icons">link</ButtonIcon>
					<ButtonLabel> { find_member( item.value ) } </ButtonLabel>
				</Button>
			{:else if item.id == group_fid && item.value }
                <Button color="secondary" variant="unelevated" on:click={() => { router.goto('/group/' + item.value + '/view'); }}>
					<ButtonIcon class="material-icons">link</ButtonIcon>
					<ButtonLabel> { find_group( item.value ) } </ButtonLabel>
				</Button>

            {:else if item.id == event_fid && item.value }
                <Button color="secondary" variant="unelevated" on:click={() => { router.goto('/event/' + item.value + '/view'); }}>
					<ButtonIcon class="material-icons">link</ButtonIcon>
					<ButtonLabel> { find_event( item.value ) } </ButtonLabel>
				</Button>

            {:else}
				{item.value}
			{/if}
		</Cell>
      </Row>
    {/each}
    </Body>
</DataTable>
</Paper>

{/await}

<style>

</style>