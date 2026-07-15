<script>

import {meta, router, Route} from 'tinro';

import AccessDenied from './AccessDenied.svelte';
import PleaseWait from './PleaseWait.svelte';

import Select, { Option } from '@smui/select';
import IconButton from '@smui/icon-button';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';
import CharacterCounter from '@smui/textfield/character-counter';
import Paper from '@smui/paper';

import { screen, auth, workflow_id } from '../store.js';
import { createWorkflow } from '../utils/pnb-api.js';
import { sleep } from '../utils/sleep.js';

let pleaseWait = false;
let wdata = { name: '', description: '', weight: 0 };

const updateRecord = async () => {

    pleaseWait = 'ADDING NEW WORKFLOW, PLEASE WAIT';

    const data = {
		"status": "active",
		"name": wdata.name,
		"description": wdata.description,
		"weight": wdata.weight
	};

    let rc = await createWorkflow( data );

    await sleep(1000);

	$screen = 'workflows';
	if ( rc && rc.id ) {
		$workflow_id = rc.id;
		router.goto('/workflow/' + rc.id + '/view' );
	} else {
		router.goto('/workflows');
	}
}

</script>

{#if $auth['grants']['workflows-edit']}
	{#if pleaseWait}
		<PleaseWait text="{pleaseWait}" />
	{:else}

	<div style="text-align: center;" class="mdc-typography--headline4">NEW WORKFLOW</div>

	<Paper>

        <Textfield bind:value={ wdata.name }
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="NAME"
            required=true
        >
          <svelte:fragment slot="helper">
            <HelperText>workflow name</HelperText>
          </svelte:fragment>
        </Textfield>

        <Textfield bind:value={ wdata.description }
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="DESCRIPTION"
            required=true
        >
          <svelte:fragment slot="helper">
            <HelperText>workflow description</HelperText>
          </svelte:fragment>
        </Textfield>

        <Textfield bind:value={ wdata.weight }
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="WEIGHT"
        >
          <svelte:fragment slot="helper">
            <HelperText>workflow weight (ordering)</HelperText>
          </svelte:fragment>
        </Textfield>

	<br/>
	<br/>
	<p style="text-align: center;">
        <Button on:click={() => { updateRecord(); }} variant="raised">
            <ButtonIcon class="material-icons">save</ButtonIcon>
            <ButtonLabel>CREATE NEW WORKFLOW</ButtonLabel>
        </Button>
	</p>

	</Paper>

	{/if}
{:else}
	<AccessDenied />
{/if}


<style>
    :global(.mdc-text-field__input::-webkit-calendar-picker-indicator) {
        display: initial !important;
    }
</style>