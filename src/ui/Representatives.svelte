<script>

import { writable } from 'svelte/store';
import { onDestroy } from 'svelte';

import {router, Route} from 'tinro';
import { tran } from '../utils/tran.js';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';

import AccessDenied from './AccessDenied.svelte';

import { getMembers, getMemberFields, getInstitutions, getInstitutionFields } from '../utils/pnb-api.js';
import { convertMembers, convertInstitutions, addInstitutionsToConvertedMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers, sortConvertedInstitutions } from '../utils/pnb-sort.js';
import { fuzzySearchConvertedMembers, find_field_id } from '../utils/pnb-search.js';

import { screen, member_id, member_mode } from '../store.js';
import { auth } from '../store.js';

import Textfield from '@smui/textfield';
import Icon from '@smui/textfield/icon';
import HelperText from '@smui/textfield/helper-text';

import FileSaver from '../vendor/FileSaver.js';
import { s2ab } from '../utils/s2ab.js';
import * as XLSX from 'xlsx';

let items = [];
let filteredItems = [];
let quickSearch = writable('');
let fuzzySearch = writable('');

let display_fields = JSON.parse(JSON.stringify(window.pnb['filter-representatives']['display-fields'])),
    sort_fields = JSON.parse(JSON.stringify(window.pnb['filter-representatives']['sort-fields']));

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
			for ( const val of window.pnb.representatives ) {
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

    let ifields = await getInstitutionFields();
    let fid = find_field_id( ifields, 'council_representative' );
	if ( fid && ( ifields[fid].is_enabled == 'y' ) ) {
		// representatives use Institutions, not Members
	    const inst = await getInstitutions();
    	let iinst = await convertInstitutions( inst );
	    sortConvertedInstitutions( iinst );
		let members = await convertMembers( mem );
			members = await addInstitutionsToConvertedMembers( members );
		items = iinst.map( i => ( members.find( m => m.id == i.council_representative ) || false ) ).filter( i => i !== false );
		filteredItems = items;

	} else {
		// remove members with no member_role or member_role = 'member'
		items = await convertMembers( mem );
		items = await addInstitutionsToConvertedMembers( items );
		sortConvertedMembers( items );
		items = items.filter( i => i.member_role.toLowerCase() !== 'member' );
		filteredItems = items;
	}

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

const exportToExcel = ( data ) => {
    var ws = XLSX.utils.aoa_to_sheet( data ),
        ws_name = window.pnb.xlsx['representatives-export'];
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
        for ( const f of display_fields ) {
            row.push( v[ f ] );
        }
        data.push( row );
    }
    return exportToExcel( data );
}

</script>

{#if $auth['grants']['representatives-view']}

{#await downloadMembers()}

<LinearProgress indeterminate />

{:then}

<div class="columns">
	<div>
<Textfield variant="outlined" bind:value={$quickSearch} label="{tran('_quick_search_')}">
	<Icon class="material-icons" slot="trailingIcon">search</Icon>
	<HelperText slot="helper">{tran('_quick_search_help_')}</HelperText>
</Textfield>
	</div>
	<div>
<Textfield variant="outlined" bind:value={$fuzzySearch} label="{tran('_fuzzy_search_')}">
	<Icon class="material-icons" slot="trailingIcon">search</Icon>
	<HelperText slot="helper">{tran('_fuzzy_search_help_')}</HelperText>
</Textfield>
	</div>
</div>

<Paper>
<div style="text-align: center;" class="mdc-typography--headline4">{tran('_representatives_')}</div>
<DataTable table$aria-label="Member List" style="width: 100%;"
  sortable
  bind:sort
  bind:sortDirection
  on:SMUIDataTable:sorted={handleSort}
  on:SMUIDataTableRow:click={handleRowClick}
>
	<Head>
		<Row>
			{#each window.pnb.representatives as mem}
			<Cell columnId="{mem.field}" style="text-align: {mem.align}; width: {mem.width};">
				<Label>{tran(mem.title.toUpperCase())}</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
			</Cell>
			{/each}
		</Row>
	</Head>
	<Body>
    {#each slice as item (item.id)}
      <Row data-entry-id="{item.id}">
		{#each window.pnb.representatives as mem}
        <Cell style="text-align: {mem.align}; width: {mem.width}; { mem.field == "institution__name_full" ? "text-wrap: auto;":""}">
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