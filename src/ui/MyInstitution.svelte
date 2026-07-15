<script>

import { writable } from 'svelte/store';
import { onDestroy } from 'svelte';

import {router, Route} from 'tinro';
import { auth, screen } from '../store.js';
import { tran } from '../utils/tran.js';

import Autocomplete from '@smui-extra/autocomplete';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';
import Fab, { Icon as FabIcon } from '@smui/fab';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import PleaseWait from './PleaseWait.svelte';

import Textfield from '@smui/textfield';
import Icon from '@smui/textfield/icon';
import HelperText from '@smui/textfield/helper-text';

import { getInstitution, getInstitutionFields, getInstitutionFieldgroups } from '../utils/pnb-api.js';
import { convertInstitution } from '../utils/pnb-convert.js';

import { getTasks, getGroups, getMembers, getMemberFields, taskAssigned, taskAssign, taskUnassign } from '../utils/pnb-api.js';
import { convertMembers, convertTasks, addInstitutionsToConvertedMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';

import { updateMember } from '../utils/pnb-api.js';
import { downloadMember } from '../utils/pnb-download.js';
import { toggleMember, createMember } from '../utils/pnb-api.js';

const mid = parseInt(window.pnb.mid);
let member = false;

let members, groups, tasks, ttasks = [];

let new_member = false;
let new_member_fields = {};

let new_task = false;
let new_task_member = false,
	new_task_group = false,
	new_task_task = false,
	new_task_fte = 0,
	new_task_begintime = (new Date().getFullYear()) + '-01-01',
    new_task_endtime = (new Date().getFullYear()) + '-12-31';

let representative = false;
let title = '', subtitle = '';

let inst_members = [];

let member_cache = {};
let task_cache = {};
let group_cache = {};

let refresh = false;

let edit_member_fields = window.pnb['self-member-fields'] || {};

let filtered_members = [];
let filtered_tasks = [];

let quickSearch = writable('');
let quickSearchTask = writable('');

let sort = 'name';
let sortDirection = 'ascending';

let tasksort = 'member';
let tasksortDirection = 'ascending';

let rowsPerPage = 10;
let currentPage = 0;

let taskrowsPerPage = 10;
let taskcurrentPage = 0;

let pleaseWait = false;

$: start = currentPage * rowsPerPage;
$: end = Math.min(start + rowsPerPage, filtered_members.length);
$: slice = filtered_members.slice(start, end);
$: lastPage = Math.max(Math.ceil( filtered_members.length / rowsPerPage) - 1, 0);
$: if (currentPage > lastPage) {
    currentPage = lastPage;
}

$: taskstart = taskcurrentPage * taskrowsPerPage;
$: taskend = Math.min(taskstart + taskrowsPerPage, filtered_tasks.length);
$: taskslice = filtered_tasks.slice(taskstart, taskend);
$: tasklastPage = Math.max(Math.ceil( filtered_tasks.length / taskrowsPerPage) - 1, 0);
$: if ( taskcurrentPage > tasklastPage ) {
    taskcurrentPage = tasklastPage;
}

let edit_member = false;
let edit_member_field = false;
let edit_member_field_value = false;

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
    console.log('begin_time: ' + v.begin_time + ', end_time: ' + v.end_time );
    return 'ERROR';
}

const handleSort = () => {
    filtered_members = ( filterItemsQuick() ).sort((a, b) => {
        const [aVal, bVal] = [a[sort], b[sort]][
            sortDirection === 'ascending' ? 'slice' : 'reverse'
        ]();
        if (typeof aVal === 'string' && typeof bVal !== 'string') {
            return aVal.localeCompare(String(''));
        } else if (typeof aVal !== 'string' && typeof bVal === 'string') {
            return String('').localeCompare(bVal);
        } else if (typeof aVal === 'string' && typeof bVal === 'string') {
            return aVal.localeCompare(bVal);
        }
        return Number(aVal) - Number(bVal);
    });
}

