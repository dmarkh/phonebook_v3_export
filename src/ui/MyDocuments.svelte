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

import { getGroups, getMembers, getMemberDocuments, getDocuments, getDocumentFields } from '../utils/pnb-api.js';
import { convertDocuments, convertMembers } from '../utils/pnb-convert.js';
import { auth, screen } from '../store.js';
import { document_id, document_mode } from '../store.js';

const mid = parseInt(window.pnb.mid);

let member = false;
let members = false;
let docs = false;
let cdocs = false;
let lookup = {};
let memdocs = false;
let groups = false;

let title = '', subtitle = '';
let refresh = false;

const getMemberInfo = async () => {

    let mem = await getMembers();
    members = await convertMembers(mem);

	member = await downloadMember( mid );
	title = member.cmember.name_first + ' ' + member.cmember.name_last;
	subtitle = member.cinstitution.name_full;

	groups = await getGroups();
	memdocs = await getMemberDocuments( mid );

	const uniqids = [ ...new Set([ ...memdocs['owner'], ...memdocs['author'], ...memdocs['reviewer'] ]) ];

	if ( uniqids.length ) {
		docs = await getDocuments( 'full', 0, 1000000, uniqids );
		cdocs = await convertDocuments( docs );
		for ( const d of cdocs ) {
			lookup[ d.id ] = d;
		}
	}

	// eliminate inactive documents
	memdocs.owner = memdocs.owner.filter( docid => lookup[docid] );
	memdocs.author = memdocs.author.filter( docid => lookup[docid] );
	memdocs.reviewer = memdocs.reviewer.filter( docid => lookup[docid] );

	return member;
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

const handleRowClick = ( e ) => {
    $document_mode = 'view';
    $document_id = e.target.dataset.entryId;
    $screen = 'document';
    router.goto('/document/' + $document_id + '/view');
}

onDestroy(() => {

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
	<p style="text-align: center;">
    <Button color="primary" on:click={() => { $screen = 'new-document'; router.goto('/new-document'); }} variant="raised">
      <ButtonLabel>CREATE NEW DOCUMENT</ButtonLabel>
    </Button>
	</p>
	{#if !memdocs.owner.length}
		<p> NO OWN DOCUMENTS FOUND </p>
	{:else}
	<details open>
		<summary>DOCUMENTS:</summary>

	<DataTable table$aria-label="Institution List" style="width: 100%;"
		on:SMUIDataTableRow:click={handleRowClick}
	>
    <Head>
        <Row>
            {#each window.pnb.documents as doc}
            <Cell columnId="{doc.field}" style="text-align: {doc.align}; width: {doc.width};">
                <Label>{doc.title}</Label>
            </Cell>
            {/each}
        </Row>
    </Head>
    <Body>
    {#each memdocs.owner as id}
      <Row data-entry-id="{id}">
        {#each window.pnb.documents as doc}
        <Cell style="text-align: {doc.align}; width: {doc.width}; {doc.field == "title" ? "text-wrap: auto;":"" }">
			{#if lookup[id] }
				{#if doc.field == 'author_id'}
					{get_member_name(lookup[id][ doc.field ])}
				{:else if doc.field == 'group_id'}
					{get_group_name(lookup[id][ doc.field ])}
				{:else}
		            {lookup[id][ doc.field ] || ''}
				{/if}
			{:else}
				id: {id}, doc.field: {doc.field}
			{/if}
        </Cell>
        {/each}
      </Row>
    {/each}
    </Body>
	</DataTable>
	</details>

	{/if}

	{#if !memdocs.author.length}
		<p> NOT AN AUTHOR ON ANY DOCUMENTS </p>
	{:else}
	<br/>
	<details>
		<summary>AUTHOR ON DOCUMENTS:</summary>
	<DataTable table$aria-label="Institution List" style="width: 100%;"
		on:SMUIDataTableRow:click={handleRowClick}
	>
    <Head>
        <Row>
            {#each window.pnb.documents as doc}
            <Cell columnId="{doc.field}" style="text-align: {doc.align}; width: {doc.width};">
                <Label>{doc.title}</Label>
            </Cell>
            {/each}
        </Row>
    </Head>
    <Body>
    {#each memdocs.author as id}
      <Row data-entry-id="{id}">
        {#each window.pnb.documents as doc}
        <Cell style="text-align: {doc.align}; width: {doc.width}; {doc.field == "title" ? "text-wrap: auto;":"" }">
			{#if lookup[id] }
				{#if doc.field == 'author_id'}
					{get_member_name(lookup[id][ doc.field ])}
				{:else if doc.field == 'group_id'}
					{get_group_name(lookup[id][ doc.field ])}
				{:else}
		            {lookup[id][ doc.field ] || ''}
				{/if}
			{:else}
				id: {id}, doc.field: {doc.field}
			{/if}
        </Cell>
        {/each}
      </Row>
    {/each}
    </Body>
	</DataTable>
	</details>
	{/if}

	{#if !memdocs.reviewer.length}
		<p> NOT A REVIEWER ON ANY DOCUMENTS </p>
	{:else}
	<br/>
	<details>
	<summary>REVIEWER ON DOCUMENTS:</summary>
	<DataTable table$aria-label="Institution List" style="width: 100%;"
		on:SMUIDataTableRow:click={handleRowClick}
	>
    <Head>
        <Row>
            {#each window.pnb.documents as doc}
            <Cell columnId="{doc.field}" style="text-align: {doc.align}; width: {doc.width};">
                <Label>{doc.title}</Label>
            </Cell>
            {/each}
        </Row>
    </Head>
    <Body>
    {#each memdocs.reviewer as id}
      <Row data-entry-id="{id}">
        {#each window.pnb.documents as doc}
        <Cell style="text-align: {doc.align}; width: {doc.width}; {doc.field == "title" ? "text-wrap: auto;":"" }">
			{#if lookup[id] }
				{#if doc.field == 'author_id'}
					{get_member_name(lookup[id][ doc.field ])}
				{:else if doc.field == 'group_id'}
					{get_group_name(lookup[id][ doc.field ])}
				{:else}
		            {lookup[id][ doc.field ] || ''}
				{/if}
			{:else}
				id: {id}, doc.field: {doc.field}
			{/if}
        </Cell>
        {/each}
      </Row>
    {/each}
    </Body>
	</DataTable>
	</details>
	{/if}



	</Paper>

	{/await}

{/if}

{/key}

<style>
</style>