<script>

import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';
import { tran } from '../utils/tran.js';

import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';

import { screen, auth } from '../store.js';

import TaskStatsInstitution from './TaskStatsInstitution.svelte';
import TaskStatsGroup from './TaskStatsGroup.svelte';
import TaskStatsTask from './TaskStatsTask.svelte';

export let meta;

$screen = 'task-stats';

let tabs = [];
if ( $auth['grants']['statistics-view-per-institution'] ) {
	tabs = [ ...tabs, { "label": '_task_stats_institution_', "icon": "view_list", "mode": "/institution" } ];
}
if ( $auth['grants']['statistics-view-per-group'] ) {
	tabs = [ ...tabs, { "label": '_task_stats_group_', "icon": "view_list", "mode": "/group" } ];
}
if ( $auth['grants']['statistics-view-per-task'] ) {
    tabs = [ ...tabs, { "label": '_task_stats_task_', "icon": "view_list", "mode": "/task" } ];
}

let active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];

const tab_change = (t) => {
	if ( t ) {
	    router.goto('/task-stats' + t.mode);
	}
}

afterUpdate( () => {
    active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];
});

</script>

<TabBar tabs={tabs} let:tab bind:active>
    <Tab {tab} on:click={() => tab_change(tab)}>
        <Icon class="material-icons">{tab.icon}</Icon>
        <Label>{tran(tab.label)}</Label>
    </Tab>
</TabBar>

    <Route path="/institution"> <TaskStatsInstitution /> </Route>
    <Route path="/group"> <TaskStatsGroup /> </Route>
    <Route path="/task"> <TaskStatsTask /> </Route>
    <Route fallback> <TaskStatsInstitution /> </Route>

<style>

</style>