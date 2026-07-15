<script>

import {meta, router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';

import Card, {
    Content,
    PrimaryAction,
    Actions,
    ActionButtons,
    ActionIcons,
  } from '@smui/card';
import Badge from '@smui-extra/badge';

import PleaseWait from './PleaseWait.svelte';
import AccessDenied from './AccessDenied.svelte';
import { sleep } from '../utils/sleep.js';

import { find_field_id } from '../utils/pnb-search.js';

import {
	getDocument,
	getDocumentFields,
	getWorkflowMap,
	removeWorkflowMap,
	getWorkflows,
	getWorkflow,
	getWorkflowBlocks,
	getConfiguredWorkflowBlocks,
	getWorkflowProgress,
	createWorkflowMap,
	advanceWorkflow,
	resetWorkflow
} from '../utils/pnb-api.js';

import { convertDocument, convertField } from '../utils/pnb-convert.js';
import { document_id } from '../store.js';
import { auth } from '../store.js';

import { getGroups, getMembers, getGroupRoles, listGroupRoles } from '../utils/pnb-api.js';
import { convertMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';

const mid = window.pnb.mid ? parseInt(window.pnb.mid) : null;

let title = '', subtitle = '';
let pleaseWait = false;
let refresh = false;

let doc = false;
let wmap = false;
let cblocks = false;
let workflows = false;
let workflow = 0;
let workflow_select = 0;
let wprogress = false;

let groups = false;
let groups_lookup = {};

let groups_roles = false;
let groups_roles_lookup = {};

let members = false;
let members_lookup = {};

const downloadDocument = async ( id ) => {

    let data = [];

    let idata = await getDocument( id );
    let ifields = await getDocumentFields();
    doc = await convertDocument( idata );

	wmap = await getWorkflowMap( id );
	if ( wmap.map ) { wmap = wmap.map; }

	if ( wmap ) {
		workflow = await getWorkflow(wmap.workflow_id);
	    if ( workflow.workflow ) { workflow = workflow.workflow; }
		if ( workflow ) {
			wprogress = await getWorkflowProgress( id, wmap.workflow_id );
			if ( wprogress.progress ) { wprogress = wprogress.progress; }
		}
	}

	workflows = await getWorkflows();

    groups = await getGroups();
    for ( const grp of groups ) {
        groups_lookup[grp.id] = grp;
    }

    groups_roles = await listGroupRoles();
    for ( const gr of groups_roles ) {
        groups_roles_lookup[ gr.id ] = gr;
    }

    const mem = await getMembers();
    members = await convertMembers( mem );
    sortConvertedMembers( members );
    for ( const m of members ) {
        members_lookup[ m.id ] = m;
    }

    title = doc.title;

    return doc;
}

const fetchWorkflowConfig = async ( id ) => {

    // fetch all available blocks
    let blocks = await getWorkflowBlocks();
    if ( blocks.blocks ) { blocks = blocks.blocks; }

    // fetch configured blocks, if there are any
    let conb = await getConfiguredWorkflowBlocks( id );
    if ( conb.data ) { conb = conb.data; }

    // convert blocks
    cblocks = [];
    if ( conb && conb.length ) {
        for ( const cb of conb ) {
            cblocks.push(
                JSON.parse(JSON.stringify(
                    { ...blocks[ cb.block_id ], group_id: cb.group_id, member_ids: cb.member_ids, group_role_ids: cb.group_role_ids }
                ))
            );
        }
    }
    return cblocks;
}

const unassignWorkflow = async () => {
    pleaseWait = 'UNASSIGNING WORKFLOW, PLEASE WAIT';
	if ( !workflow || !workflow.id ) { return; }

    const data = {
		"document_id": $document_id,
		"workflow_id": workflow.id
    };

    let rc = await removeWorkflowMap( data );
    await sleep(1000);

	pleaseWait = false;
	refresh = !refresh;
}

const assignWorkflow = async () => {
	if ( !workflow_select ) { return; }

    pleaseWait = 'ASSIGNING WORKFLOW, PLEASE WAIT';

    const data = {
		"document_id": $document_id,
		"workflow_id": workflow_select
    };

    let rc = await createWorkflowMap( data );
    await sleep(1000);

	pleaseWait = false;
	refresh = !refresh;

}

const advanceCurrentWorkflow = async() => {
    pleaseWait = 'PROCESSING WORKFLOW, PLEASE WAIT';
    let rc = await advanceWorkflow( $document_id );
    await sleep(1000);
	pleaseWait = false;
	refresh = !refresh;
}

const resetCurrentWorkflow = async() => {
    pleaseWait = 'PROCESSING WORKFLOW, PLEASE WAIT';
    let rc = await resetWorkflow( $document_id );
    await sleep(1000);
	pleaseWait = false;
	refresh = !refresh;
}

const handleWorkflowChange = async( e ) => {
	console.log('select change', e.detail.value );
	console.log( 'workflow: ', workflows.find( w => w.id == e.detail.value ) );
	// TODO: list workflow blocks
}

</script>

{#key refresh}

{#if $auth['grants']['workflows-view']}

{#await downloadDocument( $document_id )}

<LinearProgress indeterminate />

{:then}

<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
<div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>

<Paper>

    {#if pleaseWait}
   	    <PleaseWait text="{pleaseWait}" />
    {:else}

	{#if !wmap}

	{#if $auth['grants']['workflows-edit'] || ( doc.author_id == mid ) }
		<p>SET UP A WORKFLOW FOR THIS DOCUMENT:</p>

        <Select
            bind:value={ workflow_select }
            style="width: 100%;"
            label="WORKFLOW"
            required=true
			on:SMUISelect:change={handleWorkflowChange}
        >
            <Option value="0">--- NOT SET ---</Option>
			{#each Object.values(workflows) as wfl}
	            <Option value="{wfl.id}">{wfl.name}</Option>
			{/each}
        </Select>

	    <p style="text-align: center;">
        <Button on:click={() => { assignWorkflow(); }} variant="raised">
            <ButtonIcon class="material-icons">save</ButtonIcon>
            <ButtonLabel>ASSIGN WORKFLOW</ButtonLabel>
        </Button>
    	</p>
	{:else}
		<p style="text-align: center;">You are not authorized to set Workflows for this document.</p>
	{/if}

	{:else}

		<DataTable table$aria-label="Document Workflow" style="width: 100%;">
		    <Head>
		        <Row>
		            <Cell columnId="field" style="width: 10%; text-align: center;">
                		<Label>STATE</Label>
        		    </Cell>
		            <Cell columnId="field" style="width: 20%; text-align: center;">
                		<Label>WORKFLOW</Label>
        		    </Cell>
		            <Cell columnId="value" style="width: 50%; text-align: center;">
        		        <Label>DESCRIPTION</Label>
		            </Cell>
		            <Cell columnId="value" style="width: 20%; text-align: center;">
        		        <Label>ACTION</Label>
		            </Cell>
        		</Row>
		    </Head>
		    <Body>
		      <Row>
		        <Cell style="width: 20%; font-weight: bold; text-align: center;">
					{#if wmap.status == 0}
						OPEN ( { wmap.current_step_id } / { cblocks.length || 0 })
					{:else if wmap.status == 1}
						COMPLETED
					{:else}
						UNDEFINED ({wmap.status})
					{/if}
				</Cell>
		        <Cell style="width: 20%; font-weight: bold;">
					{workflow.name}
				</Cell>
		        <Cell style="width: 50%; font-weight: bold;">
					{workflow.description}
				</Cell>
		        <Cell style="width: 20%; font-weight: bold; text-align: center;">

			{#if $auth['grants']['workflows-edit']}
			        <Button on:click={() => { unassignWorkflow(); }} variant="raised" style="margin-right: 1vmin;">
       				    <ButtonIcon class="material-icons">delete</ButtonIcon>
			            <ButtonLabel>UNASSIGN WORKFLOW</ButtonLabel>
	    		    </Button>
			        <Button on:click={() => { resetCurrentWorkflow(); }} variant="raised" style="margin-right: 1vmin;">
       				    <ButtonIcon class="material-icons">restart_alt</ButtonIcon>
			            <ButtonLabel>RESET WORKFLOW</ButtonLabel>
	    		    </Button>
			{/if}
			{#if ( doc.author_id == mid ) || $auth['grants']['workflows-edit']}
			        <Button on:click={() => { advanceCurrentWorkflow(); }} variant="raised" style="margin-right: 1vmin;">
       				    <ButtonIcon class="material-icons">play_circle</ButtonIcon>
			            <ButtonLabel>RUN WORKFLOW</ButtonLabel>
	    		    </Button>
			{/if}
				</Cell>
			  </Row>
			</Body>
		</DataTable>

		<table style="width: 100%;">
		<tr><td style="vertical-align: top; width: 50%;">

		<p style="text-align: center;">Workflow Steps</p>

		{#await fetchWorkflowConfig(wmap.workflow_id)}
			<LinearProgress indeterminate />
		{:then cblocks}

        {#if cblocks && cblocks.length }
                <div id="configured-blocks">
                    {#each cblocks as item, idx}
                        <Card style="margin-bottom: 1vmin;">
                            <Content>
                                <span style="position: relative;">
                                    {item.name}
									{#if idx < wmap.current_step_id }
                                    <Badge position="outset" align="top-start" color="secondary" aria-label="block position">{idx+1}</Badge>
									{:else}
                                    <Badge position="outset" align="top-start" color="primary" aria-label="block position">{idx+1}</Badge>
									{/if}
                                </span>
                                <br /> {item.description}

                               {#if item.member_ids && item.member_ids.length }
                                    <hr/>
                                    CONFIGURED MEMBERS:<br/>
                                    {#each item.member_ids.split(',') as mem }
                                    {#if members_lookup[mem]}
                                        <Button color="primary" variant="unelevated" style="margin-right: 1vmin; margin-bottom: 1vmin;" on:click={() => { router.goto('/member/' + mem + '/view'); }}>
                                          <ButtonIcon class="material-icons">link</ButtonIcon>
                                          <ButtonLabel>{members_lookup[mem].name_first} {members_lookup[mem].name_last}</ButtonLabel>
                                        </Button>
                                    {:else}
                                        DEACTIVATED MEMBER
                                    {/if}
                                    {/each}
                                {/if}
                                {#if item.group_id && item.group_id > 0 }
                                    <hr/>
                                    CONFIGURED GROUP:<br/>
                                    {#if groups_lookup[item.group_id]}
                                        <Button color="primary" variant="unelevated" on:click={() => { router.goto('/group/' + item.group_id + '/view'); }}>
                                          <ButtonIcon class="material-icons">link</ButtonIcon>
                                          <ButtonLabel> {groups_lookup[item.group_id].name}</ButtonLabel>
                                        </Button>
                                    {:else}
                                        DEACTIVATED GROUP
                                    {/if}
                                {/if}
                                {#if item.group_role_ids}
                                    <hr/>
                                    CONFIGURED GROUP ROLES:<br/>
                                    {#each item.group_role_ids.split(',') as grid}
                                    <Button color="secondary" variant="unelevated" style="margin-right: 1vmin;">
                                      <ButtonLabel> {groups_roles_lookup[grid] ? groups_roles_lookup[grid].role : 'No Role'}</ButtonLabel>
                                    </Button>
                                    {/each}
                                {/if}

                            </Content>
                        </Card>
                    {/each}
                </div>

        {:else}
        <p style="text-align: center; color: #900;">WARNING: No Workflow Blocks configured yet.</p>
        {/if}
		{/await}

		</td><td style="vertical-align: top; width: 50%;">
		<p style="text-align: center;">Workflow Actions History</p>
		<div id="action-history">
		{#if wprogress && wprogress.length}
			{#each wprogress as item}
	            <Card style="margin-bottom: 1vmin;">
                    <Content>
						<div style="position: relative;">
                            <Button color="secondary" variant="unelevated" style="margin-right: 1vmin;">
	                       		{item.operation}
							</Button>
							{#if item.member_id && item.member_id != 0 }
	                            <Button color="primary" variant="unelevated" on:click={() => { router.goto('/member/' + item.member_id + '/view'); }}>
    	                            <ButtonIcon class="material-icons">link</ButtonIcon>
        	                        <ButtonLabel> {item.member_name}</ButtonLabel>
                                </Button>
							{:else}
	                            <Button color="primary" variant="unelevated">
									{item.member_name}
                                </Button>
							{/if}
							<Badge position="outset" align="top-start" color="secondary" aria-label="block position">{item.step_id}</Badge>
						</div>
						{#if item.metadata && item.metadata.length}
						<div style="margin-top: 2vmin;">
							{item.metadata}
						</div>
						{/if}
                    </Content>
                    <Actions fullBleed>
                        &nbsp;&nbsp;<b>{item.created}, STEP</b>
        	        </Actions>
    	        </Card>
			{/each}
		{:else}
			<p style="text-align: center;"> NO WORKFLOW ACTIONS REGISTERED YET</p>
		{/if}
		</div>
		</td></tr></table>

	{/if}

	{/if}


</Paper>

{/await}

{:else}
	<AccessDenied />
{/if}

{/key}

<style>
#configured-blocks {
    margin-top: 3vmin;
    background-color: #CCC;
    padding: 1vmin;
    box-sizing: border-box;
    border-radius: 1vmin;
}
#action-history {
    margin-top: 3vmin;
    background-color: #CCC;
    padding: 1vmin;
    box-sizing: border-box;
    border-radius: 1vmin;
}
</style>
