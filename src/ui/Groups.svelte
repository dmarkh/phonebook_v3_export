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

import { getGroups } from '../utils/pnb-api.js';
import { invenio_search_community, invenio_create_community } from '../utils/pnb-graphql.js';

import { screen } from '../store.js';
import { auth, group_id, group_mode } from '../store.js';

import FileSaver from '../vendor/FileSaver.js';
import { s2ab } from '../utils/s2ab.js';
import * as XLSX from 'xlsx';

import Textfield from '@smui/textfield';
import Icon from '@smui/textfield/icon';
import HelperText from '@smui/textfield/helper-text';

let items = [];
let filteredItems = [];
let quickSearch = writable('');

let sort = 'name';
let sortDirection = 'ascending';

let rowsPerPage = 10;
let currentPage = 0;

$: start = currentPage * rowsPerPage;
$: end = Math.min(start + rowsPerPage, filteredItems.length);
$: slice = filteredItems.slice(start, end);
$: lastPage = Math.max(Math.ceil( filteredItems.length / rowsPerPage) - 1, 0);
$: if (currentPage > lastPage) {
	currentPage = lastPage;
}

const findParentGroup = ( id ) => {
    for ( const item of items ) {
        if ( item.id == id ) { return item.name; }
    }
    return '';
}

const filterItemsQuick = () => {
	if ( $quickSearch.length ) {
		filteredItems = items.filter( item => {
			if ( String(item.name).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
			if ( String(item.email).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
			return false;
		});
	} else {
		filteredItems = items.slice();
		return filteredItems;
	}
	return filteredItems;
}

const downloadGroups = async () => {
	items = await getGroups();
	filteredItems = items;
	return items;
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
    $group_mode = 'view';
    $group_id = e.target.dataset.entryId;
	$screen = 'group';
	router.goto('/group/' + $group_id + '/view');
}

const unsubscribe_quickSearch = quickSearch.subscribe( v => {
	filteredItems = filterItemsQuick();
});

const exportToExcel = ( data ) => {
    var ws = XLSX.utils.aoa_to_sheet( [ [ "NAME", "CATEGORY", "EMAIL", "PRIVACY", "MEMBER COUNT", "GROUP COUNT" ], ...data ] ),
        ws_name = window.pnb.xlsx['groups-export'];
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
		row.push( v.name || '' );
		row.push( v.parent ? findParentGroup( v.parent ) : '' );
		row.push( v.email || '' );
		row.push( v.private === 'yes' ? 'private' : 'public' );
		row.push( v['member-count'] || '0' );
		row.push( v['group-count'] || '0' );
        data.push( row );
    }
    return exportToExcel( data );
}

onDestroy(() => {
	unsubscribe_quickSearch();
});

</script>

{#if $auth['grants']['groups-view']}

<div style="text-align: center;" class="mdc-typography--headline4">GROUPS</div>

{#await downloadGroups()}

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
<DataTable table$aria-label="Group List" style="width: 100%;"
  sortable
  bind:sort
  bind:sortDirection
  on:SMUIDataTable:sorted={handleSort}
  on:SMUIDataTableRow:click={handleRowClick}
>
	<Head>
		<Row>
			<Cell columnId="name" style="text-align: center;">
				<Label>NAME</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
			</Cell>
			<Cell columnId="parent" style="text-align: center;">
				<Label>PARENT GROUP</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
			</Cell>
			<Cell columnId="email" style="text-align: center;">
				<Label>EMAIL</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
			</Cell>
			<Cell columnId="private" style="text-align: center;">
				<Label>PRIVACY</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
			</Cell>
			<Cell columnId="member-count" style="text-align: center;">
				<Label>MEMBERS</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
			</Cell>
			<Cell columnId="group-count" style="text-align: center;">
				<Label>SUBGROUPS</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
			</Cell>
		</Row>
	</Head>
	<Body>
    {#each slice as item (item.id)}
      <Row data-entry-id="{item.id}">
        <Cell style="text-align: center;">
			{item.name || ''} {item.category ? ( '( ' + item.category + ' )' ) : ''}
		</Cell>
        <Cell style="text-align: center;">
			{item.parent ? findParentGroup( item.parent ) : ''}
		</Cell>
        <Cell style="text-align: center;">
			{item.email || ''}
		</Cell>
        <Cell style="text-align: center;">
			{item.private === 'yes' ? 'private' : 'public' }
		</Cell>
        <Cell style="text-align: center;">
			{item['member-count'] || '0'}
		</Cell>
        <Cell style="text-align: center;">
			{item['group-count'] || '0'}
		</Cell>
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
      {start + 1}-{end} of {filteredItems.length}
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

{#if window.pnb && window.pnb.collaboration}
        {#await invenio_search_community( 'public' ) then idata}
            {#if idata.invenioSearchCommunity == ''}
                {#await invenio_create_community( 'public' ) then iidata}
					<div>
	                	<a href="{iidata.invenioCreateCommunity}">{iidata.invenioCreateCommunity}</a>
					</div>
                {/await}
            {:else}
				<div>
	                <a href="{idata.invenioSearchCommunity}">{idata.invenioSearchCommunity}</a>
				</div>
            {/if}
        {/await}
        {#await invenio_search_community( 'internal' ) then idata}
            {#if idata.invenioSearchCommunity == ''}
                {#await invenio_create_community( 'internal' ) then iidata}
					<div>
	                	<a href="{iidata.invenioCreateCommunity}">{iidata.invenioCreateCommunity}</a>
					</div>
                {/await}
            {:else}
				<div>
	                <a href="{idata.invenioSearchCommunity}">{idata.invenioSearchCommunity}</a>
				</div>
            {/if}
        {/await}
{/if}

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