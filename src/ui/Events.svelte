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

import AccessDenied from './AccessDenied.svelte';

import { getEvents, getEventFields } from '../utils/pnb-api.js';
import { convertEvents } from '../utils/pnb-convert.js';
import { screen, event_id, event_mode } from '../store.js';
import { auth } from '../store.js';

import Textfield from '@smui/textfield';
import Icon from '@smui/textfield/icon';
import HelperText from '@smui/textfield/helper-text';

let items = [];
let filteredItems = [];

let sort = 'name';
let sortDirection = 'ascending';

let currentPage = 0;
let rowsPerPage = 25;

let quickSearch = writable('');

$: start = currentPage * rowsPerPage;
$: end   = start + rowsPerPage;
$: slice = filteredItems.slice( start, end );
$: lastPage = Math.max(Math.ceil( filteredItems.length / rowsPerPage) - 1, 0);

$: if (currentPage > lastPage) {
    currentPage = lastPage;
}

const filterItemsQuick = () => {
    if ( $quickSearch.length ) {
        filteredItems = items.filter( item => {
            if ( String(item.name).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            if ( String(item.category).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            if ( String(item.location).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            if ( String(item.start_time).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            if ( String(item.end_time).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
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

const downloadEvents = async () => {
    let evts = await getEvents( 'full', currentPage, rowsPerPage );
    items = await convertEvents( evts );
	slice = items.slice( currentPage, rowsPerPage );
    return items;
}

const handleRowClick = ( e ) => {
	$event_mode = 'view';
	$event_id = e.target.dataset.entryId;
	$screen = 'event';
	router.goto('/event/' + $event_id + '/view');
}

const unsubscribe_quickSearch = quickSearch.subscribe( v => {
    filteredItems = filterItemsQuick();
});

onDestroy(() => {
    unsubscribe_quickSearch();
});

</script>

{#if $auth['grants']['events-view']}

<div style="text-align: center;" class="mdc-typography--headline4">EVENTS</div>

{#await downloadEvents()}

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

<DataTable table$aria-label="Institution List" style="width: 100%;"
  sortable
  bind:sort
  bind:sortDirection
  on:SMUIDataTable:sorted={handleSort}
  on:SMUIDataTableRow:click={handleRowClick}
>
	<Head>
		<Row>
			{#each window.pnb.events as evt}
			<Cell columnId="name_full" style="text-align: {evt.align}; width: {evt.width};">
				<Label>{evt.title}</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
			</Cell>
			{/each}
		</Row>
	</Head>
	<Body>
    {#each slice as item (item.id)}
      <Row data-entry-id="{item.id}">
		{#each window.pnb.events as evt}
        <Cell style="text-align: {evt.align}; width: {evt.width};">
			{item[ evt.field ] || ''}
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
      {start + 1}-{Math.min(end,items.length)} of {items.length}
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
{/await}

{:else}
	<AccessDenied />
{/if}

<style>

</style>