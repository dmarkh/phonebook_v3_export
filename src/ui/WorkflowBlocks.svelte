<script>

import {meta, router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import Button, { Label as ButtonLabel } from '@smui/button';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';

import { getWorkflowBlocks } from '../utils/pnb-api.js';
import { workflowblock_id, workflowblock_mode, screen, auth } from '../store.js';

let blocks = false;

const fetchWorkflowBlocks = async () => {
	blocks = await getWorkflowBlocks();
}

const handleRowClick = ( e ) => {
    $workflowblock_mode = 'view';
    $workflowblock_id = e.target.dataset.entryId;
    $screen = 'workflow-blocks';
    router.goto('/workflow-block/' + $workflowblock_id + '/view');
}

</script>

{#await fetchWorkflowBlocks()}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">WORKFLOW BLOCKS</div>

<Paper>

        <DataTable table$aria-label="Document Workflow" style="width: 100%;"
			on:SMUIDataTableRow:click={handleRowClick}
		>
            <Head>
                <Row>
                    <Cell columnId="field" style="width: 10%; text-align: center;">
                        <Label>BLOCK</Label>
                    </Cell>
                    <Cell columnId="value" style="width: 60%; text-align: center;">
                        <Label>DESCRIPTION</Label>
                    </Cell>
                    <Cell columnId="value" style="width: 10%; text-align: center;">
                        <Label>STATUS</Label>
                    </Cell>
                    <Cell columnId="value" style="width: 10%; text-align: center;">
                        <Label>CONFIGURABLE</Label>
                    </Cell>
                    <Cell columnId="value" style="width: 10%; text-align: center;">
                        <Label>WEIGHT</Label>
                    </Cell>
                </Row>
            </Head>
            <Body>
			{#if blocks}
			{#each Object.values(blocks) as block}
              <Row data-entry-id="{block.id}">
                <Cell style="width: 10%; font-weight: bold;">
                    {block.name}
                </Cell>
                <Cell style="width: 60%; font-weight: bold;">
                    {block.description}
                </Cell>
                <Cell style="width: 10%; font-weight: bold; text-align: center;">
                    {block.status}
                </Cell>
                <Cell style="width: 10%; font-weight: bold; text-align: center;">
                    {block.configurable}
                </Cell>
                <Cell style="width: 10%; font-weight: bold; text-align: center;">
                    {block.weight}
                </Cell>
              </Row>
			{/each}
			{/if}
            </Body>
        </DataTable>

</Paper>

{/await}

