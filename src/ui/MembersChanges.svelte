<script>

import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

import AccessDenied from './AccessDenied.svelte';

import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button from '@smui/button';

import { auth } from '../store.js';

export let meta;

import MembersSummary from './MembersSummary.svelte';
import MembersHistory from './MembersHistory.svelte';

let tabs = [
    { "label": "summary", "icon": "view_list", "mode": "/summary" },
    { "label": "history", "icon": "view_list", "mode": "/history" }
];

let active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];

const tab_change = (t) => {
    if ( t ) {
        router.goto('/members-changes' + t.mode);
    }
}

afterUpdate( () => {
    active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];
});

</script>

{#if !$auth['grants']['members-changes']}

	<AccessDenied />

{:else}

	<TabBar tabs={tabs} let:tab bind:active>
    	<Tab {tab} on:click={() => tab_change(tab)}>
        	<Icon class="material-icons">{tab.icon}</Icon>
	        <Label>{tab.label}</Label>
    	</Tab>
	</TabBar>

    <Route path="/summary"> <MembersSummary /> </Route>
    <Route path="/history"> <MembersHistory /> </Route>

    <Route fallback> <MembersSummary /> </Route>

{/if}