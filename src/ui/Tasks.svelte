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

import AccessDenied from './AccessDenied.svelte';

import { getTasks, getTaskFields } from '../utils/pnb-api.js';
import { convertTasks } from '../utils/pnb-convert.js';
import { screen, task_id, task_mode } from '../store.js';
import { auth } from '../store.js';

import FileSaver from '../vendor/FileSaver.js';
import { s2ab } from '../utils/s2ab.js';
import * as XLSX from 'xlsx';

import Textfield from '@smui/textfield';
import Icon from '@smui/textfield/icon';
import HelperText from '@smui/textfield/helper-text';

let items = [];
let filteredItems = [];
let quickSearch = writable('');

let sort = 'title';
let sortDirection = 'ascending';

let currentPage = 0;
let rowsPerPage = 25;

$: start = currentPage * rowsPerPage;
$: end   = Math.min(start + rowsPerPage, filteredItems.length);
$: slice = filteredItems.slice( start, end );
$: lastPage = Math.max(Math.ceil( filteredItems.length / rowsPerPage) - 1, 0);
$: if (currentPage > lastPage) {
    currentPage = lastPage;
}

const filterItemsQuick = () => {
    if ( $quickSearch.length ) {
        filteredItems = items.filter( item => {
            if ( String(item.title).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            if ( String(item.desc).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            return false;
        });
    } else {
        filteredItems = items.slice();
    }
    return filteredItems;
}

const downloadTasks = async () => {
    let tasks = await getTasks();
    items = await convertTasks( tasks );
	filteredItems = items;
	slice = filteredItems.slice(currentPage,rowsPerPage);
    return slice;
}

const handleRowClick = ( e ) => {
	$task_mode = 'view';
	$task_id = e.target.dataset.entryId;
	$screen = 'task';
	router.goto('/task/' + $task_id + '/view');
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

const exportToExcel = ( data ) => {
    var ws = XLSX.utils.aoa_to_sheet( data ),
        ws_name = window.pnb.xlsx['tasks-export'];
    var wb = XLSX.utils.book_new();
    wb.SheetNames.push(ws_name);
    wb.Sheets[ws_name] = ws;
    var wbout = XLSX.write(wb, {bookType:'xlsx', bookSST:true, type: 'binary'});
    saveAs( new Blob([s2ab(wbout)],{type:"application/octet-stream"}), ws_name + '-' + ( Date.now() / 1000 | 0 )+".xlsx" );
}

const prepareForExcel = () => {
    let data = [];
    for( const v of filteredItems ) {
        let row = [];
        for ( const f of window.pnb.tasks ) {
            row.push( v[ f.field ] || '' );
        }
        data.push( row );
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

{#if $auth['grants']['tasks-view']}

<div style="text-align: center;" class="mdc-typography--headline4">TASKS</div>

{#await downloadTasks()}

<LinearProgress indeterminate />

{:then}

<div class="columns">
    <div>
		<Textfield variant="outlined" bind:value={$quickSearch} label="Quick Search">
	    	<Icon class="material-icons" slot="trailingIcon">search</Icon>
	    	<HelperText slot="helper">search by matching substring</HelperText>
		</Textfield>
    </div>
</div>

<Paper>
<DataTable table$aria-label="Institution List" style="width: 100%;"
  on:SMUIDataTableRow:click={handleRowClick}
  sortable
  bind:sort
  bind:sortDirection
  on:SMUIDataTable:sorted={handleSort}
>
	<Head>
		<Row>
			{#each window.pnb.tasks as task}
			<Cell columnId="{task.field}" style="text-align: {task.align}; width: {task.width};">
				<Label>{task.title}</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
			</Cell>
			{/each}
		</Row>
	</Head>
	<Body>
    {#each slice as item (item.id)}
      <Row data-entry-id="{item.id}">
		{#each window.pnb.tasks as task}
        <Cell style="text-align: {task.align}; width: {task.width}; white-space: normal;">
			{@html item[ task.field ] || '&nbsp;'}
		</Cell>
		{/each}
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
.columns {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
}

.columns > * {
    margin-right: 2vmin;
}
.save-button {
    position: absolute;
    bottom: 2vmin;
    left: 2vmin;
}
</style>