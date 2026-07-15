<script>

import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

import AccessDenied from './AccessDenied.svelte';

import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';

import WorkflowView from './WorkflowView.svelte';
import WorkflowEdit from './WorkflowEdit.svelte';
import WorkflowConfig from './WorkflowConfig.svelte';

import { screen, auth } from '../store.js';
import { workflow_id, workflow_mode } from '../store.js';

export let meta;

$screen = 'workflows';

let tabs = [
	{ "label": "view", "icon": "view_list", "mode": "/view" },
];

if ( $auth['grants']['workflows-edit'] ) {
	tabs = [ ...tabs,
		{ "label": "edit", "icon": "edit", "mode": "/edit" }
	];
	tabs = [ ...tabs,
		{ "label": "config", "icon": "settings", "mode": "/config" }
	];
}

let active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];
if ( meta.params.id ) {
	$workflow_id = meta.params.id;
}

const tab_change = (t) => {
	if ( t ) {
		router.goto('/workflow/' + meta.params.id + t.mode);
	}
}

afterUpdate( () => {
	active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];
});

</script>

{#if $auth['grants']['workflows-view']}

<TabBar tabs={tabs} let:tab bind:active>
	<Tab {tab} on:click={() => tab_change(tab)}>
		<Icon class="material-icons">{tab.icon}</Icon>
		<Label>{tab.label}</Label>
	</Tab>
</TabBar>

	<Route path="/view"> <WorkflowView /> </Route>

	{#if $auth['grants']['workflows-edit']}
		<Route path="/edit"> <WorkflowEdit /> </Route>
		<Route path="/config"> <WorkflowConfig /> </Route>
	{/if}

	<Route fallback> <WorkflowView /> </Route>

{:else}
    <AccessDenied />
{/if}


<style>

</style>