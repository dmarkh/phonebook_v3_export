<script>

import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';
import { tran } from '../utils/tran.js';

import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';

import { screen, auth } from '../store.js';
import { member_id, member_mode } from '../store.js';
import { aitoolkit } from '../utils/ai-toolkit.js';

import AiAskScientist from './AiAskScientist.svelte';
import AiAskCoder from './AiAskCoder.svelte';
import AiDocSearch from './AiDocSearch.svelte';

export let meta;

console.log('aitoolkit', aitoolkit);
$screen = 'aitools';

let tabs = [
    { "label": '_ask_a_scientist_', "icon": "view_list", "mode": "/ask-scientist" },
    { "label": '_ask_a_coder_', "icon": "view_list", "mode": "/ask-coder" },
    { "label": '_search_documents_', "icon": "view_list", "mode": "/search-docs" }
];

let active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];

const tab_change = (t) => {
	if ( t ) {
	    router.goto('/ai-tools' + t.mode);
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

    <Route path="/ask-scientist"> <AiAskScientist aitoolkit={aitoolkit} /> </Route>
    <Route path="/ask-coder"> <AiAskCoder aitoolkit={aitoolkit} /> </Route>
    <Route path="/search-docs">
		{#if window.pnb.ai.search}
			<AiDocSearch />
		{:else}
			<p style="text-align: center; font-weight: bold; font-size: 150%; margin: 5vmin;">FEATURE DISABLED</p>
		{/if}
	</Route>
    <Route fallback> <AiAskScientist aitoolkit={aitoolkit} /> </Route>

<style>

</style>