<script>

import {meta, router, Route} from 'tinro';

router.base( window.pnb.basepath );

switch ( window.pnb.router ) {
	case 'hash':
		router.mode.hash();
		break;
	case 'memory':
		router.mode.memory();
		break;
	default:
}

import makeurl from '../utils/makeurl.js';

import { onMount } from 'svelte';
import { get } from 'svelte/store';
import { fade } from 'svelte/transition';
import { auth, status, screen, themeMode } from '../store.js';

import Drawer, { AppContent, Content, Subtitle } from '@smui/drawer';
import List, { Item, Label, Text, Graphic, Separator, Subheader } from '@smui/list';
import TopAppBar, { Row, Section, Title } from '@smui/top-app-bar';
import IconButton from '@smui/icon-button';
import LinearProgress from '@smui/linear-progress';
import Radio from '@smui/radio';

import Menu from '@smui/menu';
import Button, { Label as ButtonLabel } from '@smui/button';

import Representatives from './Representatives.svelte';

import Group from './Group.svelte';
import Groups from './Groups.svelte';
import GroupNew from './GroupNew.svelte';

import Institution from './Institution.svelte';
import Institutions from './Institutions.svelte';
import InstitutionNew from './InstitutionNew.svelte';
import InstitutionsChanges from './InstitutionsChanges.svelte';

import InstitutionsFilter from './InstitutionsFilter.svelte';
import InstitutionsBulkImport from './InstitutionsBulkImport.svelte';
import InstitutionsBulkUpdate from './InstitutionsBulkUpdate.svelte';

import MyOverview from './MyOverview.svelte';

import AiTools from './AiTools.svelte';

import MyTasksEdit from './MyTasksEdit.svelte';
import MemberTasksEdit from './MemberTasksEdit.svelte';
import GroupTasksEdit from './GroupTasksEdit.svelte';
import AssignedTasksEdit from './AssignedTasksEdit.svelte';

import Member from './Member.svelte';
import Members from './Members.svelte';
import MemberNew from './MemberNew.svelte';
import MembersChanges from './MembersChanges.svelte';

import MembersFilter from './MembersFilter.svelte';
import MembersBulkImport from './MembersBulkImport.svelte';
import MembersBulkUpdate from './MembersBulkUpdate.svelte';

import MemberField from './MemberField.svelte';
import MemberFieldgroup from './MemberFieldgroup.svelte';
import MemberFields from './MemberFields.svelte';
import MemberFieldgroups from './MemberFieldgroups.svelte';

import Document from './Document.svelte';
import Documents from './Documents.svelte';
import DocumentField from './DocumentField.svelte';
import DocumentFields from './DocumentFields.svelte';
import DocumentNewField from './DocumentNewField.svelte';
import DocumentEditField from './DocumentEditField.svelte';
import DocumentNew from './DocumentNew.svelte';
import DocumentsFilter from './DocumentsFilter.svelte';

import Task from './Task.svelte';
import TasksAssigned from './TasksAssigned.svelte';
import Tasks from './Tasks.svelte';
import TaskField from './TaskField.svelte';
import TaskFields from './TaskFields.svelte';
import TaskNewField from './TaskNewField.svelte';
import TaskEditField from './TaskEditField.svelte';
import TaskNew from './TaskNew.svelte';
import TasksFilter from './TasksFilter.svelte';

import Event from './Event.svelte';
import Events from './Events.svelte';
import EventField from './EventField.svelte';
import EventFields from './EventFields.svelte';
import EventNewField from './EventNewField.svelte';
import EventEditField from './EventEditField.svelte';
import EventNew from './EventNew.svelte';
import EventsFilter from './EventsFilter.svelte';

import InstitutionField from './InstitutionField.svelte';
import InstitutionFields from './InstitutionFields.svelte';
import InstitutionNewField from './InstitutionNewField.svelte';
import InstitutionEditField from './InstitutionEditField.svelte';

import InstitutionFieldgroup from './InstitutionFieldgroup.svelte';
import InstitutionFieldgroups from './InstitutionFieldgroups.svelte';
import InstitutionNewFieldgroup from './InstitutionNewFieldgroup.svelte';
import InstitutionEditFieldgroup from './InstitutionEditFieldgroup.svelte';

import MemberNewField from './MemberNewField.svelte';
import MemberNewFieldgroup from './MemberNewFieldgroup.svelte';
import MemberEditField from './MemberEditField.svelte';
import MemberEditFieldgroup from './MemberEditFieldgroup.svelte';

