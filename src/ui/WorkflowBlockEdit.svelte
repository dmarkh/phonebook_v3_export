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

import { getWorkflowBlock, updateWorkflowBlock } from '../utils/pnb-api.js';
import { sleep } from '../utils/sleep.js';
import { screen, auth, workflowblock_id } from '../store.js';

let pleaseWait = false;
let block = false;

let name = '';
let description = '';
let weight = 0;
let configurable = '';
let status = false;

const fetchWorkflowBlock = async ( id ) => {
    block = await getWorkflowBlock( id );
	if ( block.block ) { block = block.block; }

	name 	= block.name;
	description = block.description;
	weight = block.weight;
	configurable = block.configurable;
	status 	= block.status;

    return block;
}

const updateRecord = async () => {

    pleaseWait = 'UPDATING WORKFLOW BLOCK, PLEASE WAIT';

    let data = {
			"id"			: $workflowblock_id,
			"status"		: status,
			"name"			: name,
			"description"	: description,
			"configurable"	: configurable,
			"weight"		: weight
	};
	console.log('update data', data);

    let rc = await updateWorkflowBlock( data );
	console.log('rc: ' + rc );

    await sleep(1000);

	$screen = 'workflow-blocks';
	router.goto('/workflow-block/' + $workflowblock_id + '/view' );

    pleaseWait = false;
}

</script>

{#if $auth['grants']['workflows-edit']}
	{#if pleaseWait}
		<PleaseWait text="{pleaseWait}" />
	{:else}

	{#await fetchWorkflowBlock( $workflowblock_id )}

	<LinearProgress indeterminate />

	{:then data}
	<div style="text-align: center;" class="mdc-typography--headline4">EDITING WORKFLOW BLOCK</div>

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

    	<Select
        	bind:value={ configurable }
    	    style="width: 100%;"
	        label="CONFIGURABLE"
        	required=true
    	>
        	<Option value="not-configurable">NOT CONFIGURABLE</Option>
        	<Option value="members">MEMBERS</Option>
        	<Option value="group">GROUP</Option>
        	<Option value="group-and-roles">GROUP AND ROLES</Option>
	    </Select>


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
            <ButtonLabel>UPDATE WORKFLOW BLOCK</ButtonLabel>
        </Button>
    </p>

	</Paper>

	{/await}

	{/if}
{:else}
	<AccessDenied />
{/if}
