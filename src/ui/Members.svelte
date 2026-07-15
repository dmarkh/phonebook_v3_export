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

import { getMembers, getMemberFields } from '../utils/pnb-api.js';
import { convertMembers, addInstitutionsToConvertedMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';
import { fuzzySearchConvertedMembers } from '../utils/pnb-search.js';

import { screen, member_id, member_mode } from '../store.js';
import { auth } from '../store.js';

import Textfield from '@smui/textfield';
import Icon from '@smui/textfield/icon';
import HelperText from '@smui/textfield/helper-text';

let items = [];
let filteredItems = [];
let quickSearch = writable('');
let fuzzySearch = writable('');

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

const filterItemsQuick = () => {
	if ( $quickSearch ) {
		filteredItems = items.filter( item => {
			for ( const val of window.pnb.members ) {
				if ( String(item[ val.field ]).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
			}
			return false;
		});
	} else {
		filteredItems = items.slice();
		return filteredItems;
	}
	return filteredItems;
}

const filterItemsFuzzy = () => {
	if ( $fuzzySearch ) {
		filteredItems = fuzzySearchConvertedMembers( items, $fuzzySearch );
	} else {
		filteredItems = items.slice();
		return filteredItems;
	}
	return filteredItems;
}

const downloadMembers = async () => {
	let mem = await getMembers();
	items = await convertMembers( mem );
	items = await addInstitutionsToConvertedMembers( items );
	sortConvertedMembers( items );
	filteredItems = items;
	return items;
}

const handleSort = () => {
	filteredItems = ( $fuzzySearch.length ? filterItemsFuzzy() : filterItemsQuick() ).sort((a, b) => {
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
    $member_mode = 'view';
    $member_id = e.target.dataset.entryId;
	$screen = 'member';
	router.goto('/member/' + $member_id + '/view');
}

const unsubscribe_quickSearch = quickSearch.subscribe( v => {
	if ( v || ( !v && !$fuzzySearch ) ) {
		$fuzzySearch = '';
		filteredItems = filterItemsQuick();
	}
});

const unsubscribe_fuzzySearch = fuzzySearch.subscribe( v => {
	if ( v || ( !v && !$quickSearch ) ) {
		$quickSearch = '';
		filteredItems = filterItemsFuzzy();
	}
});

onDestroy(() => {
	unsubscribe_quickSearch();
	unsubscribe_fuzzySearch();
});

</script>

{#if $auth['grants']['members-view']}

{#await downloadMembers()}

<LinearProgress indeterminate />

{:then}

<div class="columns">
	<div>
<Textfield variant="outlined" bind:value={$quickSearch} label="Quick Search">
	<Icon class="material-icons" slot="trailingIcon">search</Icon>
	<HelperText slot="helper">search by matching substring</HelperText>
</Textfield>
	</div>
	<div>
<Textfield variant="outlined" bind:value={$fuzzySearch} label="Fuzzy Search">
	<Icon class="material-icons" slot="trailingIcon">search</Icon>
	<HelperText slot="helper">search by fuzzy match</HelperText>
</Textfield>
	</div>
{#if $auth['grants']['members-bulk-import']}
    <div>
        <Button on:click={() => { router.goto('/members-bulk-import'); }} variant="raised">
            <ButtonIcon class="material-icons">table</ButtonIcon>
            <ButtonLabel>BULK IMPORT</ButtonLabel>
        </Button>
    </div>
{/if}
{#if $auth['grants']['members-bulk-update']}
    <div>
        <Button on:click={() => { router.goto('/members-bulk-update'); }} variant="raised">
            <ButtonIcon class="material-icons">table_rows</ButtonIcon>
            <ButtonLabel>BULK UPDATE</ButtonLabel>
        </Button>
    </div>
{/if}
</div>

<Paper>
<DataTable table$aria-label="Member List" style="width: 100%;"
  sortable
  bind:sort
  bind:sortDirection
  on:SMUIDataTable:sorted={handleSort}
  on:SMUIDataTableRow:click={handleRowClick}
>
	<Head>
		<Row>
			{#each window.pnb.members as mem}
			<Cell columnId="{mem.field}" style="text-align: {mem.align}; width: {mem.width};">
				<Label>{mem.title}</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
			</Cell>
			{/each}
		</Row>
	</Head>
	<Body>
    {#each slice as item (item.id)}
      <Row data-entry-id="{item.id}">
		{#each window.pnb.members as mem}
        <Cell style="text-align: {mem.align}; width: {mem.width}; { mem.field == 'institution__name_full' ? "text-wrap: auto;" : ""}">
           {#if mem.field === 'institution__country' && item['institution__country_code']}
           		<img src="images/flags_iso_3166/24/{item['institution__country_code'].toLowerCase()}.png" style="vertical-align: text-bottom;"/>
           {/if}
			{item[ mem.field ] || ''}
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
      {start + 1}-{end} of {items.length}
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

.columns {
	display: flex;
	flex-wrap: wrap;
    align-items: baseline;
}

.columns > * {
	margin-right: 2vmin;
}

</style>