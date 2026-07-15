<script>

import {meta, router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import AccessDenied from './AccessDenied.svelte';
import PleaseWait from './PleaseWait.svelte';
import LinearProgress from '@smui/linear-progress';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import Paper from '@smui/paper';
import Select, { Option } from '@smui/select';
import IconButton from '@smui/icon-button';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';
import CharacterCounter from '@smui/textfield/character-counter';
import Dialog, { Title as DialogTitle, Content as DialogContent, Actions as DialogActions, InitialFocus as DialogInitialFocus } from '@smui/dialog';

import { getGroup, groupUpdateRole, getGroupRoles, groupAddRole, groupRemoveRole } from '../utils/pnb-api.js';

import { group_id, auth, screen } from '../store.js';
import { sleep } from '../utils/sleep.js';

let title = '';
let data = {};
let roleupdate_open = false;
let roleupdate_name = '';
let roleupdate_weight = 0;

let new_role_name = '';
let group;

let pleaseWait = false;

const updateRole = async () => {
    pleaseWait = 'UPDATING A ROLE, PLEASE WAIT';
    let rc = await groupUpdateRole({ group_id: $group_id, role_id: roleupdate_open, name: roleupdate_name, weight: roleupdate_weight });
    await sleep(1000);
	await fetchRoles();
    pleaseWait = false;
}

const createNewRole = async () => {
    pleaseWait = 'CREATING A ROLE, PLEASE WAIT';
    let rc = await groupAddRole({ group_id: $group_id, name: new_role_name });
    await sleep(1000);
	await fetchRoles();
    pleaseWait = false;
}

const removeRoleFromGroup = async ( role_id ) => {
    pleaseWait = 'DELETING A ROLE, PLEASE WAIT';
    let rc = await groupRemoveRole({ group_id: $group_id, role_id });
    await sleep(1000);
	await fetchRoles();
    pleaseWait = false;
}

const fetchRoles = async () => {
	group = await getGroup( $group_id );
	data = await getGroupRoles( $group_id );
	return data;
}

const findRole = ( id ) => {
    for ( const role of data ) {
        if ( role.id == id ) {
            return role;
        }
    }
    return '';
}

</script>

    {#if pleaseWait}

        <PleaseWait text="{pleaseWait}" />

    {:else}

{#await fetchRoles()}

<LinearProgress indeterminate />

{:then}

<Dialog
  open={roleupdate_open}
  aria-labelledby="default-focus-title"
  aria-describedby="default-focus-content"
  scrimClickAction=""
  escapeKeyAction=""
>
  <DialogTitle id="default-focus-title">EDIT ROLE NAME</DialogTitle>
  <DialogContent id="default-focus-content">
	<div style="width: 50vmin; height: 50vmin;">
        <Textfield bind:value={roleupdate_name}
            style="width: 100%;"
            helperLine$style="width: 80%;"
            label="ROLE NAME"
        >
          <svelte:fragment slot="helper">
            <HelperText>ROLE NAME</HelperText>
          </svelte:fragment>
        </Textfield>

    <Select bind:value={roleupdate_weight}
        style="width: 100%;"
        label="ROLE WEIGHT"
    >
        <Option value="-10">-10</Option>
        <Option value="-9">-9</Option>
        <Option value="-8">-8</Option>
        <Option value="-7">-7</Option>
        <Option value="-6">-6</Option>
        <Option value="-5">-5</Option>
        <Option value="-4">-4</Option>
        <Option value="-3">-3</Option>
        <Option value="-2">-2</Option>
        <Option value="-1">-1</Option>
        <Option value="0">0</Option>
        <Option value="1">1</Option>
        <Option value="2">2</Option>
        <Option value="3">3</Option>
        <Option value="4">4</Option>
        <Option value="5">5</Option>
        <Option value="6">6</Option>
        <Option value="7">7</Option>
        <Option value="8">8</Option>
        <Option value="9">9</Option>
        <Option value="10">10</Option>
    </Select>
	</div>
  </DialogContent>
  <DialogActions>
    <Button on:click={() => { roleupdate_open = false; }}>
      <ButtonLabel>CANCEL</ButtonLabel>
    </Button>
    <Button
      defaultAction
      use={[DialogInitialFocus]}
      on:click={ async () => { await updateRole(); roleupdate_open = false; }}
    >
      <ButtonLabel>APPLY CHANGES</ButtonLabel>
    </Button>
  </DialogActions>
</Dialog>

<div style="text-align: center;" class="mdc-typography--headline4">Group Roles: {group.name}</div>
<Paper>

<!-- ADD NEW ROLE //-->

<div style="width: 100%;">
        <Textfield bind:value={ new_role_name }
            label="NEW ROLE NAME"
            input$maxlength=250
            required=true
			style="width: 50%;"
        >
          <svelte:fragment slot="helper">
            <HelperText>NEW ROLE NAME</HelperText>
          </svelte:fragment>
        </Textfield>

        <Button on:click={() => { createNewRole(); }} variant="raised">
            <ButtonIcon class="material-icons">save</ButtonIcon>
            <ButtonLabel>ADD NEW ROLE</ButtonLabel>
        </Button>

</div>

<!-- LIST OF GROUP ROLES //-->

{#if data.length}
<div style="text-align: center;" class="mdc-typography--headline4">Roles</div>
<DataTable table$aria-label="Role Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="value" style="text-align: center;">
                <Label>NAME</Label>
            </Cell>
            <Cell columnId="value" style="text-align: center;">
                <Label>OPERATION</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
	{#each data as role ( role.id )}
      <Row data-entry-id="role">
   	    <Cell style="text-align: center;">
			{role.role}
		</Cell>
       	<Cell style="text-align: center;">
        <Button on:click={() => { removeRoleFromGroup( role.id ); }} variant="raised">
            <ButtonIcon class="material-icons">save</ButtonIcon>
            <ButtonLabel>REMOVE</ButtonLabel>
        </Button>
        <Button on:click={() => { roleupdate_name = findRole( role.id ).role; roleupdate_weight = findRole( role.id ).weight; roleupdate_open = role.id; }} variant="raised">
            <ButtonIcon class="material-icons">table</ButtonIcon>
            <ButtonLabel>EDIT</ButtonLabel>
        </Button>
		</Cell>
   	  </Row>
	{/each}
    </Body>
</DataTable>
{/if}

</Paper>

{/await}

{/if}

<style>

</style>