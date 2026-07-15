<script>

import { router, Route} from 'tinro';

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
import AccessDenied from './AccessDenied.svelte';

import { downloadMember } from '../utils/pnb-download.js';

import { taskAssign, taskAssignedGet, taskAssignedUpdate, taskUnassign } from '../utils/pnb-api.js';
import { getTask, getTasks, getGroups } from '../utils/pnb-api.js';
import { convertTasks, convertTask } from '../utils/pnb-convert.js';
import { auth, screen } from '../store.js';

import { sleep } from '../utils/sleep.js';

export let meta;
let task_id = false;

if ( meta.params.id ) {
    task_id = meta.params.id;
}

let pleaseWait = false;

const mid = parseInt(window.pnb.mid);
let member = false;
let title = '', subtitle = '';

let assignedtask = false;
let groups = false;
let tasks = false;
let ttasks = false;
let task_cache = {};

let new_member_value = false;
let new_task_value = false;
let new_group_value = false;
let new_fte_value = 0;
let new_begin_time = false,
	new_end_time = false;

const updateTask = async () => {

    if ( !task_id || !new_task_value || !new_group_value ) { return; }

    pleaseWait = 'UPDATING A TASK, PLEASE WAIT';

	const data = { id: task_id, task_id: new_task_value.id, group_id: new_group_value.id, member_id: new_member_value,
		fte: new_fte_value, begin_time: new_begin_time, end_time: new_end_time };
    let rc = await taskAssignedUpdate(data);

    await sleep(1000);
    $screen = 'members';
    if ( rc ) {
		router.goto( $router.from );
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
	groups = await getGroups();
	tasks  = await getTasks();
	tasks = await convertTasks(tasks);

	assignedtask = await taskAssignedGet( task_id );
	if ( assignedtask ) {
		new_member_value = assignedtask.member_id;
		member = await downloadMember( assignedtask.member_id );
		title = member.cmember.name_first + ' ' + member.cmember.name_last;
		subtitle = member.cinstitution.name_full;
		new_task_value = tasks.find( t => t.id == assignedtask.task_id );
		new_group_value = groups.find( g => g.id == assignedtask.group_id );
		new_fte_value = parseFloat(assignedtask.fte);
		new_begin_time = assignedtask.begin_time;
		new_end_time = assignedtask.end_time;
    }

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

{#if !$auth['grants']['member-tasks-edit']}
    <AccessDenied />
{:else}

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
        <Button on:click={() => { updateTask(); }} variant="raised">
            <ButtonIcon class="material-icons">save</ButtonIcon>
            <ButtonLabel>UPDATE TASK</ButtonLabel>
        </Button>

        <Button on:click={() => { history.back(); }} variant="raised">
            <ButtonIcon class="material-icons">undo</ButtonIcon>
            <ButtonLabel>BACK</ButtonLabel>
        </Button>
    </p>
</div>

	</Paper>

	{/await}

{/if}

{/if}

{/if}

<style>
</style>