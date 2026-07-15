<script>

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';

import LinearProgress from '@smui/linear-progress';
import Paper, { Content } from '@smui/paper';

import { sleep } from '../utils/sleep.js';
import { auth } from '../store.js';
import { getTaskFields } from '../utils/pnb-api.js';

import {router, Route} from 'tinro';

export let meta;

let subtitle = '';

const fetchData = async() => {
	let names = [
		{ 'name': 'id', 'editable': 0, 'value': meta.params.id },
		{ 'name': 'name_fixed', 'editable': 0, 'value': '' },
		{ 'name': 'name_desc', 'editable': 1, 'value': '' },
		{ 'name': 'weight', 'editable': 1, 'value': '' },
		{ 'name': 'type', 'editable': 0, 'value': '' },
		{ 'name': 'options', 'editable': 1, 'value': '' },
		{ 'name': 'size_min', 'editable': 1, 'value': '' },
		{ 'name': 'size_max', 'editable': 1, 'value': '' },
		{ 'name': 'hint_short', 'editable': 1, 'value': '' },
		{ 'name': 'hint_full', 'editable': 1, 'value': '' },
		{ 'name': 'is_required', 'editable': 1, 'options': [ ['y','YES'], ['n','NO'] ] },
		{ 'name': 'is_enabled', 'editable': 1, 'options': [ ['y','YES'], ['n','NO'] ] }
	];
	let fields = await getTaskFields();
	let field = fields[ meta.params.id ];
	for ( const v of names ) {
		v.value = field[ v.name ] || '';
		if ( v.options ) {
			let opt = v.options.find( o => o[0] === v.value );
			if ( opt ) { v.value = opt[1]; }
		}
	}
	subtitle = names[1].value + ' : ' + names[4].value;
	return names;
}

</script>

{#await fetchData()}
	<LinearProgress indeterminate />

{:then data}

    <div style="text-align: center;" class="mdc-typography--headline4">TASK FIELD DESCRIPTOR: {meta.params.id}</div>
	<div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>
<Paper>
<DataTable table$aria-label="Task Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="field" style="width: 20%; text-align: left;">
                <Label>FIELD NAME</Label>
            </Cell>
            <Cell columnId="value" style="width: 60%; text-align: left;">
                <Label>FIELD VALUE</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each data as item (item.name)}
      <Row data-entry-id="{item.name}">
        <Cell style="font-weight: bold; align: right;">{item.name}</Cell>
        <Cell style="align: left;">{item.value}</Cell>
      </Row>
    {/each}
    </Body>
</DataTable>
</Paper>
{/await}

<style>
</style>