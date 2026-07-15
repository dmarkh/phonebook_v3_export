<script>

import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';

import { auth } from '../store.js';
import { screen, group_id, group_mode } from '../store.js';

import GroupView from './GroupView.svelte';
import GroupEdit from './GroupEdit.svelte';

import GroupMembers from './GroupMembers.svelte';
import GroupRoles from './GroupRoles.svelte';
import GroupTasks from './GroupTasks.svelte';
import GroupGraph from './GroupGraph.svelte';

export let meta;

$screen = 'groups';

let tabs = [
    { "label": "view", "icon": "view_list", "mode": "/view" }
];

if ( $auth['grants']['groups-edit'] ) {
	tabs = [ ...tabs,
    	{ "label": "edit", "icon": "edit", "mode": "/edit" },
    	{ "label": "members", "icon": "edit", "mode": "/members" },
    	{ "label": "roles", "icon": "edit", "mode": "/roles" }
	];
}

if ( $auth['grants']['group-tasks-view'] ) {
	tabs.push(
    	{ "label": "tasks", "icon": "edit", "mode": "/tasks" }
	);
}

if ( $auth['grants']['groups-edit'] ) {
	tabs.push(
    	{ "label": "graph", "icon": "edit", "mode": "/graph" }
	);
}

let active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];
if ( meta.params.id ) {
    $group_id = meta.params.id;
}
const tab_change = (t) => {
	if ( t ) {
	    router.goto('/group/' + meta.params.id + t.mode);
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

    <Route path="/view"> <GroupView /> </Route>
    <Route path="/edit"> <GroupEdit /> </Route>

    <Route path="/members"> <GroupMembers /> </Route>
    <Route path="/roles"> <GroupRoles /> </Route>
    <Route path="/tasks"> <GroupTasks /> </Route>
    <Route path="/graph"> <GroupGraph /> </Route>

    <Route fallback> <GroupView /> </Route>

<style>

</style>