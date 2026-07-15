<script>

import { writable } from 'svelte/store';
import { onDestroy } from 'svelte';

import { router, Route } from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';
import Button, { Label as ButtonLabel } from '@smui/button';

import Textfield from '@smui/textfield';
import Icon from '@smui/textfield/icon';
import HelperText from '@smui/textfield/helper-text';

import { downloadMember } from '../utils/pnb-download.js';
import { getGroups, getMemberGroups } from '../utils/pnb-api.js';
import { groupAddMember, groupRemoveMember } from '../utils/pnb-api.js';

import { member_id, auth, screen } from '../store.js';

const mid = $member_id;

let gdata = { tgroups: [] };
let member = false;
let title = '', subtitle = '';
let refresh = false;

let filtered_groups = [];
let filtered_new_groups = [];

let quickSearch = writable('');
let groupSearch = writable('');

let sort = 'name';
let sortDirection = 'ascending';

let rowsPerPage = 10;
let currentPage = 0;

$: start = currentPage * rowsPerPage;
$: end = Math.min(start + rowsPerPage, filtered_groups.length);
$: slice = filtered_groups.slice(start, end);
$: lastPage = Math.max(Math.ceil( filtered_groups.length / rowsPerPage) - 1, 0);
$: if (currentPage > lastPage) {
    currentPage = lastPage;
}

const handleSort = () => {
    filtered_groups = ( filterItemsQuick() ).sort((a, b) => {
        const [aVal, bVal] = [ gdata.lgroups[a.group_id][sort], gdata.lgroups[b.group_id][sort] ][
            sortDirection === 'ascending' ? 'slice' : 'reverse'
        ]();
        if (typeof aVal === 'string' && typeof bVal !== 'string') {
            return aVal.localeCompare(String(''));
        } else if (typeof aVal !== 'string' && typeof bVal === 'string') {
            return String('').localeCompare(bVal);
        } else if (typeof aVal === 'string' && typeof bVal === 'string') {
            return aVal.localeCompare(bVal);
        }
        return Number(aVal) - Number(bVal);
    });
}

const getMemberInfo = async () => {
	member = await downloadMember( mid );
	title = member.cmember.name_first + ' ' + member.cmember.name_last;
	subtitle = member.cinstitution.name_full;
	return member;
}

const fetchGroups = async () => {
    gdata.groups = await getGroups();
	gdata.lgroups = {};
	for ( const grp of gdata.groups ) {
		gdata.lgroups[grp.id] = grp;
	}
   	gdata.tgroups = await getMemberGroups( mid );
	gdata.egroups = {};
	if ( gdata.tgroups ) {
		for ( const tg of gdata.tgroups ) {
			gdata.egroups[ tg.group_id ] = gdata.lgroups[tg.group_id];
		}
	}
	setTimeout( () => {
		filterItemsQuick();
	}, 0 );
    return gdata;
}

