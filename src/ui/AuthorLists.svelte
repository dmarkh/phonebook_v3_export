<script>

import { tran } from '../utils/tran.js';

import { afterUpdate } from 'svelte';
import {router,Route} from 'tinro';

import Fab from '@smui/fab';
import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';
import Textfield from '@smui/textfield';

import AccessDenied from './AccessDenied.svelte';
import AuthorListsAPS from './AuthorListsAPS.svelte';
import AuthorListsARXIV from './AuthorListsARXIV.svelte';
import AuthorListsIOP from './AuthorListsIOP.svelte';
import AuthorListsInspire from './AuthorListsInspire.svelte';
import AuthorListsPTDR from './AuthorListsPTDR.svelte';

import { auth } from '../store.js';

export let meta;

let unix_ts = false;

let tabs = [
    { "label": "_latex_aps_format_", "icon": "view_list", "mode": "/aps" },
    { "label": "_latex_iop_format_", "icon": "view_list", "mode": "/iop" },
    { "label": "_arxiv_format_", "icon": "view_list", "mode": "/arxiv" },
    { "label": "_inspire_xml_format_", "icon": "view_list", "mode": "/inspire" },
    { "label": "_ptdr_format_", "icon": "view_list", "mode": "/ptdr" }
];

let active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];
const tab_change = (t) => {
	if ( t ) {
		router.goto( '/authorlists' + t.mode );
	}
}

afterUpdate( () => {
    active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];
});

</script>

{#if $auth['grants']['authorlists-view']}
<TabBar tabs={tabs} let:tab bind:active>
    <Tab {tab} on:click={() => tab_change(tab)}>
        <Icon class="material-icons">{tab.icon}</Icon>
        <Label>{tran(tab.label)}</Label>
    </Tab>
</TabBar>

{#key unix_ts}
<Paper>
	<Route path="/aps"> <AuthorListsAPS {unix_ts} /> </Route>
	<Route path="/arxiv"> <AuthorListsARXIV {unix_ts} /> </Route>
	<Route path="/iop"> <AuthorListsIOP {unix_ts} /> </Route>
	<Route path="/inspire"> <AuthorListsInspire {unix_ts} /> </Route>
	<Route path="/ptdr"> <AuthorListsPTDR {unix_ts} /> </Route>
	<Route fallback> <AuthorListsAPS {unix_ts} /> </Route>
</Paper>
{/key}

	<div class="ts-button">
		<Fab color="primary" extended>
    		<Textfield
		      bind:value={unix_ts}
		      label="AuthorList Date"
		      type="date"
			  class="ts-textfield"
		    />
		</Fab>
	</div>

{:else}
	<AccessDenied />
{/if}

<style>
.ts-button {
    position: absolute;
    bottom: 2vmin;
    left: 2vmin;
}

:global(.ts-textfield .mdc-floating-label) {
    color: #FFF !important;
}
:global(.ts-textfield .mdc-text-field__input) {
    color: #FFF !important;
}

</style>