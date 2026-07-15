<script>

import { writable } from 'svelte/store';
import { onDestroy } from 'svelte';

import {router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';

import FileSaver from '../vendor/FileSaver.js';
import { s2ab } from '../utils/s2ab.js';
import * as XLSX from 'xlsx';

import AccessDenied from './AccessDenied.svelte';

import { taskAssigned, getTasks, getTaskFields, getGroups } from '../utils/pnb-api.js';
import { convertTasks } from '../utils/pnb-convert.js';

import { getMembers, getMemberFields } from '../utils/pnb-api.js';
import { convertMembers, addInstitutionsToConvertedMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';

import { screen, task_id, task_mode } from '../store.js';
import { auth } from '../store.js';

import Textfield from '@smui/textfield';
import Icon from '@smui/textfield/icon';
import HelperText from '@smui/textfield/helper-text';

let atasks = false, tasks = false;
let task_cache = {};
let items = [];
let filteredItems = [];

let members = false;
let member_cache = {};
let inst_cache = {};
let groups = false;

let sort = 'task';
let sortDirection = 'ascending';

let currentPage = 0;
let rowsPerPage = 25;

let quickSearch = writable('');

$: start = currentPage * rowsPerPage;
$: end   = start + rowsPerPage;
$: slice = filteredItems.slice( start, end );
$: lastPage = Math.max(Math.ceil( items.length / rowsPerPage) - 1, 0);

$: if (currentPage > lastPage) {
	currentPage = lastPage;
}

const findTask = ( id ) => {
    if ( task_cache[id] ) { return task_cache[id].title; }
    for ( const task of tasks ) {
        if ( task.id == id ) {
            task_cache[id] = task;
            return task.title;
        }
    }
    return false;
}

const findGroup = ( id ) => {
    const $grp = groups.find( g => g.id == id );
    return $grp ? $grp.name : false;
}

const findMember = ( id ) => {
    if ( member_cache[id] ) { return ( member_cache[id].name_last + ', ' + member_cache[id].name_first ); }
    for ( const member of members ) {
        if ( member.id == id ) {
            member_cache[id] = member;
            return ( member.name_last + ', ' + member.name_first );
        }
    }
    return false;
}

const findInstitution = ( id ) => {
    if ( inst_cache[id] ) { return inst_cache[id]; }
    for ( const member of members ) {
        if ( member.id == id ) {
            inst_cache[id] = member.institution__name_full;
            return member.institution__name_full;
        }
    }
    return false;
}

const getDates = ( item ) => {
    if ( !item.begin_time && !item.end_time ) {
        return ''; // not set
    } else if ( item.begin_time && item.end_time ) {
        return ( item.begin_time + ' - ' + item.end_time );
    } else if ( item.begin_time && !item.end_time ) {
        return ( item.begin_time + ' AND BEYOND' );
    } else if ( !item.begin_time && item.end_time ) {
        return ( 'UP TO ' + item.end_time);
    }
    console.log('begin_time: ' + item.begin_time + ', end_time: ' + item.end_time );
    return 'ERROR';
}

const downloadTasks = async () => {

	atasks = await taskAssigned();

    tasks = await getTasks();
    tasks = await convertTasks( tasks );

    let mem = await getMembers();
    members = await convertMembers( mem );
    sortConvertedMembers( members );
	await addInstitutionsToConvertedMembers( members );

    groups = await getGroups();

	items = atasks.filter( at => ( findGroup( at.group_id ) && findMember( at.member_id ) && findTask( at.task_id ) && findInstitution( at.member_id ) ) ).map( t => {
		return {
			id    : t.id,
			dates : getDates( t ),
			task  : findTask( t.task_id ),
			group : findGroup( t.group_id ),
			member: findMember( t.member_id ),
			inst  : findInstitution( t.member_id ),
			fte   : ( t.fte || 0 ),
			validated: Number( t.validated )
		};
	});

    filteredItems = items.slice();
    slice = filteredItems.slice(currentPage,rowsPerPage);

    return slice;
}

const filterItemsQuick = () => {
    if ( $quickSearch.length ) {
        filteredItems = items.filter( item => {
            if ( String( item.dates ).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            if ( String( item.task ).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            if ( String( item.member ).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            if ( String( item.inst ).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            if ( String( item.group ).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            return false;
        });
    } else {
        filteredItems = items.slice();
        return filteredItems;
    }
    return filteredItems;
}

const handleSort = () => {
    filteredItems = ( filterItemsQuick() ).sort((a, b) => {
        const [aVal, bVal] = [a[sort], b[sort]][
            sortDirection === 'ascending' ? 'slice' : 'reverse'
        ]();
        if (typeof aVal === 'string' && typeof bVal === 'string') {
            return aVal.localeCompare(bVal);
        }
        return Number(aVal) - Number(bVal);
    });
}

const handleRowClick = ( e ) => {
    $task_id = e.target.dataset.entryId;
    router.goto('/assigned-task-edit/' + $task_id );
}

const exportToExcel = ( data ) => {
	data = [ [ 'DATE RANGE', 'TASK', 'MEMBER', 'INSTITUTION', 'GROUP', 'FTE' ], ...data ];
    var ws = XLSX.utils.aoa_to_sheet( data ),
        ws_name = window.pnb.xlsx['assigned-tasks-export'];
    var wb = XLSX.utils.book_new();
    wb.SheetNames.push(ws_name);
    wb.Sheets[ws_name] = ws;
    var wbout = XLSX.write(wb, {bookType:'xlsx', bookSST:true, type: 'binary'});
    saveAs( new Blob([s2ab(wbout)],{type:"application/octet-stream"}), ws_name + '-' + ( Date.now() / 1000 | 0 )+".xlsx" );
}

const prepareForExcel = () => {
    let data = [];
    for( const v of filteredItems ) {
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

const unsubscribe_quickSearch = quickSearch.subscribe( v => {
    filteredItems = filterItemsQuick();
});

onDestroy(() => {
    unsubscribe_quickSearch();
});

</script>

{#if $auth['grants']['assigned-tasks-view']}

<div style="text-align: center;" class="mdc-typography--headline4">ASSIGNED TASKS</div>

{#await downloadTasks()}

<LinearProgress indeterminate />

{:then}

<Paper>

<div class="columns">
    <div>
    <Textfield variant="outlined" bind:value={$quickSearch} label="Quick Search">
        <Icon class="material-icons" slot="trailingIcon">search</Icon>
        <HelperText slot="helper">search by matching substring</HelperText>
    </Textfield>
    </div>
</div>

<DataTable table$aria-label="Task List" style="width: 100%;"
  sortable
  bind:sort
  bind:sortDirection
  on:SMUIDataTable:sorted={handleSort}
  on:SMUIDataTableRow:click={handleRowClick}
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
			<Cell columnId="member" style="text-align: center;">
				<Label>MEMBER</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
			</Cell>
			<Cell columnId="inst" style="text-align: center;">
				<Label>INSTITUTION</Label>
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
			<Cell columnId="fte" style="text-align: center;">
				<Label>VALIDATED</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
			</Cell>
		</Row>
	</Head>

	<Body>
    {#each slice as item (item.id)}
      <Row data-entry-id="{item.id}">
        <Cell style="text-align: center;">{ item.dates }</Cell>
        <Cell style="text-align: center;">{ item.task }</Cell>
        <Cell style="text-align: center;">{ item.member }</Cell>
        <Cell style="text-align: center; text-wrap: auto;">{ item.inst }</Cell>
        <Cell style="text-align: center;">{ item.group }</Cell>
        <Cell style="text-align: center;">{ item.fte }</Cell>
        <Cell style="text-align: center;">{ item.validated ? 'YES' : 'NO' }</Cell>
      </Row>
    {/each}
	</Body>

  <Pagination slot="paginate">
    <svelte:fragment slot="rowsPerPage">
      <Label>Rows Per Page</Label>
      <Select variant="outlined" bind:value={rowsPerPage} noLabel>
        <Option value={10}>10</Option>
        <Option value={25}>25</Option>
        <Option value={100}>100</Option>
      </Select>
    </svelte:fragment>
    <svelte:fragment slot="total">
      {start + 1}-{Math.min(end,filteredItems.length)} of {filteredItems.length}
    </svelte:fragment>

    <IconButton
      class="material-icons"
      action="first-page"
      title="First page"
      on:click={() => (currentPage = 0)}
      disabled={currentPage === 0}>first_page</IconButton
    >
    <IconButton
      class="material-icons"
      action="prev-page"
      title="Prev page"
      on:click={() => currentPage--}
      disabled={currentPage === 0}>chevron_left</IconButton
    >
    <IconButton
      class="material-icons"
      action="next-page"
      title="Next page"
      on:click={() => currentPage++}
      disabled={currentPage === lastPage}>chevron_right</IconButton
    >
    <IconButton
      class="material-icons"
      action="last-page"
      title="Last page"
      on:click={() => (currentPage = lastPage)}
      disabled={currentPage === lastPage}>last_page</IconButton
    >
  </Pagination>

</DataTable>
</Paper>

<div class="save-button">
    <Fab color="primary" on:click={() => { prepareForExcel(); }} extended>
        <FabIcon class="material-icons">save</FabIcon>
        <FabLabel>EXPORT TO EXCEL</FabLabel>
    </Fab>
</div>

{/await}

{:else}
	<AccessDenied />
{/if}

<style>

.save-button {
    position: absolute;
    bottom: 2vmin;
    left: 2vmin;
}

</style>