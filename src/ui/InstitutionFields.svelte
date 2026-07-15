<script>

import {meta, router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';

import { getInstitutionFields, getInstitutionFieldgroups } from '../utils/pnb-api.js';
import { orderKeys } from '../utils/pnb-download.js';

const fetchFields = async() => {
	let fields = await getInstitutionFields();
	let groups = await getInstitutionFieldgroups();
	let fields_ordered = orderKeys( fields, (a,b) => a.group == b.group ? ( a.weight - b.weight ) : groups[a.group].weight - groups[b.group].weight );
	return { fields, groups, fields_ordered }
}

const handleRowClick = ( e ) => {
	let field_id = e.target.dataset.entryId;
	router.goto('/institution-field/' + field_id + '/view');
}

const createRecord = async () => {
    router.goto('/institution-new-field');
}

</script>

{#await fetchFields()}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">INSTITUTION FIELDS</div>

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
            <Cell columnId="NAMEFIXED">
				<Label>NAME FIXED</Label>
			</Cell>
            <Cell columnId="DESCRIPTION">
				<Label>DESCRIPTION</Label>
			</Cell>
            <Cell columnId="GROUP">
				<Label>GROUP</Label>
			</Cell>
            <Cell columnId="ISREQUIRED">
				<Label>IS REQUIRED?</Label>
			</Cell>
            <Cell columnId="ISENABLED">
				<Label>IS ENABLED?</Label>
			</Cell>
            <Cell columnId="PRIVACYMODE">
				<Label>PRIVACY MODE</Label>
			</Cell>
        </Row>
    </Head>
    <Body>
	{#each data.fields_ordered as id (id)}
        <Row data-entry-id="{id}">
            <Cell>
				{id}
            </Cell>
            <Cell>
				{data.fields[id].weight}
            </Cell>
            <Cell>
				{data.fields[id].name_fixed}
            </Cell>
            <Cell>
				{data.fields[id].name_desc}
            </Cell>
            <Cell>
				{ data.groups[ data.fields[id].group ].name_full }
            </Cell>
            <Cell>
				{ data.fields[id].is_required === 'y' ? 'YES' : 'NO' }
            </Cell>
            <Cell>
				{ data.fields[id].is_enabled === 'y' ? 'YES' : 'NO' }
            </Cell>
            <Cell>
				{ data.fields[id].privacy }
            </Cell>
        </Row>
    {/each}
    </Body>
</DataTable>

    <div class="create-button">
        <Fab color="primary" on:click={() => { createRecord(); }} extended>
          <FabIcon class="material-icons">save</FabIcon>
            <FabLabel>CREATE NEW FIELD</FabLabel>
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