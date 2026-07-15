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
import CharacterCounter from '@smui/textfield/character-counter';

import Textfield from '@smui/textfield';
import Icon from '@smui/textfield/icon';
import HelperText from '@smui/textfield/helper-text';

import PleaseWait from './PleaseWait.svelte';
import AccessDenied from './AccessDenied.svelte';
import { sleep } from '../utils/sleep.js';

import { find_field_id } from '../utils/pnb-search.js';

import { getDocuments, getGroups } from '../utils/pnb-api.js';
import { convertDocuments } from '../utils/pnb-convert.js';
import { auth, event_id } from '../store.js';

import { getMembers } from '../utils/pnb-api.js';
import { convertMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';

import { downloadEvent } from '../utils/pnb-download.js';

import { screen, document_id, document_mode } from '../store.js';

const mid = window.pnb.mid ? parseInt(window.pnb.mid) : null;

let title = '', subtitle = '';
let pleaseWait = false;
let refresh = false;

let event = false;

let groups = false;
let members = false;
let documents = false;

let group_lookup  = {};
let member_lookup = {};

let items = [];
let filteredItems = [];

let sort = 'ts';
let sortDirection = 'descending';

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

const fetchEvent = async ( id ) => {

	event = await downloadEvent(id);
	title = event.cevent.name;
	subtitle = 'DOCUMENTS';

    const mem = await getMembers();
    members = await convertMembers( mem );
    sortConvertedMembers( members );
	for ( const m of members ) {
		member_lookup[m.id] = m;
	}

	groups = await getGroups();
	for ( const g of groups ) {
		group_lookup[ g.id ] = g;
	}

	documents = await getDocuments();
	documents = await convertDocuments( documents );
	documents = documents.filter( d => d.event_id == id );

    for ( const item of documents ) {
        if ( item.group_id  ) { item.group_id  = group_lookup[ item.group_id   ] ? group_lookup[ item.group_id ].name : 'NOT FOUND / DEACTIVE'; }
        if ( item.author_id ) { item.author_id = member_lookup[ item.author_id ] ? ( member_lookup[ item.author_id ].name_last + ', ' + member_lookup[ item.author_id ].name_first ) : 'NOT FOUND / DEACTIVE'  }
    }

	items = documents;
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
        if (typeof aVal === 'string' && typeof bVal === 'string') {
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

const unsubscribe_quickSearch = quickSearch.subscribe( v => {
    filteredItems = filterItemsQuick();
});

onDestroy(() => {
    unsubscribe_quickSearch();
});

</script>

{#key refresh}

{#if mid && $auth['grants']['events-view'] }

{#await fetchEvent( $event_id )}

<LinearProgress indeterminate />

{:then}

<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
<div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>

<Paper>

    {#if pleaseWait}
   	    <PleaseWait text="{pleaseWait}" />
    {:else}

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
        <Cell style="text-align: {doc.align}; width: {doc.width};">
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

	{/if}

</Paper>

{/await}

{:else}
	<AccessDenied />
{/if}

{/key}

<style>
</style>
