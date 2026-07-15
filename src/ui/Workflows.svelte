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

import { workflow_mode, workflow_id, screen, auth } from '../store.js';

import { getWorkflows } from '../utils/pnb-api.js';

import Textfield from '@smui/textfield';
import Icon from '@smui/textfield/icon';
import HelperText from '@smui/textfield/helper-text';

let workflows = false;

const downloadWorkflows = async () => {
	workflows = await getWorkflows();
    return workflows;
}

const handleRowClick = ( e ) => {
	$workflow_mode = 'view';
	$workflow_id = e.target.dataset.entryId;
	$screen = 'workflow';
	router.goto('/workflow/' + $workflow_id + '/view');
}

onDestroy(() => {
});

</script>

{#if $auth['grants']['workflows-view']}

<div style="text-align: center;" class="mdc-typography--headline4">WORKFLOWS</div>

{#await downloadWorkflows()}

<LinearProgress indeterminate />

{:then}

<Paper>
<DataTable table$aria-label="Institution List" style="width: 100%;"
  on:SMUIDataTableRow:click={handleRowClick}
>
	<Head>
		<Row>
			{#each window.pnb.workflows as wfl}
			<Cell columnId="{wfl.field}" style="text-align: {wfl.align}; width: {wfl.width};">
				<Label>{wfl.title}</Label>
			</Cell>
			{/each}
		</Row>
	</Head>
	<Body>
    {#each Object.values(workflows) as item (item.id)}
      <Row data-entry-id="{item.id}">
		{#each window.pnb.workflows as wfl}
        <Cell style="text-align: {wfl.align}; width: {wfl.width}; white-space: normal;">
			{item[ wfl.field ] || ''}
		</Cell>
		{/each}
      </Row>
    {/each}
	</Body>
</DataTable>
</Paper>
{/await}

{:else}
	<AccessDenied />
{/if}

<style>

</style>