import Workflows from './Workflows.svelte';
import Workflow from './Workflow.svelte';
import WorkflowNew from './WorkflowNew.svelte';
import WorkflowBlocks from './WorkflowBlocks.svelte';
import WorkflowBlock from './WorkflowBlock.svelte';

import WorldMap from './WorldMap.svelte';
import Stats from './Stats.svelte';

import AuthorLists from './AuthorLists.svelte';
import AuthLogin from './AuthLogin.svelte';
import NotFound from './NotFound.svelte';

import EventPoll from './EventPoll.svelte';
import EventPolls from './EventPolls.svelte';
import EventPollsList from './EventPollsList.svelte';

import EventDisplay from './EventDisplay.svelte';

import TaskStats from './TaskStats.svelte';

import { keepalive } from '../utils/pnb-api.js';
import { show_stop_and_warn } from '../store.js';
import { load_localizations, tran, set_language, get_languages } from '../utils/tran.js';

let account_menu, language_menu;
let keepalive_id = false;
let languages = {};
let refresh = false;

const onload = async () => {
	await load_localizations();
	languages = get_languages();
	const lang = localStorage.getItem("crisp-language-code");
	set_language( lang || 'en' );
}

show_stop_and_warn.subscribe( (val) => {
	if ( val === true ) {
		clearInterval(keepalive_id);
		keepalive_id = false;
	}
});

const toggleTheme = () => {
	if ( $themeMode === 'light' ) {
		$themeMode = 'dark';
	} else {
		$themeMode = 'light';
	}
}

let descriptors_open = false;

function deleteAllCookies() {
  const cookies = document.cookie.split(";");
  for (let i = 0; i < cookies.length; i++) {
    const cookie = cookies[i];
    const eqPos = cookie.indexOf("=");
    const name = eqPos > -1 ? cookie.substring(0, eqPos) : cookie;
    document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
  }
}

const doLogout = async () => {
	deleteAllCookies();
	$auth = { token: "", role: "", grants: {} };
	router.goto('/');
}

const toggleStatus = () => {
	$status = $status == 'active' ? 'inactive' : 'active';
}

const onAccessChange = () => {
	if ( $auth.roles && $auth.role ) {
		$auth = { ...$auth, grants: $auth.roles[$auth.role] };
	}
}

onMount(() => {
	if ( !keepalive_id ) {
		keepalive_id = setInterval( async () => {
			if ( !$show_stop_and_warn ) {
				let data = await keepalive();
				console.log( 'keepalive: ' + JSON.stringify(data) );
			}
		}, window.pnb['keepalive-interval']*1000 );
	}
	$screen = window.location.hash.replace('#/','');
});

</script>


