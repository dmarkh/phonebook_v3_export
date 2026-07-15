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

import { taskAssigned, getTask, getGroups, getTaskGroups, taskAddGroup, taskRemoveGroup } from '../utils/pnb-api.js';
import { downloadTask } from '../utils/pnb-download.js';

import { task_id, auth, screen } from '../store.js';
import { sleep } from '../utils/sleep.js';

let title = '';
let data = {};
let groups = false;
let tgroups = false;

let group_cache = {};
let new_group = 0;

let new_group_value = false;
let new_fte_value = 0;
let new_begin_time = null, new_end_time = null;

let pleaseWait = false;

const addNewGroup = async () => {

	if ( !new_group_value ) { return; }

	for( const group of data.groups ) {
		if ( group.group_id == new_group_value.id ) {
			alert('ERROR: GROUP ALREADY ADDED');
			return;
		}
	}

    pleaseWait = 'ADDING A GROUP TO THE TASK, PLEASE WAIT';


    let rc = await taskAddGroup({ task_id: $task_id, group_id: new_group_value.id, fte: new_fte_value, begin_time: new_begin_time, end_time: new_end_time });
    await sleep(1000);
    $screen = 'tasks';
    if ( rc ) {
        router.goto('/task/' + $task_id + '/view' );
    } else {
        console.log('ERROR: task has not been updated');
    }

    pleaseWait = false;
}

const removeGroupFromTask = async ( group_id ) => {
    pleaseWait = 'REMOVING A GROUP FROM THE TASK, PLEASE WAIT';

    let rc = await taskRemoveGroup({ task_id: $task_id, group_id });
    await sleep(1000);
    $screen = 'tasks';
    if ( rc ) {
        router.goto('/task/' + $task_id + '/view' );
    } else {
        console.log('ERROR: task has not been updated');
    }

    pleaseWait = false;
}

const findGroup = ( id ) => {
	for ( const group of groups ) {
		if ( group.id == id ) {
			return group;
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

	groups = await getGroups();
	tgroups = await getTaskGroups( $task_id );
	data.groups = tgroups;
	return data;
}

const getGroupDates = ( group ) => {
    if ( !group.begin_time && !group.end_time ) {
        return ''; // not set
    } else if ( group.begin_time && group.end_time ) {
        return ( group.begin_time + ' - ' + group.end_time );
    } else if ( group.begin_time && !group.end_time ) {
        return ( group.begin_time + ' AND BEYOND' );
    } else if ( !group.begin_time && group.end_time ) {
        return ( 'UP TO ' + group.end_time);
    }
    console.log('begin_time: ' + group.begin_time + ', end_time: ' + group.end_time );
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

<!-- ADD NEW GROUP //-->

<div style="width: 100%;">

	<Autocomplete
    	options={groups}
    	bind:value={new_group_value}
		getOptionLabel={(option) =>
			option ? `${option.name}` : ''}
    	label="SELECT NEW GROUP"
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
        <Button on:click={() => { addNewGroup(); }} variant="raised">
            <ButtonIcon class="material-icons">save</ButtonIcon>
            <ButtonLabel>ADD NEW GROUP</ButtonLabel>
        </Button>
	</p>
</div>

<!-- LIST OF TASK GROUPS //-->

{#if tgroups.length}
<div style="text-align: center;" class="mdc-typography--headline4">GROUPS</div>
<DataTable table$aria-label="Group Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="value" style="text-align: center;">
                <Label>DATES</Label>
            </Cell>
            <Cell columnId="fte" style="text-align: center;">
                <Label>FTE</Label>
            </Cell>
            <Cell columnId="group" style="text-align: center;">
                <Label>GROUP</Label>
            </Cell>
            <Cell columnId="operation" style="text-align: center;">
                <Label>OPERATION</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
	{#each tgroups as group ( group.group_id )}
      <Row data-entry-id="group">
	<Cell style="text-align: center;">{ getGroupDates( group ) }</Cell>
       	<Cell style="text-align: center;">{group.fte}</Cell>
       	<Cell style="text-align: center;">{findGroup( group.group_id ).name}</Cell>
       	<Cell style="text-align: center;">
        <Button on:click={() => { removeGroupFromTask( group.group_id ); }} variant="raised">
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