const handleTaskSort = () => {
    filtered_tasks = ( filterTasksQuick() ).sort((a, b) => {
        const [aVal, bVal] = [a[tasksort], b[tasksort]][
            tasksortDirection === 'ascending' ? 'slice' : 'reverse'
        ]();
        if (typeof aVal === 'string' && typeof bVal !== 'string') {
            return aVal.localeCompare(String(''));
        } else if (typeof aVal !== 'string' && typeof bVal === 'string') {
            return String('').localeCompare(bVal);
        } else if (typeof aVal === 'string' && typeof bVal === 'string') {
            return aVal.localeCompare(bVal);
        }
        return Number(aVal) - Number(bVal);
    });
}

const handleRowClick = ( e ) => {
    const member_id = e.target.dataset.entryId;
}

const filterItemsQuick = () => {
    if ( $quickSearch ) {
        filtered_members = inst_members.filter( item => {
            for ( const val of window.pnb.members ) {
                if ( String(item[ val.field ]).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            }
            return false;
        });
    } else {
        filtered_members = inst_members.slice();
        return filtered_members;
    }
    return filtered_members;
}

const filterTasksQuick = () => {
    if ( $quickSearchTask ) {
        filtered_tasks = ttasks.filter( item => {
            for ( const val of ['dates','member','task','group','fte'] ) {
                if ( String(item[ val ]).toLowerCase().includes( $quickSearchTask.toLowerCase() ) ) { return true; }
            }
            return false;
        });
    } else {
        filtered_tasks = ttasks.slice();
        return filtered_tasks;
    }
    return filtered_tasks;
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

const findMember = ( id ) => {
    if ( member_cache[id] ) { return ( member_cache[id].name_last + ', ' + member_cache[id].name_first ); }
    for ( const member of members ) {
        if ( member.id == id ) {
            member_cache[id] = member;
            return ( member.name_last + ', ' + member.name_first );
        }
    }
    return 'UNKNOWN/DEACTIVATED';
}

const findTTask = ( id ) => {
    for ( const task of ttasks ) {
        if ( task.task_id == id ) {
            return task;
        }
    }
    return '';
}

const findGroup = ( id ) => {
    if ( group_cache[id] ) { return group_cache[id]; }
    for ( const group of groups ) {
        if ( group.id == id ) {
            group_cache[id] = group;
            return group;
        }
    }
    return false;
}


const unassignTask = async ( id ) => {
    pleaseWait = 'UNASSIGNING TASK, PLEASE WAIT';

    let rc = await taskUnassign( id );
    await sleep(1000);

    pleaseWait = false;
}

const getMemberInfo = async () => {
	member = await downloadMember( mid );
	title = member.cinstitution.name_full;
	subtitle = member.cmember.name_first + ' ' + member.cmember.name_last;
	if ( member.cmember.member_role && member.cmember.member_role.toLowerCase() !== 'member' ) {
		representative = true;
	} else if ( member.cinstitution.council_representative && mid == member.cinstitution.council_representative ) {
		representative = true;
	}
	return member;
}

const downloadInstitution = async ( id ) => {
    let data = [];
    let idata = await getInstitution( id );
    let ifields = await getInstitutionFields();
    let igroups = await getInstitutionFieldgroups();
    let cidata = await convertInstitution( idata );
    title = cidata.name_full;
    subtitle = cidata.country || 'COUNTRY NOT SET';

    let mem = await getMembers();
    let items = await convertMembers( mem );
    items = items.filter( m => m.institution_id == member.cinstitution.id );
    sortConvertedMembers( items );

	inst_members = items;
	filtered_members = inst_members.slice();
    return items;
}

const downloadTasks = async ( id ) => {

    groups = await getGroups();

    tasks = await getTasks();
    tasks = await convertTasks( tasks );

    let mem = await getMembers();
    members = await convertMembers( mem );
    sortConvertedMembers( members );
    await addInstitutionsToConvertedMembers( members );
    members = members.filter( m => m.institution__id == id );

    let member_ids = members.map( m => m.id );

	ttasks = [];
    let atasks = await taskAssigned();
    atasks = atasks.filter( t => member_ids.includes( parseInt(t.member_id) ) );
    atasks.forEach( task => {
        ttasks.push({
            id: task.id,
            dates: getDates( task ),
            task: ( findTask( task.task_id ).title || '' ),
            member: findMember( task.member_id ),
            group: ( findGroup( task.group_id ).name || '' ),
            fte: ( task.fte || 0 )
        });
    });

	filtered_tasks = ttasks.slice();

	return ttasks;
}

const unsubscribe_quickSearch = quickSearch.subscribe( v => {
	if ( v ) {
		filtered_members = filterItemsQuick();
	} else {
		filtered_members = inst_members.slice();
	}
});

const unsubscribe_quickSearchTask = quickSearchTask.subscribe( v => {
	if ( v ) {
		filtered_tasks = filterTasksQuick();
	} else {
		filtered_tasks = ttasks.slice();
	}
});

const cb_edit_member = async ( id ) => {
	edit_member = await downloadMember( id );
}

const cb_disable_member = async ( id ) => {
	await toggleMember( id );
	edit_member = false;
	edit_member_field = false;
	refresh = !refresh;
}

const cb_add_new_member = () => {
	new_member_fields = {};
	for ( const [k,v] of Object.entries( window.pnb['self-member-fields'] || {} ) ) {
		new_member_fields[v] = '';
	}
	new_member = true;
}

const cb_add_new_task = () => {
	new_task_member = false;
	new_task_group = false;
	new_task_task = false;
	new_task_fte = 0;
	new_task_begintime = (new Date().getFullYear()) + '-01-01';
    new_task_endtime = (new Date().getFullYear()) + '-12-31';
	new_task = true;
}

const cb_submit_new_member = async () => {
	if ( !new_member_fields['name_first'] || !new_member_fields['name_last'] ) {
		return alert('please fill in requred fields');
	}
	const iid = Object.values( member.member_fields ).find( m => m.name_fixed == 'institution_id' ).id;
    let data = {
        "status": "active",
        "fields": {
			[iid]: member.cmember.institution_id
		}
    };
	for ( const [k,v] of Object.entries(new_member_fields) ) {
		if ( !v || !v.length ) { continue; }
		const fid = Object.values( member.member_fields ).find( m => m.name_fixed == k ).id;
		if ( !fid ) { return false; }
		data.fields[ fid ] = v;
	}

    await createMember( data );

	new_member = false;
	refresh = !refresh;
}

const cb_submit_new_task = async () => {

    let rc = await taskAssign({
		task_id: new_task_task.id,
		member_id: new_task_member.id,
		group_id: new_task_group.id,
        fte: new_task_fte,
		begin_time: new_task_begintime,
		end_time: new_task_endtime
	});

	new_task = false;
	refresh = !refresh;
}

const set_edit_member_field = ( v ) => {
	if ( !edit_member ) { return; }
    edit_member_field_value = edit_member.cmember[v] || '';
    edit_member_field = v;
}

const save_edited_member_field = async () => {
    const field = Object.values( edit_member.member_fields ).find( o => o.name_fixed == edit_member_field );
    if ( !field ) { console.log('ERROR: unknown field ' + edit_member_field ); return; } // ERROR
    const field_id = field.id;
    const data = { [edit_member.cmember.id]: { [field_id]: edit_member_field_value.trim() } };
    await updateMember( data );
	edit_member = await downloadMember( edit_member.cmember.id );
    edit_member_field = false;
    edit_member_field_value = false;
	refresh = !refresh;
}

const back_to_the_institution = () => {
	new_task = false;
	new_task_member = false;
    new_task_group = false;
    new_task_task = false;
    new_task_fte = 0;
	new_task_begintime = (new Date().getFullYear()) + '-01-01',
    new_task_endtime = (new Date().getFullYear()) + '-12-31';
	new_member = false;
	new_member_fields = {};
	edit_member = false;
	edit_member_field = false;
	edit_member_field_value = false;
	refresh = !refresh;
}

onDestroy(() => {
    unsubscribe_quickSearch();
    unsubscribe_quickSearchTask();
});

</script>

{#key refresh}

{#if !mid}
    <div style="text-align: center; padding: 5vmin;">
        MEMBER NOT FOUND BY ORCID LOOKUP. PLEASE ASK YOUR REPRESENTATIVE (OR THE PHONEBOOK ADMIN) TO UPDATE YOUR RECORD!
    </div>
{:else}


	{#await getMemberInfo()}
		<LinearProgress indeterminate />
	{:then data}

    {#if pleaseWait}

        <PleaseWait text="{pleaseWait}" />

    {:else}

    <div style="text-align: center; cursor: pointer;" class="mdc-typography--headline4" on:click={() => { router.goto('/institution/' + member.cinstitution.id + '/view'); $screen = 'institutions'; }}>{title}</div>
    {#if subtitle}
        <div style="text-align: center;" class="mdc-typography--subtitle1">{@html subtitle}</div>
    {/if}
	<Paper>

	{#await downloadInstitution( member.cinstitution.id )}

		<LinearProgress indeterminate />

	{:then inst_member_data}

	{#if new_member}
		<div style="text-align: center;" class="mdc-typography--headline6">
			ADDING NEW MEMBER
		</div>
        <DataTable table$aria-label="Member Data" style="width: 100%;">
            <Body>
            {#each Object.entries(edit_member_fields) as [k,v]}
                    <Row>
					<Cell style="width: 70%; text-align: left;">
					{#if ['name_first','name_last'].includes(v)}
                        <Textfield bind:value={ new_member_fields[v] } required
                            style="width: 100%;"
                            helperLine$style="width: 100%;"
                            label="{k}"
                        >
                        </Textfield>
					{:else}
                        <Textfield bind:value={ new_member_fields[v] }
                            style="width: 100%;"
                            helperLine$style="width: 100%;"
                            label="{k}"
                        >
                        </Textfield>
					{/if}
					</Cell>
					</Row>
			{/each}
            </Body>
        </DataTable>

		<br /><br />
		<div style="width: 100%; text-align: center;">
		<Button color="primary" style="width: 50vmin;" on:click={cb_submit_new_member} variant="raised">
			<Icon class="material-icons">person</Icon>
			<ButtonLabel>SUBMIT MEMBER DATA</ButtonLabel>
		</Button>
		<br /><br />
		<Button color="secondary" style="width: 50vmin;" on:click={back_to_the_institution} variant="raised">
			<Icon class="material-icons">person_off</Icon>
			<ButtonLabel>DISCARD AND RETURN</ButtonLabel>
		</Button>
		</div>


	{:else if edit_member}

		<div style="text-align: center;" class="mdc-typography--headline6">
			EDITING MEMBER: {edit_member.cmember.name_first} {edit_member.cmember.name_last}
		</div>

        <DataTable table$aria-label="Member Data" style="width: 100%;">
            <Body>
            {#each Object.entries(edit_member_fields) as [k,v]}
                    <Row>
                    <Cell style="width: 25%; text-align: left;">{k}
					{#if ['name_first','name_last'].includes(v)}
						<span style="color: red">*</span>
					{/if}
					</Cell>
					<Cell style="width: 70%; text-align: left;">
                    {#if edit_member_field == v}
                        <Textfield bind:value={ edit_member_field_value }
                            style="width: 100%;"
                            helperLine$style="width: 100%;"
                            label="{k}"
                        >
                        <svelte:fragment slot="helper">
                            <HelperText>{k}</HelperText>
                        </svelte:fragment>
                        </Textfield>
                    {:else}
                    	{edit_member.cmember[v] || ''}
                    {/if}
					</Cell>
                    <Cell style="width:  5%; text-align: right;">
                    {#if edit_member_field == v}
                            <IconButton class="material-icons" on:click={()=>{ save_edited_member_field(); }}>save</IconButton>
                    {:else}
                        {#if window.pnb['representative-edit'].includes( v )}
                            <IconButton class="material-icons" on:click={()=>{ set_edit_member_field(v); }}>edit</IconButton>
                        {/if}
                    {/if}
                    </Cell>
                    </Row>
            {/each}
            </Body>
        </DataTable>

		<br /><br />
		<div style="width: 100%; text-align: center;">
		<Button color="primary" style="width: 50vmin;" on:click={back_to_the_institution} variant="raised">
			<Icon class="material-icons">arrow_back</Icon>
		  <ButtonLabel>BACK TO THE INSTITUTION</ButtonLabel>
		</Button>
		<br /><br />
		<Button color="secondary" style="width: 50vmin;" on:click={() => { cb_disable_member(edit_member.cmember.id) }} variant="raised">
			<Icon class="material-icons">person_off</Icon>
		  <ButtonLabel>DEACTIVATE MEMBER</ButtonLabel>
		</Button>
		</div>

	{:else}

		<div style="text-align: center;" class="mdc-typography--headline6">INSTITUTION MEMBERS</div>
		{#if representative}
	        <div style="text-align: center;" class="mdc-typography--subtitle1">ACCESS: {member.cmember.member_role}</div>
		{/if}

    <div style="width: 100%; margin-top: 2vmin; position: relative;">
	    <div style="position: relative; float: left;">
			<Textfield variant="outlined" bind:value={$quickSearch} label="Search Members">
	    		<Icon class="material-icons" slot="trailingIcon">search</Icon>
	    		<HelperText slot="helper">search by matching substring</HelperText>
			</Textfield>
    	</div>

		{#if representative}
	    <div style="position: relative; float: right;">
			<Button color="primary" on:click={cb_add_new_member} variant="raised">
				<Icon class="material-icons">add</Icon>
				<ButtonLabel>ADD NEW MEMBER</ButtonLabel>
			</Button>
		</div>
		{/if}

	</div>

	<DataTable table$aria-label="Member List" style="width: 100%;"
	  sortable
	  bind:sort
	  bind:sortDirection
	  on:SMUIDataTable:sorted={handleSort}
	>
    <Head>
        <Row>
            <Cell columnId="member_role" style="width: 10%; text-align: center;">
                <Label>MEMBER ROLE</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="name_first" style="width: 20%; text-align: right;">
                <Label>FIRST NAME</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="name_last" style="width: 20%; text-align: left;">
                <Label>LAST NAME</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="email" style="width: 20%; text-align: center;">
                <Label>EMAIL</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="orcid_id" style="width: 20%; text-align: center;">
                <Label>ORCID</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="actions" style="text-align: center;">
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each slice as item (item.id)}
      <Row data-entry-id="{item.id}">
        <Cell style="width: 15%; text-align: center;">{item.member_role || ''}</Cell>
        <Cell style="width: 15%; text-align: right;">{item.name_first || ''}</Cell>
        <Cell style="width: 15%; text-align: left;">{item.name_last || ''}</Cell>
        <Cell style="width: 15%; text-align: center;">{item.email || ''}</Cell>
        <Cell style="width: 15%; text-align: center;">{item.orcid_id || ''}</Cell>
        <Cell style="text-align: center;">
			{#if representative}
				<Fab mini>
					<FabIcon class="material-icons" onclick={() => { cb_edit_member(item.id); }}>edit</FabIcon>
				</Fab>
			{/if}
		</Cell>
      </Row>
    {/each}
    </Body>
  <Pagination slot="paginate">
    <svelte:fragment slot="rowsPerPage">
      <Label>Rows Per Page</Label>
      <Select variant="outlined" bind:value={rowsPerPage} noLabel>
        <Option value={10}>10</Option>
        <Option value={25}>25</Option>
        <Option value={100}>100</Option>
      </Select>
    </svelte:fragment>
    <svelte:fragment slot="total">
      {start + 1}-{end} of {filtered_members.length}
    </svelte:fragment>

    <IconButton
      class="material-icons"
      action="first-page"
      title="First page"
      on:click={() => (currentPage = 0)}
      disabled={currentPage === 0}>first_page</IconButton
    >
    <IconButton
      class="material-icons"
      action="prev-page"
      title="Prev page"
      on:click={() => currentPage--}
      disabled={currentPage === 0}>chevron_left</IconButton
    >
    <IconButton
      class="material-icons"
      action="next-page"
      title="Next page"
      on:click={() => currentPage++}
      disabled={currentPage === lastPage}>chevron_right</IconButton
    >
    <IconButton
      class="material-icons"
      action="last-page"
      title="Last page"
      on:click={() => (currentPage = lastPage)}
      disabled={currentPage === lastPage}>last_page</IconButton
    >
  </Pagination>
	</DataTable>
	{/if}


	{#await downloadTasks(member.cinstitution.id)}
		<p>Loading tasks...</p>
	{:then data}

	{#if new_task}
		<div style="text-align: center;" class="mdc-typography--headline6">
			ASSIGNING NEW TASK
		</div>

	<div style="width: 100%;">

    <Autocomplete
        options={members}
        bind:value={new_task_member}
        getOptionLabel={(option) =>
            option ? `${option.name_last}, ${option.name_first}` : ''}
        label="SELECT MEMBER"
        class="inst-autocomplete"
    />


    <Autocomplete
        options={tasks}
        bind:value={new_task_task}
        getOptionLabel={(option) =>
            option ? `${option.title}` : ''}
        label="SELECT TASK"
        class="inst-autocomplete"
    />

    <Autocomplete
        options={groups}
        bind:value={new_task_group}
        getOptionLabel={(option) =>
            option ? `${option.name}` : ''}
        label="SELECT GROUP"
        class="inst-autocomplete"
    />

        <Textfield
            bind:value={new_task_fte}
            label="FTE"
            type="number"
            input$step="0.1"
        >
          <svelte:fragment slot="helper">
            <HelperText>Amount of FTE (fractional)</HelperText>
          </svelte:fragment>
        </Textfield>

        <Textfield bind:value={new_task_begintime}
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="BEGIN TIME"
            type="date"
        >
          <svelte:fragment slot="helper">
            <HelperText>OPTIONAL: BEGIN TIME OF THE APPOINTMENT</HelperText>
          </svelte:fragment>
        </Textfield>

        <Textfield bind:value={new_task_endtime}
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="END TIME"
            type="date"
        >
          <svelte:fragment slot="helper">
            <HelperText>OPTIONAL: END TIME OF THE APPOINTMENT</HelperText>
          </svelte:fragment>
        </Textfield>

		</div>

		<br /><br />
		<div style="width: 100%; text-align: center;">
		<Button color="primary" style="width: 50vmin;" on:click={cb_submit_new_task} variant="raised">
			<Icon class="material-icons">person</Icon>
			<ButtonLabel>SUBMIT TASK DATA</ButtonLabel>
		</Button>
		<br /><br />
		<Button color="secondary" style="width: 50vmin;" on:click={back_to_the_institution} variant="raised">
			<Icon class="material-icons">person_off</Icon>
			<ButtonLabel>DISCARD AND RETURN</ButtonLabel>
		</Button>
		</div>

		{:else}

		<div style="text-align: center; margin-top: 2vmin;" class="mdc-typography--headline6">INSTITUTION TASKS</div>
        {#if representative}
            <div style="text-align: center;" class="mdc-typography--subtitle1">ACCESS: {member.cmember.member_role}</div>
        {/if}

    <div style="width: 100%; margin-top: 2vmin; position: relative;">
	    <div style="position: relative; float: left;">
			<Textfield variant="outlined" bind:value={$quickSearchTask} label="Search Tasks">
	    		<Icon class="material-icons" slot="trailingIcon">search</Icon>
	    		<HelperText slot="helper">search by matching substring</HelperText>
			</Textfield>
    	</div>
		{#if representative && $auth['grants']['representative-task-create']}
	    <div style="position: relative; float: right;">
			<Button color="primary" on:click={cb_add_new_task} variant="raised">
				<Icon class="material-icons">add</Icon>
				<ButtonLabel>ADD NEW TASK</ButtonLabel>
			</Button>
		</div>
		{/if}
	</div>

{#if filtered_tasks && filtered_tasks.length}

<DataTable table$aria-label="Tasks" style="width: 100%;"
  sortable
  bind:sort={tasksort}
  bind:sortDirection={tasksortDirection}
  on:SMUIDataTable:sorted={handleTaskSort}
>
    <Head>
        <Row>
            <Cell columnId="dates" style="text-align: center;">
                <Label>DATES</Label>
                <IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="member" style="text-align: center;">
                <Label>MEMBER</Label>
                <IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="task" style="text-align: center;">
                <Label>TASK</Label>
                <IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="group" style="text-align: center;">
                <Label>GROUP</Label>
                <IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="fte" style="text-align: center;">
                <Label>FTE</Label>
                <IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="operation" style="text-align: center;">
                <Label>OPERATION</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each filtered_tasks as task ( task.id ) }
      <Row data-entry-id="task-{task.id}">
        <Cell style="text-align: center;">{ task.dates }</Cell>
        <Cell style="text-align: center;">{ task.member }</Cell>
        <Cell style="text-align: center;">{ task.task }</Cell>
        <Cell style="text-align: center;">{ task.group }</Cell>
        <Cell style="text-align: center;">{ task.fte }</Cell>
        <Cell style="text-align: center;">
{#if representative && $auth['grants']['representative-task-edit']}
        <Button on:click={() => { router.goto('/assigned-task-edit/' + task.id); }} variant="raised">
            <ButtonIcon class="material-icons">edit</ButtonIcon>
            <ButtonLabel>EDIT</ButtonLabel>
        </Button>
        <Button on:click={() => { unassignTask( task.id ); }} variant="raised">
            <ButtonIcon class="material-icons">table</ButtonIcon>
            <ButtonLabel>DELETE</ButtonLabel>
        </Button>
{/if}
        </Cell>
      </Row>
    {/each}
    </Body>
  <Pagination slot="paginate">
    <svelte:fragment slot="rowsPerPage">
      <Label>Rows Per Page</Label>
      <Select variant="outlined" bind:value={taskrowsPerPage} noLabel>
        <Option value={10}>10</Option>
        <Option value={25}>25</Option>
        <Option value={100}>100</Option>
      </Select>
    </svelte:fragment>
    <svelte:fragment slot="total">
      {taskstart + 1}-{taskend} of {ttasks.length}
    </svelte:fragment>

    <IconButton
      class="material-icons"
      action="first-page"
      title="First page"
      on:click={() => (taskcurrentPage = 0)}
      disabled={taskcurrentPage === 0}>first_page</IconButton
    >
    <IconButton
      class="material-icons"
      action="prev-page"
      title="Prev page"
      on:click={() => taskcurrentPage--}
      disabled={taskcurrentPage === 0}>chevron_left</IconButton
    >
    <IconButton
      class="material-icons"
      action="next-page"
      title="Next page"
      on:click={() => taskcurrentPage++}
      disabled={taskcurrentPage === tasklastPage}>chevron_right</IconButton
    >
    <IconButton
      class="material-icons"
      action="last-page"
      title="Last page"
      on:click={() => (taskcurrentPage = tasklastPage)}
      disabled={taskcurrentPage === tasklastPage}>last_page</IconButton
    >
  </Pagination>
</DataTable>
	{:else}
		<p style="margin: 5vmin; text-align: center;"> NO TASKS FOUND </p>
	{/if}

	{/if}

	{/await}

	{/await}

	</Paper>

	{/if}

	{/await}

{/if}



{/key}

<style>
</style>