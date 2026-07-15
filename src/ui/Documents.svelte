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

import FileSaver from '../vendor/FileSaver.js';
import { s2ab } from '../utils/s2ab.js';
import * as XLSX from 'xlsx';

import { getDocuments, getDocumentFields } from '../utils/pnb-api.js';
import { convertDocuments } from '../utils/pnb-convert.js';
import { screen, document_id, document_mode } from '../store.js';
import { auth } from '../store.js';

import { getMembers, getGroups } from '../utils/pnb-api.js';
import { convertMembers } from '../utils/pnb-convert.js';

import Textfield from '@smui/textfield';
import Icon from '@smui/textfield/icon';
import HelperText from '@smui/textfield/helper-text';

let members = false;
let groups = false;

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

const get_member_name = ( mid ) => {
	const mem = members.find( m => m.id == mid );
	if ( mem ) {
		return mem.name_first + ' ' + mem.name_last;
	}
	return 'Unknown/Deactivated';
}

const get_group_name = ( gid ) => {
	const grp = groups.find( g => g.id == gid );
	if ( grp ) {
		return grp.name;
	}
	return 'Unknown/Deactivated';
}

const downloadDocuments = async () => {
	let mem = await getMembers();
	members = await convertMembers(mem);
	groups = await getGroups();

    let docs = await getDocuments( 'full', currentPage, rowsPerPage );

    items = await convertDocuments( docs );
	for ( const item of items ) {
		if ( item.group_id  ) { item.group_id  = get_group_name( item.group_id );   }
		if ( item.author_id ) { item.author_id = get_member_name( item.author_id ); }
	}
	items.reverse();

	filteredItems = items.slice();
	slice = filteredItems.slice(currentPage,rowsPerPage);
    return slice;
}

const filterItemsQuick = () => {
    if ( $quickSearch.length ) {
        filteredItems = items.filter( item => {
            if ( String(item.title).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            if ( String(item.author_id).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            if ( String(item.group_id).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            if ( String(item.category).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            if ( String(item.ts).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            if ( String(item.reference_id).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
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
        if ( typeof aVal === 'string' && typeof bVal === 'string' ) {
            return aVal.localeCompare(bVal);
        }
        return Number(aVal) - Number(bVal);
    });
}

const handleRowClick = ( e ) => {
	$document_mode = 'view';
	$document_id = e.target.dataset.entryId;
	$screen = 'document';
	router.goto('/document/' + $document_id + '/view');
}

const exportToExcel = ( data ) => {
	let tmp = [];
    for ( const f of window.pnb.documents ) {
	    tmp.push( f.title );
    }
	data = [ tmp, ...data ];
    var ws = XLSX.utils.aoa_to_sheet( data ),
        ws_name = window.pnb.xlsx['documents-export'];
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
        for ( const f of window.pnb.documents ) {
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

{#if $auth['grants']['documents-view']}

<div style="text-align: center;" class="mdc-typography--headline4">DOCUMENTS</div>

{#await downloadDocuments()}

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
  sortable
  bind:sort
  bind:sortDirection
  on:SMUIDataTable:sorted={handleSort}
  on:SMUIDataTableRow:click={handleRowClick}
>
	<Head>
		<Row>
			{#each window.pnb.documents as doc}
			<Cell columnId="{doc.field}" style="text-align: {doc.align}; width: {doc.width};">
				<Label>{doc.title}</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
			</Cell>
			{/each}
		</Row>
	</Head>
	<Body>
    {#each slice as item (item.id)}
      <Row data-entry-id="{item.id}">
		{#each window.pnb.documents as doc}
        <Cell style="text-align: {doc.align}; width: {doc.width}; {doc.field == 'title' ? "text-wrap: auto;" : ""}">
			{#if doc.field == 'ts'}
				{ item[doc.field] ? item[ doc.field ].replace(' 00:00:00','') : '' }
			{:else}
				{item[ doc.field ] || ''}
			{/if}
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
.save-button {
    position: absolute;
    bottom: 2vmin;
    left: 2vmin;
}
</style>