const filterItemsQuick = () => {
    if ( $quickSearch ) {
        filtered_groups = gdata.tgroups.filter( item => {
			const group = gdata.groups.find( g => g.id == item.group_id );
			if ( group && group.name.toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
			if ( group && group.desc.toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            return false;
        });
    } else {
        filtered_groups = gdata.tgroups.slice();
    }
    return filtered_groups;
}

const filterNewGroupsQuick = () => {
    if ( $groupSearch ) {
        filtered_new_groups = gdata.groups.filter( group => {
			if ( gdata.egroups[ group.id ] ) { return false; }
			if ( group.name.toLowerCase().includes( $groupSearch.toLowerCase() ) ) { return true; }
			if ( group.desc.toLowerCase().includes( $groupSearch.toLowerCase() ) ) { return true; }
            return false;
        });
    } else {
		filtered_new_groups = [];
    }
    return filtered_new_groups;
}

const unsubscribe_quickSearch = quickSearch.subscribe( v => {
    if ( v ) {
        filtered_groups = filterItemsQuick();
    } else {
        filtered_groups = gdata.tgroups.slice();
    }
});

const unsubscribe_groupSearch = groupSearch.subscribe( v => {
    if ( v ) {
        filtered_new_groups = filterNewGroupsQuick();
    } else {
        filtered_new_groups = [];
    }
});

export const join_group = async ( id ) => {
	console.log('joining group ' + id );
    let rc = await groupAddMember({ group_id: id, member_id: mid, role_id: 0 });
	$groupSearch = '';
	refresh = !refresh;
	return rc;
}

export const leave_group = async ( id ) => {
	console.log('leaving group ' + id );
	let rc = await groupRemoveMember({ group_id: id, member_id: mid });
	$groupSearch = '';
	refresh = !refresh;
	return rc;
}

onDestroy(() => {
    unsubscribe_quickSearch();
	unsubscribe_groupSearch();
});

</script>

{#key refresh}

{#if !mid}
    <div style="text-align: center; padding: 5vmin;">
        MEMBER NOT FOUND BY ORCID LOOKUP. PLEASE ASK YOUR REPRESENTATIVE (OR THE PHONEBOOK ADMIN) TO UPDATE THE RECORD!
    </div>
{:else}

	{#await getMemberInfo()}
		<LinearProgress indeterminate />
	{:then data}

	<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
    {#if subtitle}
    	<div style="text-align: center;" class="mdc-typography--subtitle1">{@html subtitle}</div>
    {/if}
	<Paper>
	{#await fetchGroups()}
		<LinearProgress indeterminate />
	{:then group_data}
		{#if !gdata || !gdata.tgroups.length}
			<p style="text-align: center;">NO GROUP MEMBERSHIPS FOUND</p>
		{:else}

    	<div style="text-align: center; margin-top: 2vmin;">
	        <Textfield variant="outlined" bind:value={$quickSearch} label="Search Groups">
            	<Icon class="material-icons" slot="trailingIcon">search</Icon>
        	    <HelperText slot="helper">search by matching substring</HelperText>
    	    </Textfield>
	    </div>

		<DataTable table$aria-label="Group Data" style="width: 100%;"
    	  sortable
	      bind:sort
    	  bind:sortDirection
	      on:SMUIDataTable:sorted={handleSort}
		>
		    <Head>
        		<Row>
		            <Cell columnId="name" style="text-align: center;">
        		        <Label>GROUP</Label>
						<IconButton class="material-icons">arrow_upward</IconButton>
		            </Cell>
        		    <Cell columnId="role" style="text-align: center;">
                		<Label>ROLE</Label>
						<IconButton class="material-icons">arrow_upward</IconButton>
		            </Cell>
		            <Cell columnId="private" style="text-align: center;">
        		        <Label>PRIVATE?</Label>
						<IconButton class="material-icons">arrow_upward</IconButton>
		            </Cell>
        		</Row>
		    </Head>
		    <Body>
			{#each filtered_groups as grp (grp.group_id)}
			{#if grp.group_id && gdata && gdata.lgroups && gdata.lgroups[grp.group_id] }
				<Row data-entry-id="group">
			        <Cell style="text-align: center; cursor: pointer;" on:click={() => { router.goto('/group/' + grp.group_id + '/view'); $screen = 'groups';  }} >{ gdata.lgroups[grp.group_id].name }</Cell>
			        <Cell style="text-align: center;">{ ( grp.role_id && gdata.lgroups[grp.group_id].roles[ grp.role_id ] ) ? gdata.lgroups[grp.group_id].roles[ grp.role_id ].role : '' }</Cell>
			        <Cell style="text-align: center;">{ gdata.lgroups[grp.group_id].private }</Cell>
				</Row>
			{/if}
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
      {start + 1}-{end} of {filtered_groups.length}
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

	{/await}
	</Paper>

	{/await}

{/if}

{/key}

<style>
</style>