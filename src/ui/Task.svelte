<script>

import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';

import TaskHistory from './TaskHistory.svelte';
import TaskEdit from './TaskEdit.svelte';
import TaskView from './TaskView.svelte';
import TaskNew from './TaskNew.svelte';

import TaskMembers from './TaskMembers.svelte';
import TaskGroups from './TaskGroups.svelte';
import TaskInstitutions from './TaskInstitutions.svelte';

import { screen, auth } from '../store.js';
import { task_id, task_mode } from '../store.js';

export let meta;

$screen = 'tasks';

let tabs = [
	{ "label": "view", "icon": "view_list", "mode": "/view" },
];

if ( $auth['grants']['tasks-edit'] ) {
	tabs = [ ...tabs,
		{ "label": "edit", "icon": "edit", "mode": "/edit" }
	];
}
if ( $auth['grants']['tasks-history'] ) {
	tabs = [ ...tabs,
		{ "label": "history", "icon": "history", "mode": "/history" }
	];
}

let active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];
if ( meta.params.id ) {
	$task_id = meta.params.id;
}

const tab_change = (t) => {
	if ( t ) {
		router.goto('/task/' + meta.params.id + t.mode);
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

	<Route path="/view"> <TaskView /> </Route>
	<Route path="/edit"> <TaskEdit /> </Route>
    <Route path="/members"> <TaskMembers /> </Route>
    <Route path="/groups"> <TaskGroups /> </Route>
    <Route path="/institutions"> <TaskInstitutions /> </Route>
	<Route path="/history"> <TaskHistory /> </Route>

	<Route fallback> <TaskView /> </Route>

<style>

</style>