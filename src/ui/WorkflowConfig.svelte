<script>

import {meta, router, Route} from 'tinro';

import AccessDenied from './AccessDenied.svelte';
import PleaseWait from './PleaseWait.svelte';
import LinearProgress from '@smui/linear-progress';
import Dialog, { Title as DialogTitle, Content as DialogContent, Actions as DialogActions, InitialFocus as DialogInitialFocus } from '@smui/dialog';

import Chip, { Set, TrailingAction, Text } from '@smui/chips';
import Autocomplete from '@smui-extra/autocomplete';
import Select, { Option } from '@smui/select';
import IconButton from '@smui/icon-button';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';
import CharacterCounter from '@smui/textfield/character-counter';
import Paper from '@smui/paper';
import Badge from '@smui-extra/badge';
import Checkbox from '@smui/checkbox';
import FormField from '@smui/form-field';

import Card, {
    Content,
    PrimaryAction,
    Actions,
    ActionButtons,
    ActionIcons,
  } from '@smui/card';

import { getWorkflow, getWorkflowBlocks, getConfiguredWorkflowBlocks, addWorkflowConfig, deleteWorkflowConfig, updateWorkflow } from '../utils/pnb-api.js';

import { getGroups, getMembers, getGroupRoles, listGroupRoles } from '../utils/pnb-api.js';
import { convertMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';

import { sleep } from '../utils/sleep.js';
import { screen, auth, workflow_id } from '../store.js';

let pleaseWait = false;
let workflow = false;
let active_block = false;
let blocks = false;
let cblocks = [];
let title = '';
let subtitle = '';

let members = false;
let members_lookup = {};
let members_list = [];
let member_value = '';

let groups = false;
let groups_lookup = {};
let group_value = '';

let group_roles = false;
let group_roles_value = [];
let group_role_options = false;

let group_roles_lookup = {};
let group_roles_list = false;

let configure_block_idx = false;
let configure_members = false;
let configure_group = false;
let configure_group_and_roles = false;

const handle_group_selection = ( event ) => {
    event.preventDefault();
    group_value = event.detail;
	if ( configure_group_and_roles ) {
		setTimeout(async () => {
			const roles = await getGroupRoles( event.detail.id );
			for( const r of roles ) { r.disabled = false; }
			roles.push({ id: 0, role: "No Role", disabled: false });
			group_role_options = roles;
		});
	}
}

const handle_member_selection = ( event ) => {
    event.preventDefault();
	if ( members_list.find( m => m.id == event.detail.id ) ) { return; }
    members_list = [ ...members_list, event.detail ];
	member_value = event.detail;
}

const fetchWorkflow = async ( id ) => {

    workflow = await getWorkflow( id );
	if ( workflow.workflow ) { workflow = workflow.workflow; }

	title = 'WORKFLOW: ' + workflow.name;
	subtitle = workflow.description;

	// fetch all available blocks
	blocks = await getWorkflowBlocks();
	if ( blocks.blocks ) { blocks = blocks.blocks; }

	// fetch configured blocks, if there are any
	let conb = await getConfiguredWorkflowBlocks( id );
	if ( conb.data ) { conb = conb.data; }

	// convert configured blocks
	if ( conb && conb.length ) {
		cblocks = [];
		for ( const cb of conb ) {
			cblocks.push(
				JSON.parse(JSON.stringify(
					{ ...blocks[ cb.block_id ], group_id: cb.group_id, member_ids: cb.member_ids, group_role_ids: cb.group_role_ids }
				))
			);
		}
	}

	groups = await getGroups();
	for ( const grp of groups ) {
		groups_lookup[grp.id] = grp;
	}

	group_roles_list = await listGroupRoles();
	for ( const gr of group_roles_list ) {
		group_roles_lookup[ gr.id ] = gr;
	}

    const mem = await getMembers();
    members = await convertMembers( mem );
    sortConvertedMembers( members );
	for ( const m of members ) {
		members_lookup[ m.id ] = m;
	}


    return workflow;
}

const saveWorkflowConfig = async () => {
	pleaseWait = 'SAVING WORKFLOW CONFIG';
	let rc = await deleteWorkflowConfig( workflow.id );
	for ( const [idx, cblock] of cblocks.entries() ) {
		const data = {
			"workflow_id": workflow.id,
			"block_id": cblock.id,
			"step_id": idx,
			"member_ids": ( cblock.member_ids || false ),
			"group_id": ( cblock.group_id || false ),
			"group_role_ids": ( cblock.group_role_ids || false )
		};
		let rc = await addWorkflowConfig( data );
	}
	await sleep(1000);
	pleaseWait = false;

    $screen = 'workflows';
    router.goto('/workflow/' + workflow.id + '/view' );

	return true;
}

const swap = ( arr, indexA, indexB ) => {
  if (indexA >= 0 && indexA < arr.length && indexB >= 0 && indexB < arr.length) {
    [arr[indexA], arr[indexB]] = [arr[indexB], arr[indexA]];
  }
}

</script>

<Dialog
  open={configure_members}
  aria-labelledby="default-focus-title"
  aria-describedby="default-focus-content"
  scrimClickAction=""
  escapeKeyAction=""
>
  <DialogTitle id="default-focus-title">CONFIGURE MEMBERS</DialogTitle>
  <DialogContent id="default-focus-content" style="width: 60vmin; height: 40vmin;">

	{#if configure_members}
	    <div class="members-list">
    	    <pre style="display: inline-block;">MEMBERS:</pre>
        	<Set style="display: inline-block;" bind:chips={members_list} let:chip>
            	<Chip {chip}>
                	<Text tabindex={0}>{chip.name_last + ', ' + chip.name_first}</Text>
	                <TrailingAction icon$class="material-icons">cancel</TrailingAction>
    	        </Chip>
        	</Set>
	    </div>
        <Autocomplete
            options={members}
            bind:value={member_value}
            label="SELECT MEMBER"
            getOptionLabel={(option) => ( option ? `${option.name_first} ${option.name_last}` : '' )}
            style="width: 100%;"
            textfield$style="width: 100%;"
			on:SMUIAutocomplete:selected={handle_member_selection}
        />
	{/if}

  </DialogContent>
  <DialogActions>
    <Button
      on:click={ async () => {
		configure_members = false;
	  }}
    >
      <ButtonLabel>CANCEL</ButtonLabel>
    </Button>
    <Button
      defaultAction
      use={[DialogInitialFocus]}
      on:click={ async () => {
		cblocks[ configure_block_idx ].member_ids = members_list.map( m => m.id ).join(',');
		configure_members = false;
		member_value = '';
	  }}
    >
      <ButtonLabel>APPLY CONFIG</ButtonLabel>
    </Button>
  </DialogActions>
</Dialog>

<Dialog
  open={configure_group}
  aria-labelledby="default-focus-title"
  aria-describedby="default-focus-content"
  scrimClickAction=""
  escapeKeyAction=""
>
  <DialogTitle id="default-focus-title">CONFIGURE GROUP</DialogTitle>
  <DialogContent id="default-focus-content" style="width: 60vmin; height: 40vmin;">
	{#if configure_group}
        <Autocomplete
            options={groups}
            bind:value={group_value}
            label="SELECT GROUP"
            getOptionLabel={(option) => ( option ? `${option.name}` : '' )}
            style="width: 100%;"
            textfield$style="width: 100%;"
			on:SMUIAutocomplete:selected={handle_group_selection}
        />
	{/if}
  </DialogContent>
  <DialogActions>
    <Button
      on:click={ async () => { configure_group = false; }}
    >
      <ButtonLabel>CANCEL</ButtonLabel>
    </Button>
    <Button
      defaultAction
      use={[DialogInitialFocus]}
      on:click={ async () => {
		cblocks[ configure_block_idx ].group_id = group_value.id;
		group_value = '';
		configure_group = false;
	  }}
    >
      <ButtonLabel>APPLY CONFIG</ButtonLabel>
    </Button>
  </DialogActions>
</Dialog>

<Dialog
  open={configure_group_and_roles}
  aria-labelledby="default-focus-title"
  aria-describedby="default-focus-content"
  scrimClickAction=""
  escapeKeyAction=""
>
  <DialogTitle id="default-focus-title">CONFIGURE GROUP AND ROLES</DialogTitle>
  <DialogContent id="default-focus-content" style="width: 60vmin; height: 40vmin;">

	{#if configure_group_and_roles}
        <Autocomplete
            options={groups}
            bind:value={group_value}
            label="SELECT GROUP"
            getOptionLabel={(option) => ( option ? `${option.name}` : '' )}
            style="width: 100%;"
            textfield$style="width: 100%;"
			on:SMUIAutocomplete:selected={handle_group_selection}
        />

		{#if group_role_options}
		  {#each group_role_options as option}
	    	<FormField>
	    	  <Checkbox
    	    	bind:group={group_roles_value}
	    	    value={option.id}
    	    	disabled={option.disabled}
		      />
			  <span slot="label">{option.role}</span>
    		</FormField>
			<br/>
		  {/each}
		{/if}

	{/if}

  </DialogContent>
  <DialogActions>
    <Button
      on:click={ async () => { configure_group_and_roles = false; }}
    >
      <ButtonLabel>CANCEL</ButtonLabel>
    </Button>
    <Button
      defaultAction
      use={[DialogInitialFocus]}
      on:click={ async () => {

		cblocks[ configure_block_idx ].group_id = group_value.id;
		group_value = '';

		console.log('group_roles_value', group_roles_value);
		cblocks[ configure_block_idx ].group_role_ids = group_roles_value.join(',');

		group_roles_value = [];

		configure_group_and_roles = false;
	  }}
    >
      <ButtonLabel>APPLY CONFIG</ButtonLabel>
    </Button>
  </DialogActions>
</Dialog>

{#if $auth['grants']['workflows-edit']}
	{#if pleaseWait}
		<PleaseWait text="{pleaseWait}" />
	{:else}

	{#await fetchWorkflow( $workflow_id )}

	<LinearProgress indeterminate />

	{:then data}

	<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
	<div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>

	<Paper>

	<table style="width: 100%;">
		<tr>
			<td style="width: 48%;">
				<div id="available-blocks">
					{#each Object.values(blocks) as item}

					    <Card style="margin-bottom: 1vmin;">
					      	<Content>
								{item.name} <br /> {item.description}
							</Content>
					      <Actions fullBleed>
        					<Button on:click={() => { console.log('here: item.name'); cblocks = [ ...cblocks, JSON.parse(JSON.stringify( item )) ]; }}>
					          <ButtonLabel>ADD TO WORKFLOW</ButtonLabel>
					          <i class="material-icons" aria-hidden="true">arrow_forward</i>
					        </Button>
					      </Actions>
					    </Card>

					{/each}
				</div>
			</td>
			<td style="width: 4%;">&nbsp;</td>
			<td style="width: 48%;">
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
							          <ButtonLabel> {group_roles_lookup[grid] ? group_roles_lookup[grid].role : 'No Role'}</ButtonLabel>
				    			    </Button>
									{/each}
								{/if}
							</Content>
					      <Actions>
					        <IconButton
					          class="material-icons"
					          on:click={() => { swap(cblocks, idx, idx - 1); cblocks = [ ...cblocks ]; }}
					          title="Move Up">arrow_upward</IconButton
					        >
					        <IconButton
					          class="material-icons"
					          on:click={() => { swap(cblocks, idx, idx + 1); cblocks = [ ...cblocks ]; }}
					          title="Move Down">arrow_downward</IconButton
					        >
							{#if item.configurable && item.configurable !== 'not-configurable'}
								{#if item.configurable == 'members'}
							        <IconButton
							          class="material-icons"
							          on:click={() => {
										members_list = item.member_ids ? item.member_ids.split(',').map( m => members_lookup[m] ) : [];
										configure_block_idx = idx; configure_members = true;
									  }}
					    		      title="Configure">settings</IconButton
							        >
								{:else if item.configurable == 'group'}
							        <IconButton
							          class="material-icons"
							          on:click={ async () => {
										group_value = groups_lookup[ item.group_id ];
										configure_block_idx = idx; configure_group = true;
									  }}
					    		      title="Configure">settings</IconButton
							        >
								{:else if item.configurable == 'group-and-roles'}
							        <IconButton
							          class="material-icons"
							          on:click={() => {
										group_value = groups_lookup[ item.group_id ];
										configure_block_idx = idx; configure_group_and_roles = true;
									  }}
					    		      title="Configure">settings</IconButton
							        >
								{/if}
							{/if}
					        <IconButton
					          class="material-icons" style="margin-left: 5vmin;"
					          on:click={() => { cblocks.splice(idx, 1); cblocks = [ ...cblocks ]; }}
					          title="Delete">delete</IconButton
					        >
					      </Actions>
					    </Card>
					{/each}
				</div>
			</td>
		</tr>
	</table>

	<div class="save-button">
    	<Fab color="primary" on:click={() => { saveWorkflowConfig(); }} extended>
	      <FabIcon class="material-icons">save</FabIcon>
	      <FabLabel>SAVE CHANGES</FabLabel>
    	</Fab>
	</div>

	</Paper>

	{/await}

	{/if}
{:else}
	<AccessDenied />
{/if}

<style>

#available-blocks {
	padding: 1vmin;
	box-sizing: border-box;
	height: 70vmin;
	overflow-y: auto;
	background-color: #CCC;
	border-radius: 1vmin;
}

#configured-blocks {
	border: 1px solid #000;
	padding: 1vmin;
	box-sizing: border-box;
	height: 70vmin;
	overflow-y: auto;
	background-color: #CCC;
	border-radius: 1vmin;
}

.save-button {
	position: absolute;
	bottom: 2vmin;
	left: 50%;
	transform: translate(-50%,0);
}

</style>
