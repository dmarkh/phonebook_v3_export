<script>

import {meta, router, Route} from 'tinro';

import AccessDenied from './AccessDenied.svelte';
import PleaseWait from './PleaseWait.svelte';
import LinearProgress from '@smui/linear-progress';

import Select, { Option } from '@smui/select';
import IconButton from '@smui/icon-button';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';
import CharacterCounter from '@smui/textfield/character-counter';
import Paper from '@smui/paper';

import { getWorkflow, updateWorkflow } from '../utils/pnb-api.js';
import { sleep } from '../utils/sleep.js';
import { screen, auth, workflow_id } from '../store.js';

let pleaseWait = false;
let workflow = false;
let status = false;
let name = '';
let description = '';
let weight = 0;

const fetchWorkflow = async ( id ) => {
    workflow = await getWorkflow( id );
	if ( workflow.workflow ) { workflow = workflow.workflow; }
	status = workflow.status;
	name = workflow.name;
	description = workflow.description;
	weight = workflow.weight;
	console.log('workflow', workflow);
    return workflow;
}

const updateRecord = async () => {

    pleaseWait = 'UPDATING WORKFLOW, PLEASE WAIT';

    let data = {
		[$workflow_id]: {
			"status": status,
			"name": name,
			"description": description,
			"weight": weight
		}
	};
    let rc = await updateWorkflow( data );

    await sleep(1000);

	$screen = 'workflows';
	router.goto('/workflow/' + $workflow_id + '/view' );

    pleaseWait = false;
}

</script>

{#if $auth['grants']['workflows-edit']}
	{#if pleaseWait}
		<PleaseWait text="{pleaseWait}" />
	{:else}

	{#await fetchWorkflow( $workflow_id )}

	<LinearProgress indeterminate />

	{:then data}
	<div style="text-align: center;" class="mdc-typography--headline4">EDITING WORKFLOW</div>

	<Paper>

    	<Select
        	bind:value={ status }
    	    style="width: 100%;"
	        label="STATUS"
        	required=true
    	>
        	<Option value="active">ACTIVE</Option>
        	<Option value="inactive">INACTIVE</Option>
	    </Select>

        <Textfield bind:value={ name }
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="NAME"
            required=true
        >
          <svelte:fragment slot="helper">
            <HelperText>workflow name</HelperText>
          </svelte:fragment>
        </Textfield>

        <Textfield bind:value={ description }
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="DESCRIPTION"
            required=true
        >
          <svelte:fragment slot="helper">
            <HelperText>workflow description</HelperText>
          </svelte:fragment>
        </Textfield>

        <Textfield bind:value={ weight }
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="WEIGHT"
            required=true
        >
          <svelte:fragment slot="helper">
            <HelperText>workflow weight (ordering)</HelperText>
          </svelte:fragment>
        </Textfield>

    <p style="text-align: center;">
        <Button on:click={() => { updateRecord(); }} variant="raised">
            <ButtonIcon class="material-icons">save</ButtonIcon>
            <ButtonLabel>UPDATE WORKFLOW</ButtonLabel>
        </Button>
    </p>

	</Paper>

	{/await}

	{/if}
{:else}
	<AccessDenied />
{/if}
