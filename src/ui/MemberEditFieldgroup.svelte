<script>

import PleaseWait from './PleaseWait.svelte';
import AccessDenied from './AccessDenied.svelte';

import LinearProgress from '@smui/linear-progress';
import Fab, { Label, Icon } from '@smui/fab';
import Paper, { Content } from '@smui/paper';
import Select, { Option } from '@smui/select';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';

import { sleep } from '../utils/sleep.js';
import { auth } from '../store.js';
import { getMemberFieldgroups, updateMemberFieldgroups } from '../utils/pnb-api.js';

import {router, Route} from 'tinro';

export let meta;

let pleaseWait = false, subtitle = '';
let preedit_field_values  = {},
    postedit_field_values = {};

const fetchData = async() => {
	let names = [
		{ 'name': 'id', 'editable': 0, 'value': meta.params.id },
		{ 'name': 'name_short', 'editable': 0, 'value': '' },
		{ 'name': 'name_full', 'editable': 1, 'value': '' },
		{ 'name': 'weight', 'editable': 1, 'value': '' },
		{ 'name': 'is_enabled', 'editable': 1, 'options': [ ['y','YES'], ['n','NO'] ] }
	];
	let fields = await getMemberFieldgroups();
	let field = fields[ meta.params.id ];
	for ( const v of names ) {
		preedit_field_values[ v.name ]  = field[ v.name ];
		postedit_field_values[ v.name ] = field[ v.name ];
		v.value = field[ v.name ] || '';
	}
	subtitle = names[1].value;
	return names;
}

const updateRecord = async () => {
	pleaseWait = 'UPDATING FIELD, PLEASE WAIT';

	let changes = Object.entries( postedit_field_values ).filter( e => preedit_field_values[ e[0] ] !== e[1] );

    let data = { [ meta.params.id ]: {} };
    for ( const v of changes ) {
        data[ meta.params.id ][ v[0] ] = v[1];
    }

    let rc = await updateMemberFieldgroups( data );

	await sleep(1000);
	router.goto( '/member-fieldgroup/' + meta.params.id + '/view' );
	pleaseWait = false;
}

</script>

{#if $auth['grants']['descriptors-edit']}

{#await fetchData()}
	<LinearProgress indeterminate />

{:then data}
    <div style="text-align: center;" class="mdc-typography--headline4">MEMBER FIELDGROUP DESCRIPTOR: {meta.params.id}</div>
	<div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>
<Paper>
	<table width="100%">
	{#each data as item, idx (idx)}
		<tr>
			<td>
			{#if item.editable}
				{#if item.options}
					<Select
						bind:value={postedit_field_values[ item.name ]}
						style="width: 100%;"
						label="{item.name}"
					>
						{#each item.options as opt (opt[0]) }
							<Option value={opt[0]}>{opt[1]}</Option>
						{/each}
					</Select>
				{:else}
					<Textfield bind:value={ postedit_field_values[ item.name ] }
						style="width: 100%;"
						helperLine$style="width: 100%;"
						label="{item.name}"
					>
					<svelte:fragment slot="helper">
						<HelperText>{item.name}</HelperText>
					</svelte:fragment>
					</Textfield>
				{/if}

			{/if}
			</td>
		</tr>
	{/each}
	</table>

	<div class="save-button">
    	<Fab color="primary" on:click={() => { updateRecord(); }} extended>
	      <Icon class="material-icons">save</Icon>
		    <Label>UPDATE RECORD</Label>
	    </Fab>
	</div>
</Paper>
{/await}

	{#if pleaseWait}
		<PleaseWait text="{pleaseWait}" />
	{/if}

{:else}
	<AccessDenied />
{/if}

<style>
.save-button {
    position: absolute;
    bottom: 2vmin;
    right: 2vmin;
}
</style>