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

import { getTask, getTasks, taskAssigned, taskAssign, taskUnassign } from '../utils/pnb-api.js';
import { getGroup } from '../utils/pnb-api.js';
import { convertTasks } from '../utils/pnb-convert.js';
import { downloadInstitution } from '../utils/pnb-download.js';
import { getGroups, getMembers, getMemberFields } from '../utils/pnb-api.js';
import { convertMembers, addInstitutionsToConvertedMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';

import FileSaver from '../vendor/FileSaver.js';
import { s2ab } from '../utils/s2ab.js';
import * as XLSX from 'xlsx';

import { institution_id, auth, screen } from '../store.js';
import { sleep } from '../utils/sleep.js';

let subtitle = '';
let data = {};

let sort = 'member';
let sortDirection = 'ascending';

let tasks = false;
let ttasks = [];
let groups = false;
let tgroups = false;
let members = false;
let total_fte = 0;
let current_year = String(new Date().getFullYear());
let fte_by_year = { [current_year]: 0 };
let years = [];

let member_cache = {};
let task_cache = {};
let group_cache = {};
let new_group = 0;

let new_member_value = false;
let new_group_value = false;
let new_task_value = false;
let new_fte_value = 0;
let new_begin_time = (new Date().getFullYear()) + '-01-01',
	 new_end_time  = (new Date().getFullYear()) + '-12-31';

let pleaseWait = false;

const assignTask = async () => {

	if ( !new_task_value || !new_member_value ) { return; }

    pleaseWait = 'ASSIGNING TASK, PLEASE WAIT';

    let rc = await taskAssign({ task_id: new_task_value.id, member_id: new_member_value.id, group_id: new_group_value.id,
		fte: new_fte_value, begin_time: new_begin_time, end_time: new_end_time });
    await sleep(1000);

	new_member_value = false;
	new_group_value = false;
	new_task_value = false;
	new_fte_value = 0;
	new_begin_time = (new Date().getFullYear()) + '-01-01';
    new_end_time  = (new Date().getFullYear()) + '-12-31';
	total_fte = 0;

    $screen = 'group';
    if ( rc ) {
        router.goto('/institution/' + $institution_id + '/tasks' );
    }

    pleaseWait = false;
}

const unassignTask = async ( id ) => {
    pleaseWait = 'UNASSIGNING TASK, PLEASE WAIT';

    let rc = await taskUnassign( id );
    await sleep(1000);
    $screen = 'group';
    if ( rc ) {
        router.goto('/institution/' + $institution_id + '/tasks' );
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

const findMember = ( id ) => {
	if ( member_cache[id] ) { return ( member_cache[id].name_last + ', ' + member_cache[id].name_first ); }
	for ( const member of members ) {
		if ( member.id == id ) {
			member_cache[id] = member;
			return ( member.name_last + ', ' + member.name_first );
		}
	}
	return 'UNKNOWN/DEACTIVATED';
}

const findTTask = ( id ) => {
	for ( const task of ttasks ) {
		if ( task.task_id == id ) {
			return task;
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

const fetchData = async () => {

	const inst = await downloadInstitution( $institution_id );
	subtitle = inst.cinstitution.name_full;

    groups = await getGroups();
    data.groups = groups;

	tasks = await getTasks();
	tasks = await convertTasks(tasks);
	data.tasks = tasks;

    let mem = await getMembers();
    members = await convertMembers( mem );
    sortConvertedMembers( members );
	await addInstitutionsToConvertedMembers( members );

	members = members.filter( m => m.institution__id == $institution_id );
	data.members = members;

	let member_ids = members.map( m => m.id );

	let atasks = await taskAssigned();
	atasks = atasks.filter( t => member_ids.includes( parseInt(t.member_id) ) );

	ttasks = [];
	total_fte = 0;
	fte_by_year  = { [current_year]: 0 };
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
			member: findMember( task.member_id ),
			group: ( findGroup( task.group_id ).name || '' ),
			fte: ( task.fte || 0 ),
			year_begin: year_begin,
			year_end  : year_end
		});

		total_fte += Number(task.fte || 0);
	});

	years = Object.keys( fte_by_year ).sort();

	return ttasks;
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
	console.log('begin_time: ' + v.begin_time + ', end_time: ' + v.end_time );
	return 'ERROR';
}

const exportToExcel = ( data ) => {
    data = [ [ 'DATE RANGE', 'TASK', 'MEMBER', 'INSTITUTION', 'GROUP', 'FTE' ], ...data ];
    var ws = XLSX.utils.aoa_to_sheet( data ),
        ws_name = window.pnb.xlsx['institution-tasks-export'];
    var wb = XLSX.utils.book_new();
    wb.SheetNames.push(ws_name);
    wb.Sheets[ws_name] = ws;
    var wbout = XLSX.write(wb, {bookType:'xlsx', bookSST:true, type: 'binary'});
    saveAs( new Blob([s2ab(wbout)],{type:"application/octet-stream"}), ws_name + '-' + ( Date.now() / 1000 | 0 )+".xlsx" );
}

const prepareForExcel = () => {
    let data = [];
    for( const v of ttasks ) {
        data.push([
            v.dates,
            v.task,
            v.member,
            v.inst,
            v.group,
            v.fte
        ]);
    }
    return exportToExcel( data );
}

</script>

    {#if pleaseWait}

        <PleaseWait text="{pleaseWait}" />

    {:else}

{#await fetchData()}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">INSTITUTION TASKS</div>
<div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>

<Paper>

{#if $auth['grants']['institution-tasks-create']}

<div style="width: 100%;">

    <Autocomplete
        options={members}
        bind:value={new_member_value}
        getOptionLabel={(option) =>
            option ? `${option.name_last}, ${option.name_first}` : ''}
        label="SELECT MEMBER"
        class="inst-autocomplete"
    />


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

<!-- LIST OF TASKS //-->

{#if !$auth['grants']['institution-tasks-view']}
    <AccessDenied />
{:else}

{#if ttasks && ttasks.length}
<div style="text-align: center;" class="mdc-typography--headline4">
	TASKS BY YEAR 
    <Select variant="outlined" bind:value={current_year} noLabel>
    	{#each years as year}
        	<Option value={year}>{year}</Option>
    	{/each}
    </Select>
</div>

{#key current_year}

<div style="text-align: center;" class="mdc-typography--headline6">FTE sum for {current_year} = { Number.parseFloat(fte_by_year[current_year]).toFixed(2) }</div>

<DataTable table$aria-label="Group Data" style="width: 100%;"
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
            <Cell columnId="member" style="text-align: center;">
                <Label>MEMBER</Label>
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
	{#each ttasks as task ( task.id ) }
	  {#if ( ( task.year_begin >= current_year && ( !task.year_end || task.year_end <= current_year ) )
			|| ( task.year_end >= current_year && ( !task.year_begin || task.year_begin <= current_year ) ) ) }
      <Row data-entry-id="group">
   	    <Cell style="text-align: center;">{ task.dates }</Cell>
   	    <Cell style="text-align: center;">{ task.member }</Cell>
   	    <Cell style="text-align: center;">{ task.task }</Cell>
   	    <Cell style="text-align: center;">{ task.group }</Cell>
   	    <Cell style="text-align: center;">{ task.fte }</Cell>
       	<Cell style="text-align: center;">
{#if $auth['grants']['institution-tasks-edit']}
        <Button on:click={() => { router.goto('/assigned-task-edit/' + task.id); }} variant="raised">
            <ButtonIcon class="material-icons">edit</ButtonIcon>
            <ButtonLabel>EDIT</ButtonLabel>
        </Button>
        <Button on:click={() => { unassignTask( task.id ); }} variant="raised">
            <ButtonIcon class="material-icons">table</ButtonIcon>
            <ButtonLabel>DELETE</ButtonLabel>
        </Button>
{/if}
		</Cell>
   	  </Row>
	  {/if}
	{/each}
    </Body>
</DataTable>

{/key}

<div class="save-button">
    <Fab color="primary" on:click={() => { prepareForExcel(); }} extended>
        <FabIcon class="material-icons">save</FabIcon>
        <FabLabel>EXPORT TO EXCEL</FabLabel>
    </Fab>
</div>

{/if}

{/if}

</Paper>

{/await}

{/if}

<style>

.save-button {
    position: absolute;
    bottom: 2vmin;
    left: 2vmin;
}

</style>