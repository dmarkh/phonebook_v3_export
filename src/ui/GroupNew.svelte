<script>

import {meta, router, Route} from 'tinro';

import AccessDenied from './AccessDenied.svelte';
import PleaseWait from './PleaseWait.svelte';
import LinearProgress from '@smui/linear-progress';

import Paper, { Content } from '@smui/paper';
import Select, { Option } from '@smui/select';
import IconButton from '@smui/icon-button';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';
import CharacterCounter from '@smui/textfield/character-counter';

import { screen } from '../store.js';

import { getGroups, createGroup } from '../utils/pnb-api.js';
import { sleep } from '../utils/sleep.js';
import { auth } from '../store.js';

let pleaseWait = false;

let data = { parent: 0, name: '', desc: '', category: '', email: '', status: 'active', private: 'no' };
let groups = [];

const fetchGroups = async () => {
    groups = await getGroups();
    return data;
}

const updateRecord = async () => {
    pleaseWait = 'ADDING NEW GROUP, PLEASE WAIT';
    let rc = await createGroup( data );
    await sleep(1000);
    $screen = 'groups';
    if ( rc ) {
        router.goto('/group/' + rc.id + '/view' );
    } else {
		console.log('ERROR: new group has not been created');
	}
    pleaseWait = false;
}

</script>

{#if $auth['grants']['groups-edit']}
	{#if pleaseWait}
    	<PleaseWait text="{pleaseWait}" />
	{:else}

{#await fetchGroups()}

	<LinearProgress indeterminate />

{:then}

	<div style="text-align: center;" class="mdc-typography--headline4">NEW GROUP</div>

	<!-- PARENT //-->

	<Paper>

    <Select bind:value={data.parent}
        style="width: 100%;"
        label="PARENT GROUP"
    >
      <Option value=0>--- NO PARENT ---</Option>
      {#each groups as group ( group.id ) }
        <Option value={group.id}>{group.name}</Option>
      {/each}
    </Select>

	<!-- NAME //-->
        <Textfield bind:value={data.name}
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="GROUP NAME"
            required=true
        >
          <svelte:fragment slot="helper">
            <HelperText>FULL NAME OF THE GROUP</HelperText>
          </svelte:fragment>
        </Textfield>

	<!-- DESC //-->
        <Textfield textarea bind:value={data.desc}
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="GROUP DESCRIPTION"
            required=false
        >
          <svelte:fragment slot="helper">
            <HelperText>FULL DESCRIPTION OF THE GROUP</HelperText>
          </svelte:fragment>
        </Textfield>

	<!-- CATEGORY //-->
        <Textfield bind:value={data.category}
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="GROUP CATEGORY"
        >
          <svelte:fragment slot="helper">
            <HelperText>Management, Documents, PWG, etc</HelperText>
          </svelte:fragment>
        </Textfield>

	<!-- EMAIL //-->
        <Textfield bind:value={data.email}
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="GROUP EMAIL"
        >
          <svelte:fragment slot="helper">
            <HelperText>EMAIL / MAILING LIST OF THE GROUP</HelperText>
          </svelte:fragment>
        </Textfield>


	<!-- STATUS //-->

    <Select bind:value={data.status}
        style="width: 100%;"
        label="GROUP STATUS"
        required=true
    >
        <Option value="active">ACTIVE</Option>
        <Option value="inactive">INACTIVE</Option>
    </Select>

	<!-- PRIVACY //-->

    <Select bind:value={data.private}
        style="width: 100%;"
        label="GROUP PRIVACY"
        required=true
    >
        <Option value="no">PUBLIC GROUP</Option>
        <Option value="yes">PRIVATE GROUP</Option>
    </Select>

<p style="text-align: center;">
        <Button on:click={() => { updateRecord(); }} variant="raised">
            <ButtonIcon class="material-icons">save</ButtonIcon>
            <ButtonLabel>CREATE NEW GROUP</ButtonLabel>
        </Button>
</p>

	</Paper>

{/await}

	{/if}
{:else}
	<AccessDenied />
{/if}
