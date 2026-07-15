<script>

import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';

import EventHistory from './EventHistory.svelte';
import EventDocuments from './EventDocuments.svelte';
import EventEdit from './EventEdit.svelte';
import EventView from './EventView.svelte';
import EventNew from './EventNew.svelte';

import { screen, auth } from '../store.js';
import { event_id, event_mode } from '../store.js';

export let meta;

$screen = 'events';

let tabs = [
	{ "label": "view", "icon": "view_list", "mode": "/view" }
];

if ( $auth['grants']['events-edit'] ) {
	tabs = [ ...tabs,
		{ "label": "edit", "icon": "edit", "mode": "/edit" }
	];
}
if ( $auth['grants']['events-history'] ) {
	tabs = [ ...tabs,
		{ "label": "history", "icon": "history", "mode": "/history" }
	];
}

tabs.push({ "label": "documents", "icon": "document", "mode": "/documents" });

let active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];
if ( meta.params.id ) {
	$event_id = meta.params.id;
}

const tab_change = (t) => {
	if ( t ) {
		router.goto('/event/' + meta.params.id + t.mode);
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

	<Route path="/view"> <EventView /> </Route>
	<Route path="/edit"> <EventEdit /> </Route>
	<Route path="/history"> <EventHistory /> </Route>
	<Route path="/documents"> <EventDocuments /> </Route>

	<Route fallback> <EventView /> </Route>

<style>

</style>