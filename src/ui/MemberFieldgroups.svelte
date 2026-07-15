<script>

import {meta, router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';

import { getMemberFieldgroups } from '../utils/pnb-api.js';
import { orderKeys } from '../utils/pnb-download.js';

const fetchFieldgroups = async() => {
	let groups = await getMemberFieldgroups();
	let groups_ordered = orderKeys( groups, (a,b) => ( a.weight - b.weight ) );
	return { groups, groups_ordered }
}

const handleRowClick = ( e ) => {
	let field_id = e.target.dataset.entryId;
	router.goto('/member-fieldgroup/' + field_id + '/view');
}

const createRecord = async () => {
    router.goto('/member-new-fieldgroup');
}

</script>

{#await fetchFieldgroups()}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">MEMBER FIELDGROUPS</div>
<Paper>
<DataTable table$aria-label="Imported Data" style="width: 100%;" on:SMUIDataTableRow:click={handleRowClick}>
    <Head>
        <Row>
            <Cell columnId="ID">
				<Label>ID</Label>
			</Cell>
            <Cell columnId="WEIGHT">
				<Label>WEIGHT</Label>
			</Cell>
            <Cell columnId="NAMESHORT">
				<Label>SHORT NAME</Label>
			</Cell>
            <Cell columnId="NAMEFULL">
				<Label>FULL NAME</Label>
			</Cell>
            <Cell columnId="ISENABLED">
				<Label>IS ENABLED?</Label>
			</Cell>
        </Row>
    </Head>
    <Body>
	{#each data.groups_ordered as id (id)}
        <Row data-entry-id="{id}">
            <Cell>
				{id}
            </Cell>
            <Cell>
				{data.groups[id].weight}
            </Cell>
            <Cell>
				{data.groups[id].name_short}
            </Cell>
            <Cell>
				{data.groups[id].name_full}
            </Cell>
            <Cell>
				{data.groups[id].is_enabled === 'y' ? 'YES' : 'NO'}
            </Cell>
        </Row>
    {/each}
    </Body>
</DataTable>

    <div class="create-button">
        <Fab color="primary" on:click={() => { createRecord(); }} extended>
          <FabIcon class="material-icons">save</FabIcon>
            <FabLabel>CREATE NEW FIELDGROUP</FabLabel>
        </Fab>
    </div>

</Paper>
{/await}

<style>
.create-button {
    position: absolute;
    bottom: 2vmin;
    right: 2vmin;
}
</style>