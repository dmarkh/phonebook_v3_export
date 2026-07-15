<script>

import {meta, router, Route} from 'tinro';

import Autocomplete from '@smui-extra/autocomplete';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import AccessDenied from './AccessDenied.svelte';
import PleaseWait from './PleaseWait.svelte';
import LinearProgress from '@smui/linear-progress';

import Paper from '@smui/paper';

import Select, { Option } from '@smui/select';
import IconButton from '@smui/icon-button';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';
import CharacterCounter from '@smui/textfield/character-counter';
import Dialog, { Title as DialogTitle, Content as DialogContent, Actions as DialogActions, InitialFocus as DialogInitialFocus } from '@smui/dialog';

import { getTask, getTaskInstitutions, getInstitutions, taskAddInstitution, taskRemoveInstitution } from '../utils/pnb-api.js';
import { convertInstitutions } from '../utils/pnb-convert.js';
import { sortConvertedInstitutions } from '../utils/pnb-sort.js';
import { downloadTask } from '../utils/pnb-download.js';

import { task_id, auth, screen } from '../store.js';
import { sleep } from '../utils/sleep.js';

let title = '';
let data = {};
let groups = false;
let institutions = false;
let tinstitutions = false;

let group_cache = {};
let institution_cache = {};
let new_institution = 0;

let new_institution_value = false;
let new_fte_value = 0;
let new_begin_time = null, new_end_time = null;

let pleaseWait = false;

const addNewInstitution = async () => {

	if ( !new_institution_value ) { return; }

	for( const institution of data.institutions ) {
		if ( institution.institution_id == new_institution_value.id ) {
			alert('ERROR: INSTITUTION ALREADY ADDED');
			return;
		}
	}

    pleaseWait = 'ADDING AN INSTITUTION TO THE TASK, PLEASE WAIT';

    let rc = await taskAddInstitution({ task_id: $task_id, institution_id: new_institution_value.id, fte: new_fte_value, begin_time: new_begin_time, end_time: new_end_time });
    await sleep(1000);
    $screen = 'tasks';
    if ( rc ) {
        router.goto('/task/' + $task_id + '/view' );
    } else {
        console.log('ERROR: task has not been updated');
    }

    pleaseWait = false;
}

const removeInstitutionFromTask = async ( institution_id ) => {
    pleaseWait = 'REMOVING AN INSTITUTION FROM THE TASK, PLEASE WAIT';

    let rc = await taskRemoveInstitution({ task_id: $task_id, institution_id });
    await sleep(1000);
    $screen = 'tasks';
    if ( rc ) {
        router.goto('/task/' + $task_id + '/view' );
    } else {
        console.log('ERROR: task has not been updated');
    }

    pleaseWait = false;
}

const findInstitution = ( id ) => {
	for ( const institution of institutions ) {
		if ( institution.id == id ) {
			return institution;
		}
	}
	return '';
}

const fetchTask = async () => {
    const dtask = await downloadTask( $task_id );
    data = dtask.task;
    title = dtask.ctask.title;

	institutions = await getInstitutions();
    institutions = await convertInstitutions( institutions );
    sortConvertedInstitutions( institutions );
	tinstitutions = await getTaskInstitutions( $task_id );
	data.institutions = tinstitutions;
	return data;
}

const getInstitutionDates = ( inst ) => {
    if ( !inst.begin_time && !inst.end_time ) {
        return ''; // not set
    } else if ( inst.begin_time && inst.end_time ) {
        return ( inst.begin_time + ' - ' + inst.end_time );
    } else if ( inst.begin_time && !inst.end_time ) {
        return ( inst.begin_time + ' AND BEYOND' );
    } else if ( !inst.begin_time && inst.end_time ) {
        return ( 'UP TO ' + inst.end_time);
    }
    console.log('begin_time: ' + inst.begin_time + ', end_time: ' + inst.end_time );
    return 'ERROR';
}

</script>

    {#if pleaseWait}

        <PleaseWait text="{pleaseWait}" />

    {:else}

{#await fetchTask()}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">TASK: {title}</div>
<Paper>

<!-- ADD NEW INSTITUTION //-->

<div style="width: 100%;">

	<Autocomplete
    	options={institutions}
    	bind:value={new_institution_value}
		getOptionLabel={(option) =>
			option ? `${option.name_full}` : ''}
    	label="SELECT NEW INSTITUTION"
		class="inst-autocomplete"
	/>

        <Textfield
            bind:value={ new_fte_value }
            label="FTE"
            type="number"
        >
          <svelte:fragment slot="helper">
            <HelperText>Amount of FTE (fractional)</HelperText>
          </svelte:fragment>
        </Textfield>

        <Textfield bind:value={ new_begin_time }
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="BEGIN TIME"
            type="date"
        >
          <svelte:fragment slot="helper">
            <HelperText>OPTIONAL: BEGIN TIME OF THE APPOINTMENT</HelperText>
          </svelte:fragment>
        </Textfield>

        <Textfield bind:value={ new_end_time }
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="END TIME"
            type="date"
        >
          <svelte:fragment slot="helper">
            <HelperText>OPTIONAL: END TIME OF THE APPOINTMENT</HelperText>
          </svelte:fragment>
        </Textfield>

	<p>
        <Button on:click={() => { addNewInstitution(); }} variant="raised">
            <ButtonIcon class="material-icons">save</ButtonIcon>
            <ButtonLabel>ADD NEW INSTITUTION</ButtonLabel>
        </Button>
	</p>
</div>

<!-- LIST OF TASK INSTITUTIONS //-->

{#if tinstitutions.length}
<div style="text-align: center;" class="mdc-typography--headline4">INSTITUTIONS</div>
<DataTable table$aria-label="Institution Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="value" style="text-align: center;">
                <Label>DATES</Label>
            </Cell>
            <Cell columnId="fte" style="text-align: center;">
                <Label>FTE</Label>
            </Cell>
            <Cell columnId="institution" style="text-align: center;">
                <Label>INSTITUTION</Label>
            </Cell>
            <Cell columnId="operation" style="text-align: center;">
                <Label>OPERATION</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
	{#each tinstitutions as institution ( institution.institution_id )}
      <Row data-entry-id="institution">
	<Cell style="text-align: center;">{ getInstitutionDates( institution ) }</Cell>
       	<Cell style="text-align: center;">{institution.fte}</Cell>
       	<Cell style="text-align: center;">{findInstitution( institution.institution_id ).name_full}</Cell>
       	<Cell style="text-align: center;">
        <Button on:click={() => { removeInstitutionFromTask( institution.institution_id ); }} variant="raised">
            <ButtonIcon class="material-icons">table</ButtonIcon>
            <ButtonLabel>REMOVE</ButtonLabel>
        </Button>
		</Cell>
   	  </Row>
	{/each}
    </Body>
</DataTable>
{/if}

</Paper>

{/await}

{/if}

<style>

</style>