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

import { getTask, getTasks } from '../utils/pnb-api.js';

import { getMembers, getTaskMembers, taskAddMember, taskRemoveMember } from '../utils/pnb-api.js';
import { convertMembers, addInstitutionsToConvertedMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';
import { downloadTask } from '../utils/pnb-download.js';

import { task_id, auth, screen } from '../store.js';
import { sleep } from '../utils/sleep.js';

let title = '';
let data = {};
let tasks = false;
let members = false;
let tmembers = false;

let task_cache = {};
let member_cache = {};
let new_member = 0;

let new_member_value = false;
let new_fte_value = 0;
let new_begin_time = null, new_end_time = null;

let pleaseWait = false;

const addNewMember = async () => {

	if ( !new_member_value ) { return; }

	for( const member of data.members ) {
		if ( member.member_id == new_member_value.id ) {
			alert('ERROR: MEMBER ALREADY ADDED');
			return;
		}
	}

    pleaseWait = 'ADDING A MEMBER TO THE TASK, PLEASE WAIT';

    let rc = await taskAddMember({ task_id: $task_id, member_id: new_member_value.id, fte: new_fte_value, begin_time: new_begin_time, end_time: new_end_time });
    await sleep(1000);
    $screen = 'tasks';
    if ( rc ) {
        router.goto('/task/' + $task_id + '/view' );
    } else {
        console.log('ERROR: task has not been updated');
    }

    pleaseWait = false;
}

const removeMemberFromTask = async ( member_id ) => {
    pleaseWait = 'REMOVING A MEMBER FROM THE TASK, PLEASE WAIT';

    let rc = await taskRemoveMember({ task_id: $task_id, member_id });
    await sleep(1000);
    $screen = 'tasks';
    if ( rc ) {
        router.goto('/task/' + $task_id + '/view' );
    } else {
        console.log('ERROR: task has not been updated');
    }

    pleaseWait = false;
}

const findTask = ( id ) => {
	if ( task_cache[id] ) { return task_cache[id]; }
	for ( const task of tasks ) {
		if ( task.id == id ) {
			task_cache[id] = task;
			return task;
		}
	}
	return false;
}

const findTMember = ( id ) => {
	for ( const member of tmembers ) {
		if ( member.id == id ) {
			return member.member;
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

const fetchTask = async () => {
	const dtask = await downloadTask( $task_id );
	data = dtask.task;
	title = dtask.ctask.title;

	tasks = await getTasks();
	let mem = await getMembers();
   	let items = await convertMembers( mem );
   	members = await addInstitutionsToConvertedMembers( items );
    sortConvertedMembers( members );
	tmembers = await getTaskMembers( $task_id );
	data.members = tmembers;
	return data;
}

const getMemberDates = ( member ) => {
	if ( !member.begin_time && !member.end_time ) {
		return ''; // not set
	} else if ( member.begin_time && member.end_time ) {
		return ( member.begin_time + ' - ' + member.end_time );
	} else if ( member.begin_time && !member.end_time ) {
		return ( member.begin_time + ' AND BEYOND' );
	} else if ( !member.begin_time && member.end_time ) {
		return ( 'UP TO ' + member.end_time);
	}
	return 'ERROR';
}

</script>

    {#if pleaseWait}

        <PleaseWait text="{pleaseWait}" />

    {:else}

{#await fetchTask()}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">TASK: {title}</div>
<Paper>

<!-- ADD NEW MEMBER //-->

<div style="width: 100%;">

	<Autocomplete
    	options={members}
    	bind:value={new_member_value}
		getOptionLabel={(option) =>
			option ? `${option.name_last}, ${option.name_first}` : ''}
    	label="SELECT NEW MEMBER"
		class="inst-autocomplete"
	/>

        <Textfield
			bind:value={ new_fte_value }
            label="FTE"
            type="number"
        >
          <svelte:fragment slot="helper">
            <HelperText>Amount of FTE (fractional)</HelperText>
          </svelte:fragment>
        </Textfield>

        <Textfield bind:value={ new_begin_time }
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="BEGIN TIME"
            type="date"
        >
          <svelte:fragment slot="helper">
            <HelperText>OPTIONAL: BEGIN TIME OF THE APPOINTMENT</HelperText>
          </svelte:fragment>
        </Textfield>

        <Textfield bind:value={ new_end_time }
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="END TIME"
            type="date"
        >
          <svelte:fragment slot="helper">
            <HelperText>OPTIONAL: END TIME OF THE APPOINTMENT</HelperText>
          </svelte:fragment>
        </Textfield>

	<p>
        <Button on:click={() => { addNewMember(); }} variant="raised">
            <ButtonIcon class="material-icons">save</ButtonIcon>
            <ButtonLabel>ADD NEW MEMBER</ButtonLabel>
        </Button>
	</p>
</div>

<!-- LIST OF TASK MEMBERS //-->

{#if data.members.length}
<div style="text-align: center;" class="mdc-typography--headline4">MEMBERS</div>
<DataTable table$aria-label="Member Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="value" style="text-align: center;">
                <Label>DATES</Label>
            </Cell>
            <Cell columnId="value" style="text-align: center;">
                <Label>FTE</Label>
            </Cell>
            <Cell columnId="value" style="text-align: center;">
                <Label>NAME</Label>
            </Cell>
            <Cell columnId="email" style="text-align: center;">
                <Label>EMAIL</Label>
            </Cell>
            <Cell columnId="institution" style="text-align: center;">
                <Label>INSTITUTION</Label>
            </Cell>
            <Cell columnId="operation" style="text-align: center;">
                <Label>OPERATION</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
	{#each data.members as member ( member.member_id )}
      <Row data-entry-id="member">
   	    <Cell style="text-align: center;">{ getMemberDates( member ) }</Cell>
   	    <Cell style="text-align: center;">{member.fte || 0}</Cell>
   	    <Cell style="text-align: center;">{findMember( member.member_id ).name_first || ''} {findMember( member.member_id ).name_last || ''}</Cell>
       	<Cell style="text-align: center;">{findMember( member.member_id ).email || ''}</Cell>
       	<Cell style="text-align: center;">{findMember( member.member_id ).institution__name_full}</Cell>
       	<Cell style="text-align: center;">
        <Button on:click={() => { removeMemberFromTask( member.member_id ); }} variant="raised">
            <ButtonIcon class="material-icons">table</ButtonIcon>
            <ButtonLabel>REMOVE</ButtonLabel>
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