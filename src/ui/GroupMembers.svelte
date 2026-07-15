<script>

import {meta, router, Route} from 'tinro';

import Autocomplete from '@smui-extra/autocomplete';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import AccessDenied from './AccessDenied.svelte';
import PleaseWait from './PleaseWait.svelte';
import LinearProgress from '@smui/linear-progress';

import Paper from '@smui/paper';

import Select, { Option } from '@smui/select';
import IconButton from '@smui/icon-button';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';
import CharacterCounter from '@smui/textfield/character-counter';
import Dialog, { Title as DialogTitle, Content as DialogContent, Actions as DialogActions, InitialFocus as DialogInitialFocus } from '@smui/dialog';

import { groupUpdateMemberRole, getGroup, getGroups } from '../utils/pnb-api.js';

import { getMembers, getGroupRoles, groupAddMember, groupRemoveMember } from '../utils/pnb-api.js';
import { convertMembers, addInstitutionsToConvertedMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';

import { group_id, auth, screen } from '../store.js';
import { sleep } from '../utils/sleep.js';

let title = '';
let data = {};
let groups = false;
let members = false;
let roles = false;

let roleupdate_open = false;
let roleupdate_role = false;

let group_cache = {};
let member_cache = {};
let new_member = 0;
let new_member_role = 0;

let new_member_value = false;

let pleaseWait = false;

const updateMemberRole = async () => {
    pleaseWait = 'UPDATING MEMBER ROLE, PLEASE WAIT';

    let rc = await groupUpdateMemberRole({ group_id: $group_id, member_id: roleupdate_open, role_id: roleupdate_role });

    await sleep(1000);

	await fetchGroup();

    pleaseWait = false;
}

const addNewMember = async () => {

	if ( !new_member_value ) { return; }

	for( const member of data.members ) {
		if ( member.member_id == new_member_value.id ) {
			alert('ERROR: MEMBER ALREADY ADDED');
			return;
		}
	}

    pleaseWait = 'ADDING A MEMBER TO THE GROUP, PLEASE WAIT';

    let rc = await groupAddMember({ group_id: $group_id, member_id: new_member_value.id, role_id: new_member_role });
    await sleep(1000);

	await fetchGroup();

    pleaseWait = false;
}

const removeMemberFromGroup = async ( member_id ) => {
    pleaseWait = 'REMOVING A MEMBER FROM THE GROUP, PLEASE WAIT';

    let rc = await groupRemoveMember({ group_id: $group_id, member_id });
    await sleep(1000);

	await fetchGroup();

    pleaseWait = false;
}

const findParentGroup = ( id ) => {
	if ( group_cache[id] ) { return group_cache[id].name; }
	for ( const group of groups ) {
		if ( group.id == id ) {
			group_cache[id] = group;
			return group.name;
		}
	}
	return '';
}

const findGroup = ( id ) => {
	if ( group_cache[id] ) { return group_cache[id]; }
	for ( const group of groups ) {
		if ( group.id == id ) {
			group_cache[id] = group;
			return group;
		}
	}
	return false;
}

const findRole = ( id ) => {
	for ( const role of roles ) {
		if ( role.id == id ) {
			return role.role;
		}
	}
	return '';
}

const findMember = ( id ) => {
	if ( member_cache[id] ) { return member_cache[id]; }
	for ( const member of members ) {
		if ( member.id == id ) {
			member_cache[id] = member;
			return member;
		}
	}
	return false;
}

const fetchGroup = async () => {
	data = await getGroup( $group_id );
	groups = await getGroups();


	let mem = await getMembers();
   	let items = await convertMembers( mem );
   	members = await addInstitutionsToConvertedMembers( items );
    sortConvertedMembers( members );

	if ( data.members && data.members.length ) {
		data.members = data.members.filter( m => findMember( m.member_id ) );
	}

	roles = await getGroupRoles( $group_id );
	return data;
}

</script>

    {#if pleaseWait}

        <PleaseWait text="{pleaseWait}" />

    {:else}

{#await fetchGroup()}

<LinearProgress indeterminate />

{:then data}

<Dialog
  open={roleupdate_open}
  aria-labelledby="default-focus-title"
  aria-describedby="default-focus-content"
  scrimClickAction=""
  escapeKeyAction=""
>
  <DialogTitle id="default-focus-title">EDIT MEMBER ROLE</DialogTitle>
  <DialogContent id="default-focus-content">
	<div style="width: 50vmin; height: 50vmin;">
	<p> {findMember(roleupdate_open).name_first} {findMember(roleupdate_open).name_last} </p>
	<p>
    <Select bind:value={roleupdate_role}
        label="GROUP ROLE"
        style="width: 100%;"
    >
      <Option value=0>--- NO ROLE ---</Option>
      {#each roles as role ( role.id ) }
        <Option value={role.id}>{role.role}</Option>
      {/each}
    </Select>
	</p>
	</div>
  </DialogContent>
  <DialogActions>
    <Button on:click={() => { roleupdate_open = false; }}>
      <ButtonLabel>CANCEL</ButtonLabel>
    </Button>
    <Button
      defaultAction
      use={[DialogInitialFocus]}
      on:click={ async () => { await updateMemberRole(); roleupdate_open = false; }}
    >
      <ButtonLabel>APPLY CHANGES</ButtonLabel>
    </Button>
  </DialogActions>
</Dialog>

<div style="text-align: center;" class="mdc-typography--headline4">GROUP MEMBERS: {title}</div>
<Paper>

<!-- ADD NEW MEMBER //-->

<div style="width: 100%;">

	<Autocomplete
    	options={members}
    	bind:value={new_member_value}
		getOptionLabel={(option) =>
			option ? `${option.name_last}, ${option.name_first}` : ''}
    	label="SELECT NEW MEMBER"
	/>

    <Select bind:value={new_member_role}
        label="MEMBER ROLE"
		style="width: 30%;"
    >
      <Option value=0>--- NO ROLE ---</Option>
      {#each roles as role ( role.id ) }
        <Option value={role.id}>{role.role}</Option>
      {/each}
    </Select>

	<p>
        <Button on:click={() => { addNewMember(); }} variant="raised">
            <ButtonIcon class="material-icons">save</ButtonIcon>
            <ButtonLabel>ADD NEW MEMBER</ButtonLabel>
        </Button>
	</p>
</div>

<!-- LIST OF GROUP MEMBERS //-->

{#if data.members.length}
<div style="text-align: center;" class="mdc-typography--headline4">MEMBERS</div>
<DataTable table$aria-label="Member Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="value" style="text-align: center;">
                <Label>ROLE</Label>
            </Cell>
            <Cell columnId="value" style="text-align: center;">
                <Label>NAME</Label>
            </Cell>
            <Cell columnId="group" style="text-align: center;">
                <Label>EMAIL</Label>
            </Cell>
            <Cell columnId="group" style="text-align: center;">
                <Label>INSTITUTION</Label>
            </Cell>
            <Cell columnId="group" style="text-align: center;">
                <Label>OPERATION</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
	{#each data.members as member ( member.member_id )}
      <Row data-entry-id="member">
       	<Cell style="text-align: center;">{findRole( member.role_id )}</Cell>
   	    <Cell style="text-align: center;">{findMember( member.member_id ).name_first || ''} {findMember( member.member_id ).name_last || ''}</Cell>
       	<Cell style="text-align: center;">{findMember( member.member_id ).email || ''}</Cell>
       	<Cell style="text-align: center;">{findMember( member.member_id ).institution__name_full}</Cell>
       	<Cell style="text-align: center;">
        <Button on:click={() => { removeMemberFromGroup( member.member_id ); }} variant="raised">
            <ButtonIcon class="material-icons">table</ButtonIcon>
            <ButtonLabel>REMOVE</ButtonLabel>
        </Button>
        <Button on:click={() => { roleupdate_role = member.role_id; roleupdate_open = member.member_id; }} variant="raised">
            <ButtonIcon class="material-icons">table</ButtonIcon>
            <ButtonLabel>EDIT ROLE</ButtonLabel>
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