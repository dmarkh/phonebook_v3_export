<script>

import {meta, router, Route} from 'tinro';

import Autocomplete from '@smui-extra/autocomplete';
import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import Select, { Option } from '@smui/select';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';
import CharacterCounter from '@smui/textfield/character-counter';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';
import PleaseWait from './PleaseWait.svelte';

import { downloadMember } from '../utils/pnb-download.js';

import { taskAssign, taskAssignedMember, taskUnassign } from '../utils/pnb-api.js';
import { getTask, getTasks, getGroups } from '../utils/pnb-api.js';
import { convertTasks } from '../utils/pnb-convert.js';
import { auth, screen } from '../store.js';

import { sleep } from '../utils/sleep.js';

let pleaseWait = false;

const mid = parseInt(window.pnb.mid);
let member = false;
let title = '', subtitle = '';
let member_fields = { 'EMAIL': 'email', 'ALT EMAIL': 'email_alt', 'ORCID': 'orcid_id' };
let inst_fields = { 'INSTITUTION': 'name_full', 'INSTITUTION ROR ID': 'ror_id', 'COUNTRY': 'country', 'REGION': 'region', 'WEBSITE': 'website_institution' };

let groups = false;
let tasks = false;
let ttasks = false;
let task_cache = {};

let new_task_value = false;
let new_group_value = false;
let new_fte_value = 0;
let new_begin_time = (new Date().getFullYear()) + '-01-01',
	new_end_time = ( new Date().getFullYear() ) + '-12-31';

const assignTask = async () => {

    if ( !new_task_value || !new_group_value ) { return; }

    pleaseWait = 'ASSIGNING A TASK, PLEASE WAIT';

    let rc = await taskAssign({ task_id: new_task_value.id, group_id: new_group_value.id, member_id: mid, fte: new_fte_value, begin_time: new_begin_time, end_time: new_end_time });

    await sleep(1000);
    $screen = 'my-overview';
    if ( rc ) {
        router.goto('/my-overview/tasks');
    }

    pleaseWait = false;
}

const unassignTask = async ( id ) => {

    pleaseWait = 'UNASSIGNING THE TASK, PLEASE WAIT';

    let rc = await taskUnassign( id );
    await sleep(1000);
    $screen = 'my-overview';
    if ( rc ) {
        router.goto('/my-overview/tasks');
    }

    pleaseWait = false;
}

const findTask = ( id ) => {
    if ( task_cache[id] ) { return task_cache[id]; }
    for ( const task of tasks ) {
        if ( task.id == id ) {
            task_cache[id] = task;
            return task;
        }
    }
    return false;
}

const findTTask = ( id ) => {
    const task = ttasks.find( t => t.task_id == id );
    return task ? task : '';
}

const findGroup = ( id ) => {
    const $grp = groups.find( g => g.id == id );
    return $grp ? $grp : false;
}

const getMemberInfo = async () => {
	member = await downloadMember( mid );
	title = member.cmember.name_first + ' ' + member.cmember.name_last;
	subtitle = member.cinstitution.name_full;

	groups = await getGroups();
	tasks  = await getTasks();
	tasks = await convertTasks(tasks);

	ttasks = await taskAssignedMember( mid );

	return member;
}

const getDates = ( v ) => {
    if ( !v.begin_time && !v.end_time ) {
        return ''; // not set
    } else if ( v.begin_time && v.end_time ) {
        return ( v.begin_time + ' - ' + v.end_time );
    } else if ( v.begin_time && !v.end_time ) {
        return ( v.begin_time + ' AND BEYOND' );
    } else if ( !v.begin_time && v.end_time ) {
        return ( 'UP TO ' + v.end_time);
    }
    return 'ERROR';
}

</script>

{#if !mid}
    <div style="text-align: center; padding: 5vmin;">
        MEMBER NOT FOUND BY ORCID LOOKUP. PLEASE ASK YOUR REPRESENTATIVE TO UPDATE THE RECORD.
    </div>
{:else}

    {#if pleaseWait}

        <PleaseWait text="{pleaseWait}" />

    {:else}

	{#await getMemberInfo()}
		<LinearProgress indeterminate />
	{:then data}

    <div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
    {#if subtitle}
        <div style="text-align: center;" class="mdc-typography--subtitle1">{@html subtitle}</div>
    {/if}
	<Paper>

{#if $auth['grants']['own-task-create']}

<div style="width: 100%;">

    <Autocomplete
        options={tasks}
        bind:value={new_task_value}
        getOptionLabel={(option) =>
            option ? `${option.title}` : ''}
        label="SELECT TASK"
        class="inst-autocomplete"
    />

    <Autocomplete
        options={groups}
        bind:value={new_group_value}
        getOptionLabel={(option) =>
            option ? `${option.name}` : ''}
        label="SELECT GROUP"
        class="inst-autocomplete"
    />

        <Textfield
            bind:value={ new_fte_value }
            label="FTE"
            type="number"
			input$step="0.1"
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
        <Button on:click={() => { assignTask(); }} variant="raised">
            <ButtonIcon class="material-icons">save</ButtonIcon>
            <ButtonLabel>ASSIGN TASK</ButtonLabel>
        </Button>
    </p>
</div>
{/if}

{#if $auth['grants']['own-task-view']}

{#if ttasks.length}
<div style="text-align: center;" class="mdc-typography--headline4">TASKS</div>
<DataTable table$aria-label="Member Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="value" style="text-align: center;">
                <Label>DATES</Label>
            </Cell>
            <Cell columnId="value" style="text-align: center;">
                <Label>TASK</Label>
            </Cell>
            <Cell columnId="value" style="text-align: center;">
                <Label>GROUP</Label>
            </Cell>
            <Cell columnId="value" style="text-align: center;">
                <Label>FTE</Label>
            </Cell>
            <Cell columnId="operation" style="text-align: center;">
                <Label>OPERATION</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each ttasks as task ( task.id )}
      <Row data-entry-id="member">
        <Cell style="text-align: center;">{ getDates( task ) }</Cell>
        <Cell style="text-align: center;">{ findTask( task.task_id ).title || '' }</Cell>
        <Cell style="text-align: center;">{ findGroup( task.group_id ).name || '' }</Cell>
        <Cell style="text-align: center;">{ task.fte || 0}</Cell>
        <Cell style="text-align: center;">
{#if $auth['grants']['own-task-edit']}
        <Button on:click={() => { router.goto('/my-task-edit/' + task.id); }} variant="raised">
            <ButtonIcon class="material-icons">edit</ButtonIcon>
            <ButtonLabel>EDIT</ButtonLabel>
        </Button>
        <Button on:click={() => { unassignTask( task.id ); }} variant="raised">
            <ButtonIcon class="material-icons">delete_forever</ButtonIcon>
            <ButtonLabel>DELETE</ButtonLabel>
        </Button>
{/if}
        </Cell>
      </Row>
    {/each}
    </Body>
</DataTable>
{/if}

{/if}

	</Paper>

	{/await}

{/if}

{/if}

<style>
</style>