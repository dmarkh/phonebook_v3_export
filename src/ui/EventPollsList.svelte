<script>

import { onMount } from 'svelte';
import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import Button, { Icon as ButtonIcon, Label as ButtonLabel } from '@smui/button';
import IconButton from '@smui/icon-button';

import Tab, { Icon as TabIcon, Label as TabLabel } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Paper, { Content } from '@smui/paper';

import LinearProgress from '@smui/linear-progress';
import AccessDenied from './AccessDenied.svelte';

import { auth } from '../store.js';
import { screen } from '../store.js';

import { listEventPolls } from '../utils/pnb-api.js';

export let meta;

$screen = 'meeting-polls-list';

const mid = window.pnb.mid ? parseInt(window.pnb.mid) : null;

let polls = false;

const getPolls = async () => {
	polls = await listEventPolls();
}

const handleRowClick = ( e ) => {
    const poll_id = e.target.dataset.entryId;
    $screen = 'meeting-polls';
    router.goto('/meeting-poll/' + poll_id);
}

onMount(async () => {

});

</script>

{#if !$auth['grants']['event-poll-view'] }

    <AccessDenied />

{:else}

	<div style="text-align: center;" class="mdc-typography--headline4">MEETING POLLS</div>

	{#await getPolls()}

		<LinearProgress indeterminate />

	{:then}

	<Paper>
	<DataTable table$aria-label="Polls Data" style="width: 100%;" on:SMUIDataTableRow:click={handleRowClick}>
    <Head>
        <Row>
            <Cell columnId="field" style="width: 20%; text-align: left;">
                <Label>TS</Label>
            </Cell>
            <Cell columnId="value" style="width: 60%; text-align: left;">
                <Label>TITLE</Label>
            </Cell>
            <Cell columnId="value" style="width: 10%; text-align: left;">
                <Label>TYPE</Label>
            </Cell>
            <Cell columnId="value" style="width: 10%; text-align: left;">
                <Label>STATUS</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
	{#if polls.length}
	{#each polls as item (item.id)}
		<Row data-entry-id="{item.id}">
        	<Cell>{item.ts}</Cell>
        	<Cell>{item.title}</Cell>
        	<Cell>{item.type}</Cell>
        	<Cell>{item.status}</Cell>
		</Row>
	{/each}
	{/if}
    </Body>
	</DataTable>
	</Paper>

	{/await}

{/if}

<style>

</style>