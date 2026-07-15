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

import { taskAssign, taskAssignedMember, taskUnassign } from '../utils/pnb-api.js';
import { getTask, getTasks, getMembers, getGroups } from '../utils/pnb-api.js';
import { convertTasks, convertMembers, addInstitutionsToConvertedMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';

import { downloadMember } from '../utils/pnb-download.js';

import { member_id, auth, screen } from '../store.js';
import { sleep } from '../utils/sleep.js';

let title = '';
let subtitle = '';
let data = {};

let current_year = String(new Date().getFullYear());
let fte_by_year = { [current_year]: 0 };
let years = [];

let sort = 'member';
let sortDirection = 'ascending';

let tasks = false;
let ttasks = [];

let members = false;
let tmembers = false;

let groups = false;

let task_cache = {};
let member_cache = {};
let new_member = 0;

let new_task_value = false;
let new_group_value = false;
let new_fte_value = 0;
let new_begin_time = (new Date().getFullYear()) + '-01-01',
	new_end_time = (new Date().getFullYear()) + '-12-31';

let pleaseWait = false;

const assignTask = async () => {

	if ( !$member_id || !new_task_value || !new_group_value ) { return; }

    pleaseWait = 'ASSIGNING A TASK, PLEASE WAIT';

    let rc = await taskAssign({ task_id: new_task_value.id, group_id: new_group_value.id, member_id: $member_id, fte: new_fte_value,
		begin_time: new_begin_time, end_time: new_end_time });
    await sleep(1000);
    $screen = 'member';
    if ( rc ) {
        router.goto('/member/' + $member_id + '/tasks' );
    }

    pleaseWait = false;
}

const unassignTask = async ( id ) => {

    pleaseWait = 'UNASSIGNING THE TASK, PLEASE WAIT';

    let rc = await taskUnassign( id );
    await sleep(1000);
    $screen = 'member';
    if ( rc ) {
        router.goto('/member/' + $member_id + '/tasks' );
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

const findTTask = ( id ) => {
	const task = ttasks.find( t => t.task_id == id );
	return task ? task : '';
}

const findGroup = ( id ) => {
	const $grp = groups.find( g => g.id == id );
	return $grp ? $grp : false;
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

const fetchMember = async () => {

	data.member = await downloadMember( $member_id );

	title = data.member.cmember.name_first + ' ' + data.member.cmember.name_last;
	subtitle = data.member.cinstitution.name_full;

	groups = await getGroups();
	data.groups = groups;

	tasks = await getTasks();
	tasks = await convertTasks(tasks);

	const atasks = await taskAssignedMember( $member_id );

	fte_by_year  = { [current_year]: 0 };
	ttasks = [];
    atasks.forEach( task => {

        const year_begin = task.begin_time ? parseInt(task.begin_time.substr(0, 4)) : null;
        const year_end = task.end_time ? parseInt(task.end_time.substr(0, 4)) : null;

        if ( year_begin && !year_end ) {
            if ( !fte_by_year[year_begin] ) { fte_by_year[year_begin] = 0; }
            fte_by_year[year_begin] += Number(task.fte || 0);
        } else if ( !year_begin && year_end ) {
            if ( !fte_by_year[year_end] ) { fte_by_year[year_end] = 0; }
            fte_by_year[year_end] += Number(task.fte || 0);
        } else if ( year_begin && year_end ) {
            if ( year_begin == year_end ) {
                if ( !fte_by_year[year_begin] ) { fte_by_year[year_begin] = 0; }
                fte_by_year[year_begin] += Number(task.fte || 0);
            } else {
                if ( !fte_by_year[year_begin] ) { fte_by_year[year_begin] = 0; }
                fte_by_year[year_begin] += Number(task.fte || 0);
                if ( !fte_by_year[year_end] ) { fte_by_year[year_end] = 0; }
                fte_by_year[year_end] += Number(task.fte || 0);
            }
        }

        ttasks.push({
            id: task.id,
            dates: getDates( task ),
            task: ( findTask( task.task_id ).title || '' ),
			group: ( findGroup( task.group_id ).name || '' ),
            fte: ( task.fte || 0 ),
            year_begin: year_begin,
            year_end  : year_end
        });
    });

	years = Object.keys( fte_by_year ).sort();

	ttasks = ttasks;

	data.tasks = ttasks;

	return data;
}

const handleSort = () => {
    ttasks.sort((a, b) => {
        const [aVal, bVal] = [a[sort], b[sort]][
            sortDirection === 'ascending' ? 'slice' : 'reverse'
        ]();
        if (typeof aVal === 'string' && typeof bVal === 'string') {
            return aVal.localeCompare(bVal);
        }
        return Number(aVal) - Number(bVal);
    });
    ttasks = ttasks;
}

const getDates = ( v ) => {
	if ( !v.begin_time && !v.end_time ) {
		return ''; // not set
	} else if ( v.begin_time && v.end_time ) {
		return ( v.begin_time + ' - ' + v.end_time );
	} else if ( v.begin_time && !v.end_time ) {
		return ( v.begin_time + ' AND BEYOND' );
	} else if ( !v.begin_time && v.end_time ) {
		return ( 'UP TO ' + v.end_time);
	}
	return 'ERROR';
}

</script>

{#if !$auth['grants']['member-tasks-view']}
    <AccessDenied />
{:else}

    {#if pleaseWait}

        <PleaseWait text="{pleaseWait}" />

    {:else}

{#await fetchMember()}

<LinearProgress indeterminate />

{:then data}

	<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
    {#if subtitle}
        <div style="text-align: center;" class="mdc-typography--subtitle1">{@html subtitle}</div>
    {/if}

<Paper>

{#if $auth['grants']['member-tasks-create']}

<div style="width: 100%;">

	<Autocomplete
    	options={tasks}
    	bind:value={new_task_value}
		getOptionLabel={(option) =>
			option ? `${option.title}` : ''}
    	label="SELECT TASK"
		class="inst-autocomplete"
	/>

	<Autocomplete
    	options={groups}
    	bind:value={new_group_value}
		getOptionLabel={(option) =>
			option ? `${option.name}` : ''}
    	label="SELECT GROUP"
		class="inst-autocomplete"
	/>

        <Textfield
			bind:value={ new_fte_value }
            label="FTE"
            type="number"
			input$step="0.1"
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
        <Button on:click={() => { assignTask(); }} variant="raised">
            <ButtonIcon class="material-icons">save</ButtonIcon>
            <ButtonLabel>ASSIGN TASK</ButtonLabel>
        </Button>
	</p>
</div>

{/if}

<!-- LIST OF MEMBER TASKS //-->

{#if $auth['grants']['member-tasks-view']}

{#if data.tasks.length}

<div style="text-align: center;" class="mdc-typography--headline4">TASKS BY YEAR 
    <Select variant="outlined" bind:value={current_year} noLabel>
        {#each years as year}
            <Option value={year}>{year}</Option>
        {/each}
    </Select>
</div>
{#key current_year}
<div style="text-align: center;" class="mdc-typography--headline6">FTE sum for {current_year} = { Number.parseFloat(fte_by_year[current_year]).toFixed(2) }</div>
<DataTable table$aria-label="Member Data" style="width: 100%;"
  sortable
  bind:sort
  bind:sortDirection
  on:SMUIDataTable:sorted={handleSort}
>
    <Head>
        <Row>
            <Cell columnId="dates" style="text-align: center;">
                <Label>DATES</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="task" style="text-align: center;">
                <Label>TASK</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="group" style="text-align: center;">
                <Label>GROUP</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="fte" style="text-align: center;">
                <Label>FTE</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="operation" style="text-align: center;">
                <Label>OPERATION</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
	{#each ttasks as task ( task.id )}
	{#if ( ( task.year_begin >= current_year && ( !task.year_end || task.year_end <= current_year ) )
            || ( task.year_end >= current_year && ( !task.year_begin || task.year_begin <= current_year ) ) ) }
      <Row data-entry-id="member">
   	    <Cell style="text-align: center;">{ task.dates }</Cell>
   	    <Cell style="text-align: center;">{ task.task }</Cell>
   	    <Cell style="text-align: center;">{ task.group }</Cell>
   	    <Cell style="text-align: center;">{ task.fte }</Cell>
       	<Cell style="text-align: center;">
        <Button on:click={() => { router.goto('/member-task-edit/' + task.id); }} variant="raised">
            <ButtonIcon class="material-icons">edit</ButtonIcon>
            <ButtonLabel>EDIT</ButtonLabel>
        </Button>
        <Button on:click={() => { unassignTask( task.id ); }} variant="raised">
            <ButtonIcon class="material-icons">table</ButtonIcon>
            <ButtonLabel>DELETE</ButtonLabel>
        </Button>
		</Cell>
   	  </Row>
	{/if}
	{/each}
    </Body>
</DataTable>
{/key}

{/if}

{/if}

</Paper>

{/await}

{/if}

{/if}

<style>

</style>