<svelte:head>
  {#if $themeMode === 'dark'}
    <link rel="stylesheet" href="smui-dark.css" />
  {:else}
    <link rel="stylesheet" href="smui.css" />
  {/if}
</svelte:head>

{#key refresh}

{#if $show_stop_and_warn}

<div class="dimmer"></div>
<div class="stop-and-warn flex-center">
	<p>PHONEBOOK: SESSION EXPIRED</p>
	<p>
		<Button on:click={() => { window.location.reload(); }} color="secondary" variant="raised">
        	<ButtonLabel>RELOAD PAGE</ButtonLabel>
    	</Button>
	</p>
</div>

{:else}

	<div id="viewport">

{#await onload()}

	<LinearProgress indeterminate />

{:then onl}

{#if !$auth['role']}

	<AuthLogin />

{:else}

	<div class="flexy">
		<div class="top-app-bar-container flexor">
			<TopAppBar variant="static" color="primary">
				<Row>
					<Section>
						<IconButton class="material-icons" on:click={() => { $screen = 'stats'; router.goto('/'); }}>menu</IconButton>
						<Title>{tran('_collaboration_name_')}</Title>
					</Section>
					<Section align="end" toolbar>
					{#if $themeMode === 'light'}
						<IconButton class="material-icons" aria-label="Dark Mode" on:click={() => { toggleTheme(); }}>dark_mode</IconButton>
					{:else}
						<IconButton class="material-icons" aria-label="Light Mode" on:click={() => { toggleTheme(); }}>light_mode</IconButton>
					{/if}

					<IconButton class="material-icons" aria-label="Account" on:click={() => { language_menu.setOpen(true) }}>
						translate
						<Menu bind:this={language_menu} anchorCorner="BOTTOM_LEFT">
							<List>
								{#each Object.entries(languages) as [code,lang]}
								<Item on:SMUI:action={() => { set_language(code); localStorage.setItem("crisp-language-code", code); refresh = !refresh; }}>
									<Graphic class="material-icons" aria-hidden="true">language</Graphic>
									<Text>{lang}</Text>
								</Item>
								{/each}
							</List>
						</Menu>
					</IconButton>


					<IconButton class="material-icons" aria-label="Account" on:click={() => { account_menu.setOpen(true) }}>
						account_circle
						<Menu bind:this={account_menu} anchorCorner="BOTTOM_LEFT">
							<List>
								<Item>
									<Graphic class="material-icons" aria-hidden="true">person</Graphic>
									<Text>{($auth.name || $auth.user)}</Text>
								</Item>
								<Separator />
								{#if $auth.roles}
						    	    {#each Object.keys($auth.roles) as role}
										<Item>
											<Graphic>
												<Radio on:change={onAccessChange} bind:group={$auth.role} value="{role}" />
											</Graphic>
											<Label>{role}</Label>
										</Item>
									{/each}
								{:else}
								<Item>
									<Graphic class="material-icons" aria-hidden="true">group</Graphic>
									<Text>{$auth.role}</Text>
								</Item>
								{/if}
								<Separator />
								<Item on:SMUI:action={() => ( doLogout() )}>
									<Graphic class="material-icons" aria-hidden="true">logout</Graphic>
									<Text>LOGOUT</Text>
								</Item>
								{#if $auth['grants']['members-edit']}
									{#if $status === 'active'}
										<Item on:SMUI:action={() => ( toggleStatus() )}>
											<Graphic class="material-icons" aria-hidden="true">toggle_on</Graphic>
											<Text>SHOW INACTIVE RECORDS</Text>
										</Item>
									{:else}
										<Item on:SMUI:action={() => ( toggleStatus() )}>
											<Graphic class="material-icons" aria-hidden="true">toggle_off</Graphic>
											<Text>SHOW ACTIVE RECORDS</Text>
										</Item>
									{/if}
								{/if}
								<Item>
									<Graphic class="material-icons" aria-hidden="true">construction</Graphic>
									<Text>BUILD: {window.pnb['build-date']}</Text>
								</Item>
							</List>
						</Menu>
					</IconButton>

					</Section>
				</Row>
			</TopAppBar>
			<div class="flexor-content">

		<div class="drawer-container">
			<Drawer color="secondary">
				<Content>
					<List>
{#if window.pnb.orcid && window.pnb.mid}
						<Item href="{makeurl('/my-overview')}" on:click={() => { $screen = 'my-overview'; }} activated={$screen === 'my-overview'}>
							<Graphic class="material-icons" aria-hidden="true">account_circle</Graphic>
							<Text>{tran('_my_overview_')}</Text>
						</Item>
{/if}
{#if $auth['grants']['representatives-view']}
						<Item href="{makeurl('/representatives')}" on:click={() => { $screen = 'representatives'; }} activated={$screen === 'representatives'}>
								<Graphic class="material-icons" aria-hidden="true">group</Graphic>
								<Text>{tran('_representatives_')}</Text>
						</Item>
{/if}
{#if $auth['grants']['worldmap-view']}
						<Item href="{makeurl('/worldmap')}" on:click={() => { $screen = 'worldmap'; }} activated={$screen === 'worldmap'}>
							<Graphic class="material-icons" aria-hidden="true">map</Graphic>
							<Text>{tran('_world_map_')}</Text>
						</Item>
{/if}
{#if $auth['grants']['authorlists-view']}
						<Item href="{makeurl('/authorlists')}" on:click={() => { $screen = 'authorlists'; }} activated={$screen === 'authorlists'}>
							<Graphic class="material-icons" aria-hidden="true">portrait</Graphic>
							<Text>{tran('_author_lists_')}</Text>
						</Item>
{/if}
{#if $auth['grants']['event-display-view']}
						<Item href="{makeurl('/event-display')}" on:click={() => { $screen = 'event-display'; }} activated={$screen === 'event-display'}>
							<Graphic class="material-icons" aria-hidden="true">view_in_ar</Graphic>
							<Text>{tran('_event_display_')}</Text>
						</Item>
{/if}
{#if $auth['grants']['ai-tools']}
						<Item href="{makeurl('/ai-tools')}" on:click={() => { $screen = 'ai-tools'; }} activated={$screen === 'ai-tools'}>
							<Graphic class="material-icons" aria-hidden="true">star</Graphic>
							<Text>{tran('_ai_tools_')}</Text>
						</Item>
{/if}

{#if window.pnb.orcid && window.pnb.mid && $auth['grants']['event-poll-view']}
						<Separator />
						<details open>
						<summary class="mdc-deprecated-list-group__subheader">
							{tran('_meeting_polls_')}
						</summary>
						<Item href="{makeurl('/meeting-polls')}" on:click={() => { $screen = 'meeting-polls'; }} activated={$screen === 'meeting-polls'}>
							<Graphic class="material-icons" aria-hidden="true">ballot</Graphic>
							<Text>{tran('_meeting_polls_')}</Text>
						</Item>
{#if window.pnb.orcid && window.pnb.mid && $auth['grants']['event-poll-create']}
						<Item href="{makeurl('/new-meeting-poll')}" on:click={() => { $screen = 'new-meeting-poll'; }} activated={$screen === 'new-meeting-poll'}>
							<Graphic class="material-icons" aria-hidden="true">ballot</Graphic>
							<Text>{tran('_new_meeting_poll_')}</Text>
						</Item>
{/if}
						</details>
{/if}
{#if $auth['grants']['statistics-view']}
						<Separator />
						<details open>
						<summary class="mdc-deprecated-list-group__subheader">
							{tran('_statistics_')}
						</summary>
						<Item href="{makeurl('/stats')}" on:click={() => { $screen = 'stats'; }} activated={$screen === 'stats'}>
								<Graphic class="material-icons" aria-hidden="true">query_stats</Graphic>
								<Text>{tran('_statistics_')}</Text>
						</Item>
{#if $auth['grants']['statistics-view-tasks']}
						<Item href="{makeurl('/task-stats')}" on:click={() => { $screen = 'task-stats'; }} activated={$screen === 'task-stats'}>
								<Graphic class="material-icons" aria-hidden="true">query_stats</Graphic>
								<Text>{tran('_task_statistics_')}</Text>
						</Item>
{/if}
						</details>
{/if}
						<Separator />
{#if $auth['grants']['institutions-view']}
						<details open>
						<summary class="mdc-deprecated-list-group__subheader">
							{tran('_institutions_')}
						</summary>
						<Item href="{makeurl('/institutions')}" on:click={() => { $screen = 'institutions'; }} activated={$screen === 'institutions' || $screen === 'institution' }>
							<Graphic class="material-icons" aria-hidden="true">public</Graphic>
							<Text>{tran('_institutions_')}</Text>
						</Item>
						<Item href="{makeurl('/filter-institutions')}" on:click={() => { $screen = 'filter-institutions'; }} activated={$screen === 'filter-institutions' }>
							<Graphic class="material-icons" aria-hidden="true">view_list</Graphic>
							<Text>{tran('_filter_institutions_')}</Text>
						</Item>
	{#if $auth['grants']['institutions-create']}
						<Item href="{makeurl('/new-institution')}" on:click={() => { $screen = 'new-institution'; }} activated={$screen === 'new-institution' }>
							<Graphic class="material-icons" aria-hidden="true">add_circle</Graphic>
							<Text>{tran('_add_new_institution_')}</Text>
						</Item>
	{/if}
	{#if $auth['grants']['institutions-changes']}
						<Item href="{makeurl('/institutions-changes')}" on:click={() => { $screen = 'institutions-changes'; }} activated={$screen === 'institutions-changes' }>
							<Graphic class="material-icons" aria-hidden="true">history</Graphic>
							<Text>{tran('_institution_changes_')}</Text>
						</Item>
	{/if}
						</details>
						<Separator />
{/if}

{#if $auth['grants']['members-view']}
						<details open>
						<summary class="mdc-deprecated-list-group__subheader">
							{tran('_members_')}
						</summary>
						<Item href="{makeurl('/members')}" on:click={() => { $screen = 'members'; }} activated={$screen === 'members' || $screen === 'member' }>
							<Graphic class="material-icons" aria-hidden="true">group</Graphic>
							<Text>{tran('_members_')}</Text>
						</Item>
						<Item href="{makeurl('/filter-members')}" on:click={() => { $screen = 'filter-members'; }} activated={$screen === 'filter-members'}>
							<Graphic class="material-icons" aria-hidden="true">view_list</Graphic>
							<Text>{tran('_filter_members_')}</Text>
						</Item>
	{#if $auth['grants']['members-create']}
						<Item href="{makeurl('/new-member')}" on:click={() => { $screen = 'new-member'; }} activated={$screen === 'new-member'}>
							<Graphic class="material-icons" aria-hidden="true">add_circle</Graphic>
							<Text>{tran('_add_new_member_')}</Text>
						</Item>
	{/if}
	{#if $auth['grants']['members-changes']}
						<Item href="{makeurl('/members-changes')}" on:click={() => { $screen = 'members-changes'; }} activated={$screen === 'members-changes'}>
							<Graphic class="material-icons" aria-hidden="true">history</Graphic>
							<Text>{tran('_member_changes_')}</Text>
						</Item>
	{/if}
						</details>
						<Separator />
{/if}

{#if $auth['grants']['groups-view']}
						<details open>
						<summary class="mdc-deprecated-list-group__subheader">
							{tran('_groups_')}
						</summary>
						<Item href="{makeurl('/groups')}" on:click={() => { $screen = 'groups'; }} activated={$screen === 'groups' || $screen === 'group' }>
							<Graphic class="material-icons" aria-hidden="true">group</Graphic>
							<Text>{tran('_groups_')}</Text>
						</Item>
	{#if $auth['grants']['groups-create']}
						<Item href="{makeurl('/new-group')}" on:click={() => { $screen = 'new-group'; }} activated={$screen === 'new-group'}>
							<Graphic class="material-icons" aria-hidden="true">add_circle</Graphic>
							<Text>{tran('_add_new_group_')}</Text>
						</Item>
	{/if}
						</details>
						<Separator />
{/if}

{#if $auth['grants']['documents-view']}
						<details open>
						<summary class="mdc-deprecated-list-group__subheader">
							{tran('_documents_')}
						</summary>
						<Item href="{makeurl('/documents')}" on:click={() => { $screen = 'documents'; }} activated={$screen === 'documents' || $screen === 'documents' }>
							<Graphic class="material-icons" aria-hidden="true">description</Graphic>
							<Text>{tran('_documents_')}</Text>
						</Item>
						<Item href="{makeurl('/filter-documents')}" on:click={() => { $screen = 'filter-documents'; }} activated={$screen === 'filter-documents'}>
							<Graphic class="material-icons" aria-hidden="true">view_list</Graphic>
							<Text>{tran('_filter_documents_')}</Text>
						</Item>
	{#if $auth['grants']['documents-create']}
						<Item href="{makeurl('/new-document')}" on:click={() => { $screen = 'new-document'; }} activated={$screen === 'new-document'}>
							<Graphic class="material-icons" aria-hidden="true">add_circle</Graphic>
							<Text>{tran('_add_new_document_')}</Text>
						</Item>
	{/if}
						</details>
						<Separator />
{/if}

{#if $auth['grants']['tasks-view']}
						<details open>
						<summary class="mdc-deprecated-list-group__subheader">
							TASKS
						</summary>

{#if $auth['grants']['assigned-tasks-view']}
						<Item href="{makeurl('/assigned-tasks')}" on:click={() => { $screen = 'assigned-tasks'; }} activated={$screen === 'assigned-tasks' }>
							<Graphic class="material-icons" aria-hidden="true">task</Graphic>
							<Text>{tran('_assigned_tasks_')}</Text>
						</Item>
{/if}
						<Item href="{makeurl('/tasks')}" on:click={() => { $screen = 'tasks'; }} activated={$screen === 'tasks' }>
							<Graphic class="material-icons" aria-hidden="true">task</Graphic>
							<Text>{tran('_tasks_')}</Text>
						</Item>
						<Item href="{makeurl('/filter-tasks')}" on:click={() => { $screen = 'filter-tasks'; }} activated={$screen === 'filter-tasks'}>
							<Graphic class="material-icons" aria-hidden="true">view_list</Graphic>
							<Text>{tran('_filter_tasks_')}</Text>
						</Item>
	{#if $auth['grants']['tasks-create']}
						<Item href="{makeurl('/new-task')}" on:click={() => { $screen = 'new-task'; }} activated={$screen === 'new-task'}>
							<Graphic class="material-icons" aria-hidden="true">add_task</Graphic>
							<Text>{tran('_add_new_task_')}</Text>
						</Item>
	{/if}
						</details>
						<Separator />
{/if}

{#if $auth['grants']['events-view']}
						<details open>
						<summary class="mdc-deprecated-list-group__subheader">
							{tran('_events_')}
						</summary>
						<Item href="{makeurl('/events')}" on:click={() => { $screen = 'events'; }} activated={$screen === 'events' || $screen === 'events' }>
							<Graphic class="material-icons" aria-hidden="true">group</Graphic>
							<Text>{tran('_events_')}</Text>
						</Item>
						<Item href="{makeurl('/filter-events')}" on:click={() => { $screen = 'filter-events'; }} activated={$screen === 'filter-events'}>
							<Graphic class="material-icons" aria-hidden="true">view_list</Graphic>
							<Text>{tran('_filter_events_')}</Text>
						</Item>
	{#if $auth['grants']['events-create']}
						<Item href="{makeurl('/new-event')}" on:click={() => { $screen = 'new-event'; }} activated={$screen === 'new-event'}>
							<Graphic class="material-icons" aria-hidden="true">add_circle</Graphic>
							<Text>{tran('_add_new_event_')}</Text>
						</Item>
	{/if}
						</details>
						<Separator />
{/if}

{#if $auth['grants']['workflows-view']}
						<details open>
						<summary class="mdc-deprecated-list-group__subheader">
							{tran('_workflows_')}
						</summary>
						<Item href="{makeurl('/workflows')}" on:click={() => { $screen = 'workflows'; }} activated={$screen === 'workflows' || $screen === 'workflows' }>
							<Graphic class="material-icons" aria-hidden="true">view_timeline</Graphic>
							<Text>{tran('_workflows_')}</Text>
						</Item>
	{#if $auth['grants']['workflows-edit']}
						<Item href="{makeurl('/new-workflow')}" on:click={() => { $screen = 'new-workflow'; }} activated={$screen === 'new-workflow'}>
							<Graphic class="material-icons" aria-hidden="true">add_circle</Graphic>
							<Text>{tran('_add_new_workflow_')}</Text>
						</Item>
	{/if}
						</details>
						<Separator />
{/if}

{#if $auth['grants']['descriptors-view']}
						<details open>
						<summary class="mdc-deprecated-list-group__subheader">
							{tran('_descriptors_')}
						</summary>
						<Item href="{makeurl('/institution-fields')}" on:click={() => { $screen = 'institution-fields'; }} activated={$screen === 'institution-fields'}>
							<Graphic class="material-icons" aria-hidden="true">add</Graphic>
							<Text>{tran('_institution_fields_')}</Text>
						</Item>
						<Item href="{makeurl('/member-fields')}" on:click={() => { $screen = 'member-fields'; }} activated={$screen === 'member-fields'}>
							<Graphic class="material-icons" aria-hidden="true">add</Graphic>
							<Text>{tran('_member_fields_')}</Text>
						</Item>
						<Item href="{makeurl('/institution-field-groups')}" on:click={() => { $screen = 'institution-field-groups'; }} activated={$screen === 'institution-field-groups'}>
							<Graphic class="material-icons" aria-hidden="true">add</Graphic>
							<Text>{tran('_institution_fieldgroups_')}</Text>
						</Item>
						<Item href="{makeurl('/member-field-groups')}" on:click={() => { $screen = 'member-field-groups'; }} activated={$screen === 'member-field-groups'}>
							<Graphic class="material-icons" aria-hidden="true">add</Graphic>
							<Text>{tran('_member_fieldgroups_')}</Text>
						</Item>
						<Item href="{makeurl('/document-fields')}" on:click={() => { $screen = 'document-fields'; }} activated={$screen === 'document-fields'}>
							<Graphic class="material-icons" aria-hidden="true">add</Graphic>
							<Text>{tran('_document_fields_')}</Text>
						</Item>
						<Item href="{makeurl('/task-fields')}" on:click={() => { $screen = 'task-fields'; }} activated={$screen === 'task-fields'}>
							<Graphic class="material-icons" aria-hidden="true">add</Graphic>
							<Text>{tran('_task_fields_')}</Text>
						</Item>
						<Item href="{makeurl('/event-fields')}" on:click={() => { $screen = 'event-fields'; }} activated={$screen === 'event-fields'}>
							<Graphic class="material-icons" aria-hidden="true">add</Graphic>
							<Text>{tran('_event_fields_')}</Text>
						</Item>
						<Item href="{makeurl('/workflow-blocks')}" on:click={() => { $screen = 'workflow-blocks'; }} activated={$screen === 'workflow-blocks'}>
							<Graphic class="material-icons" aria-hidden="true">add</Graphic>
							<Text>{tran('_workflow_blocks_')}</Text>
						</Item>
						</details>
						<Separator />
{/if}
					</List>
				</Content>
			</Drawer>
			<AppContent class="app-content">
				<main id="app-main-content" class="main-content">
					{#key $status}
					<Route path="/"> <Stats /> </Route>
					<Route path="/stats"> <Stats /> </Route>
					<Route path="/representatives"> <Representatives /> </Route>
					<Route path="/worldmap"> <WorldMap /> </Route>
					<Route path="/authorlists/*" let:meta> <AuthorLists {meta} /> </Route>
					<Route path="/ai-tools/*" let:meta> <AiTools {meta} /> </Route>

					<Route path="/documents"> <Documents /> </Route>
					<Route path="/document/:id/*" let:meta> <Document {meta} /> </Route>
					<Route path="/new-document"> <DocumentNew /> </Route>
					<Route path="/document-field/:id/*" let:meta> <DocumentField {meta} /> </Route>
					<Route path="/document-fields"> <DocumentFields /> </Route>
					<Route path="/document-new-field"> <DocumentNewField /> </Route>
					<Route path="/document-edit-field/:id/*" let:meta> <DocumentEditField {meta} /> </Route>
					<Route path="/filter-documents"> <DocumentsFilter /> </Route>

					<Route path="/assigned-tasks"> <TasksAssigned /> </Route>
					<Route path="/tasks"> <Tasks /> </Route>
					<Route path="/task/:id/*" let:meta> <Task {meta} /> </Route>
					<Route path="/new-task"> <TaskNew /> </Route>
					<Route path="/task-field/:id/*" let:meta> <TaskField {meta} /> </Route>
					<Route path="/task-fields"> <TaskFields /> </Route>
					<Route path="/task-new-field"> <TaskNewField /> </Route>
					<Route path="/task-edit-field/:id/*" let:meta> <TaskEditField {meta} /> </Route>
					<Route path="/filter-tasks"> <TasksFilter /> </Route>

					<Route path="/task-stats/*" let:meta> <TaskStats {meta} /> </Route>

					<Route path="/events"> <Events /> </Route>
					<Route path="/event/:id/*" let:meta> <Event {meta} /> </Route>
					<Route path="/new-event"> <EventNew /> </Route>
					<Route path="/event-field/:id/*" let:meta> <EventField {meta} /> </Route>
					<Route path="/event-fields"> <EventFields /> </Route>
					<Route path="/event-new-field"> <EventNewField /> </Route>
					<Route path="/event-edit-field/:id/*" let:meta> <EventEditField {meta} /> </Route>
					<Route path="/filter-events"> <EventsFilter /> </Route>

					<Route path="/workflows"> <Workflows /> </Route>
					<Route path="/workflow/:id/*" let:meta> <Workflow {meta} /> </Route>
					<Route path="/new-workflow"> <WorkflowNew /> </Route>

					<Route path="/workflow-blocks"> <WorkflowBlocks /> </Route>
					<Route path="/workflow-block/:id/*" let:meta> <WorkflowBlock {meta} /> </Route>

					<Route path="/new-institution"> <InstitutionNew /> </Route>
					<Route path="/institution/:id/*" let:meta> <Institution {meta} /> </Route>
					<Route path="/institution-edit-field/:id/*" let:meta> <InstitutionEditField {meta} /> </Route>
					<Route path="/institution-edit-fieldgroup/:id/*" let:meta> <InstitutionEditFieldgroup {meta} /> </Route>
					<Route path="/institutions" let:meta>
						<Institutions {meta} />
					</Route>
					<Route path="/institutions-changes/*" let:meta> <InstitutionsChanges {meta} /> </Route>
					<Route path="/institutions-bulk-import"> <InstitutionsBulkImport /> </Route>
					<Route path="/institutions-bulk-update"> <InstitutionsBulkUpdate /> </Route>
					<Route path="/filter-institutions"> <InstitutionsFilter /> </Route>
					<Route path="/institution-fields"> <InstitutionFields /> </Route>
					<Route path="/institution-field-groups"> <InstitutionFieldgroups /> </Route>
					<Route path="/institution-new-field"> <InstitutionNewField /> </Route>
					<Route path="/institution-new-fieldgroup"> <InstitutionNewFieldgroup /> </Route>
					<Route path="/institution-field/:id/*" let:meta> <InstitutionField {meta} /> </Route>
					<Route path="/institution-fieldgroup/:id/*" let:meta> <InstitutionFieldgroup {meta} /> </Route>

					<Route path="/my-task-edit/:id/*" let:meta> <MyTasksEdit {meta} /> </Route>
					<Route path="/member-task-edit/:id/*" let:meta> <MemberTasksEdit {meta} /> </Route>
					<Route path="/group-task-edit/:id/*" let:meta> <GroupTasksEdit {meta} /> </Route>
					<Route path="/assigned-task-edit/:id/*" let:meta> <AssignedTasksEdit {meta} /> </Route>

					<Route path="/my-overview/*" let:meta> <MyOverview {meta} /> </Route>

					<Route path="/new-member"> <MemberNew /> </Route>
					<Route path="/member/:id/*" let:meta> <Member {meta} /> </Route>
					<Route path="/member-edit-field/:id/*" let:meta> <MemberEditField {meta} /> </Route>
					<Route path="/member-edit-fieldgroup/:id/*" let:meta> <MemberEditFieldgroup {meta} /> </Route>
					<Route path="/members" let:meta>
						<Members {meta} />
					</Route>
					<Route path="/members-changes/*" let:meta> <MembersChanges {meta} /> </Route>
					<Route path="/members-bulk-import"> <MembersBulkImport /> </Route>
					<Route path="/members-bulk-update"> <MembersBulkUpdate /> </Route>
					<Route path="/filter-members"> <MembersFilter /> </Route>
					<Route path="/member-fields"> <MemberFields /> </Route>
					<Route path="/member-field-groups"> <MemberFieldgroups /> </Route>
					<Route path="/member-new-field"> <MemberNewField /> </Route>
					<Route path="/member-new-fieldgroup"> <MemberNewFieldgroup /> </Route>
					<Route path="/member-field/:id/*" let:meta> <MemberField {meta} /> </Route>
					<Route path="/member-fieldgroup/:id/*" let:meta> <MemberFieldgroup {meta} /> </Route>


					<Route path="/groups"> <Groups /> </Route>
					<Route path="/new-group"> <GroupNew /> </Route>
					<Route path="/group/:id/*" let:meta> <Group {meta} /> </Route>

					<Route path="/event-display" let:meta>
						<EventDisplay {meta} />
					</Route>

					<Route path="/new-meeting-poll/*" let:meta>
						<EventPolls {meta} />
					</Route>

					<Route path="/meeting-polls" let:meta>
						<EventPollsList {meta} />
					</Route>

					<Route path="/meeting-poll/:id" let:meta>
						<EventPoll {meta} />
					</Route>

					{/key}
				</main>
			</AppContent>
		</div>

			</div>
		</div>
	</div>

{/if}
{/await}

	</div>
{/if}

{/key}

<style>

  .top-app-bar-container {
    width: 100%;
    overflow: auto;
    display: inline-block;
  }

  .flexy {
    display: flex;
    flex-wrap: wrap;
	width: 100vw;
	height: 100vh;
	overflow: hidden;
  }

  .flexor {
    display: inline-flex;
    flex-direction: column;
	overflow: hidden;
  }

  .drawer-container {
    position: relative;
    display: flex;
    height: calc(100vh - 64px);
    width: 100vw;
    border: 1px solid var(--mdc-theme-text-hint-on-background, rgba(0, 0, 0, 0.1));
    overflow: hidden;
    z-index: 0;
  }

  * :global(.app-content) {
    flex: auto;
    overflow: auto;
    position: relative;
    flex-grow: 1;
  }


  .main-content {
    overflow: auto;
    padding: 16px;
    height: 100%;
    box-sizing: border-box;
  }

  .dimmer {
    position: absolute;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    margin: 0;
    padding: 0;
    overflow: hidden;
    background-color: #000C;
  }

  .stop-and-warn {
	position: absolute;
	width: 50vmin;
	height: 30vmin;
	top: 50%;
	left: 50%;
	transform: translate(-50%,-50%);
	background-color: #F99;
	color: #000;
	font-size: 2vmin;
	padding: 2vmin;
	border-radius: 2vmin;
	outline: 0.5vmin solid #FFF;
  }

 .flex-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
 }

</style>