<script>

import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';

import InstitutionMembers from './InstitutionMembers.svelte';
import InstitutionHistory from './InstitutionHistory.svelte';
import InstitutionEdit from './InstitutionEdit.svelte';
import InstitutionView from './InstitutionView.svelte';
import InstitutionTasks from './InstitutionTasks.svelte';
import InstitutionNew from './InstitutionNew.svelte';
import InstitutionNewMember from './InstitutionNewMember.svelte';

import { screen, auth } from '../store.js';
import { institution_id, institution_mode } from '../store.js';

$screen = 'institutions';

export let meta;

let tabs = [
	{ "label": "view", "icon": "view_list", "mode": "/view" },
	{ "label": "show members", "icon": "group", "mode": "/members" }
];

if ( $auth['grants']['members-create'] ) {
	tabs = [ ...tabs,
		{ "label": "new member", "icon": "person_add", "mode": "/add-new-member" }
	];
}

if ( $auth['grants']['institutions-edit'] ) {
	tabs = [ ...tabs,
		{ "label": "edit", "icon": "edit", "mode": "/edit" }
	];
}

if ( $auth['grants']['institution-tasks-view'] ) {
	tabs.push(
		{ "label": "tasks", "icon": "task", "mode": "/tasks" }
	);
}

if ( $auth['grants']['institutions-history'] ) {
	tabs = [ ...tabs,
		{ "label": "history", "icon": "history", "mode": "/history" }
	];
}

let active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];
if ( meta.params.id ) {
	$institution_id = meta.params.id;
}

const tab_change = (t) => {
	if ( t ) {
		router.goto('/institution/' + meta.params.id + t.mode);
	}
}

afterUpdate( () => {
	active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];
});

</script>

<TabBar tabs={tabs} let:tab bind:active>
	<Tab {tab} on:click={() => tab_change(tab)}>
		<Icon class="material-icons">{tab.icon}</Icon>
		<Label>{tab.label}</Label>
	</Tab>
</TabBar>

	<Route path="/view"> <InstitutionView /> </Route>
	<Route path="/edit"> <InstitutionEdit /> </Route>
	<Route path="/members"> <InstitutionMembers /> </Route>
	<Route path="/tasks"> <InstitutionTasks /> </Route>
	<Route path="/history"> <InstitutionHistory /> </Route>
	<Route path="/add-new-member"> <InstitutionNewMember /> </Route>

	<Route fallback> <InstitutionView /> </Route>

<style>

</style>