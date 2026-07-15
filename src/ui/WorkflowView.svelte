<script>

import {meta, router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import IconButton from '@smui/icon-button';
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

import { getWorkflow, getWorkflowBlocks, getConfiguredWorkflowBlocks } from '../utils/pnb-api.js';
import { workflow_id, auth } from '../store.js';

import { getGroups, getMembers, getGroupRoles, listGroupRoles } from '../utils/pnb-api.js';
import { convertMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';

let title = '', subtitle = '';
let workflow = false;

let groups = false;
let groups_lookup = {};

let groups_roles = false;
let groups_roles_lookup = {};

let members = false;
let members_lookup = {};

const fetchWorkflow = async ( id ) => {
    workflow = await getWorkflow( id );
	if ( workflow.workflow ) { workflow = workflow.workflow; }

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

	return workflow;
}

const fetchWorkflowConfig = async ( id ) => {

    // fetch all available blocks
    let blocks = await getWorkflowBlocks();
    if ( blocks.blocks ) { blocks = blocks.blocks; }

    // fetch configured blocks, if there are any
    let conb = await getConfiguredWorkflowBlocks( id );
    if ( conb.data ) { conb = conb.data; }

	// convert blocks
	let cblocks = [];
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

</script>

{#await fetchWorkflow( $workflow_id )}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">WORKFLOW</div>

<Paper>

        <DataTable table$aria-label="Document Workflow" style="width: 100%;">
            <Head>
                <Row>
                    <Cell columnId="field" style="width: 20%; text-align: center;">
                        <Label>WORKFLOW</Label>
                    </Cell>
                    <Cell columnId="value" style="width: 60%; text-align: center;">
                        <Label>DESCRIPTION</Label>
                    </Cell>
                    <Cell columnId="value" style="width: 20%; text-align: center;">
                        <Label>STATUS</Label>
                    </Cell>
                </Row>
            </Head>
            <Body>
              <Row>
                <Cell style="width: 20%; font-weight: bold;">
                    {workflow.name}
                </Cell>
                <Cell style="width: 60%; font-weight: bold; white-space: normal;">
                    {workflow.description}
                </Cell>
                <Cell style="width: 20%; font-weight: bold; text-align: center;">
					{workflow.status}
                </Cell>
              </Row>
            </Body>
        </DataTable>

	{#await fetchWorkflowConfig( $workflow_id )}
		<LinearProgress indeterminate />
	{:then cblocks}
		{#if cblocks && cblocks.length }
                <div id="configured-blocks">
                    {#each cblocks as item, idx}
                        <Card style="margin-bottom: 1vmin;">
                            <Content>
                                <span style="position: relative;">
                                    {item.name}
                                    <Badge position="outset" align="top-start" aria-label="block position">{idx+1}</Badge>
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
		<p style="text-align: center; color: #900;">No Workflow Blocks configured yet. Please use the CONFIG tab.</p>
		{/if}
	{/await}

</Paper>

{/await}

<style>

#configured-blocks {
	margin-top: 3vmin;
	background-color: #CCC;
	padding: 1vmin;
	box-sizing: border-box;
	border-radius: 1vmin;
}

</style>