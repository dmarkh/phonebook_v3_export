<script>

import { afterUpdate } from 'svelte';
import { meta, router, Route} from 'tinro';

import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';

import AccessDenied from './AccessDenied.svelte';
import EventPollsWhen2Meet from './EventPollsWhen2Meet.svelte';
import EventPollsDoodle from './EventPollsDoodle.svelte';

import { screen, auth } from '../store.js';

const route = meta();

const mid = window.pnb.mid ? parseInt(window.pnb.mid) : null;
$screen = 'new-meeting-poll';

let	tabs = [
		{ "label": "when2meet-like", "icon": "view_list", "mode": "/when2meet" },
		{ "label": "doodle-like", "icon": "view_list", "mode": "/doodle" }
	];

let active = tabs.find( t => route.url.endsWith( t.mode ) ) || tabs[0];

const tab_change = (t) => {
	if ( t ) {
		router.goto('/new-meeting-poll' + t.mode);
	}
}

afterUpdate( () => {
	active = tabs.find( t => route.url.endsWith( t.mode ) ) || tabs[0];
});

</script>

{#if ( $auth['grants']['event-poll-create'] )}
	<TabBar tabs={tabs} let:tab bind:active>
		<Tab {tab} on:click={() => tab_change(tab)}>
			<Icon class="material-icons">{tab.icon}</Icon>
			<Label>{tab.label}</Label>
		</Tab>
	</TabBar>

	<Route path="/when2meet"> <EventPollsWhen2Meet /> </Route>
	<Route path="/doodle"> <EventPollsDoodle /> </Route>
	<Route fallback> <EventPollsWhen2Meet /> </Route>

{:else}
	<AccessDenied />
{/if}

<style>

</style>