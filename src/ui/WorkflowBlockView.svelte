<script>

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';

import { find_field_id } from '../utils/pnb-search.js';

import { workflowblock_id, auth } from '../store.js';
import { getWorkflowBlock } from '../utils/pnb-api.js';

let title = '', subtitle = '';

let block = false;

const fetchWorkflowBlock = async ( id ) => {
	block = await getWorkflowBlock( id );
	if ( block.block ) { block = block.block; }
	title = 'WORKFLOW BLOCK';
	return block;
}

</script>

{#await fetchWorkflowBlock( $workflowblock_id )}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
<div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>

<Paper>
<DataTable table$aria-label="WorkflowBlock Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="field" style="width: 20%; text-align: left;">
                <Label>FIELD</Label>
            </Cell>
            <Cell columnId="value" style="width: 60%; text-align: left;">
                <Label>VALUE</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
      <Row>
        <Cell style="width: 30%; font-weight: bold;">
			ID
		</Cell>
        <Cell style="width: 70%; white-space: normal;">
			{block.id}
		</Cell>
      </Row>
      <Row>
        <Cell style="width: 30%; font-weight: bold;">
			NAME
		</Cell>
        <Cell style="width: 70%; white-space: normal;">
			{block.name}
		</Cell>
      </Row>
      <Row>
        <Cell style="width: 30%; font-weight: bold;">
			DESCRIPTION
		</Cell>
        <Cell style="width: 70%; white-space: normal;">
			{block.description}
		</Cell>
      </Row>
      <Row>
        <Cell style="width: 30%; font-weight: bold;">
			CONFIGURABLE
		</Cell>
        <Cell style="width: 70%; white-space: normal;">
			{block.configurable}
		</Cell>
      </Row>
      <Row>
        <Cell style="width: 30%; font-weight: bold;">
			STATUS
		</Cell>
        <Cell style="width: 70%; white-space: normal;">
			{block.status}
		</Cell>
      </Row>
      <Row>
        <Cell style="width: 30%; font-weight: bold;">
			WEIGHT
		</Cell>
        <Cell style="width: 70%; white-space: normal;">
			{block.weight}
		</Cell>
      </Row>
      <Row>
        <Cell style="width: 30%; font-weight: bold;">
			BLOCK TYPE ID
		</Cell>
        <Cell style="width: 70%; white-space: normal;">
			{block.block_type_id}
		</Cell>
      </Row>
    </Body>
</DataTable>
</Paper>

{/await}

<style>

</style>