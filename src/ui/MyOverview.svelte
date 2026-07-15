<script>

import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button from '@smui/button';

export let meta;
const mid = parseInt(window.pnb.mid);
import MyBio from './MyBio.svelte';
import MyGroups from './MyGroups.svelte';
import MyInstitution from './MyInstitution.svelte';
import MyDocuments from './MyDocuments.svelte';
import MyTasks from './MyTasks.svelte';

import { tran } from '../utils/tran.js';

let tabs = [
    { "label": "_my_bio_", "icon": "view_list", "mode": "/bio" },
    { "label": "_my_institution_", "icon": "view_list", "mode": "/institution" },
    { "label": "_my_groups_", "icon": "view_list", "mode": "/groups" },
    { "label": "_my_documents_", "icon": "view_list", "mode": "/documents" },
    { "label": "_my_tasks_", "icon": "view_list", "mode": "/tasks" }
];

let active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];

const tab_change = (t) => {
    if ( t ) {
        router.goto('/my-overview' + t.mode);
    }
}

afterUpdate( () => {
    active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];
});

</script>

{#if !mid}
	<div style="text-align: center; padding: 5vmin;">
		MEMBER NOT FOUND BY ORCID LOOKUP. PLEASE ASK YOUR REPRESENTATIVE TO UPDATE THE RECORD.
	</div>
{:else}

	<TabBar tabs={tabs} let:tab bind:active>
    	<Tab {tab} on:click={() => tab_change(tab)}>
        	<Icon class="material-icons">{tab.icon}</Icon>
	        <Label>{tran(tab.label)}</Label>
    	</Tab>
	</TabBar>

    <Route path="/bio"> <MyBio /> </Route>
    <Route path="/groups"> <MyGroups /> </Route>
    <Route path="/institution"> <MyInstitution /> </Route>
    <Route path="/documents"> <MyDocuments /> </Route>
    <Route path="/tasks"> <MyTasks /> </Route>

    <Route fallback> <MyBio /> </Route>

{/if}

