<script>

import { tran } from '../utils/tran.js';

import { onMount } from 'svelte';
import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';

import DocumentHistory from './DocumentHistory.svelte';
import DocumentWorkflow from './DocumentWorkflow.svelte';
import DocumentView from './DocumentView.svelte';
import DocumentComments from './DocumentComments.svelte';
import DocumentEdit from './DocumentEdit.svelte';
import DocumentNew from './DocumentNew.svelte';
import DocumentReview from './DocumentReview.svelte';

import { auth } from '../store.js';
import { screen, document_id, document_mode } from '../store.js';

import { downloadDocument } from '../utils/pnb-download.js';

export let meta;

$screen = 'documents';
const mid = window.pnb.mid ? parseInt(window.pnb.mid) : null;

let tabs = [
	{ "label": "_view_", "icon": "view_list", "mode": "/view" },
];

if ( $auth['grants']['comments-view'] ) {
	tabs = [ ...tabs,
		{ "label": "_comments_", "icon": "edit", "mode": "/comments" }
	];
}

if ( $auth['grants']['documents-edit'] ) {
	tabs = [ ...tabs,
		{ "label": "_edit_", "icon": "edit", "mode": "/edit" }
	];
}
if ( $auth['grants']['documents-history'] ) {
	tabs = [ ...tabs,
		{ "label": "_history_", "icon": "history", "mode": "/history" }
	];
}
if ( $auth['grants']['workflows-view'] ) {
	tabs = [ ...tabs,
		{ "label": "_workflow_", "icon": "view_timeline", "mode": "/workflow" }
	];
}

if ( meta.url.endsWith( '/review' ) ) {
	tabs = [ ...tabs,
		{ "label": "_review_", "icon": "rate_review", "mode": "/review" }
	];
}

let active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];

if ( meta.params.id ) {
	$document_id = meta.params.id;
}

const tab_change = ( t ) => {
	if ( t ) {
		router.goto('/document/' + meta.params.id + t.mode);
	}
}

afterUpdate( () => {
	active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];
});

onMount(async () => {
});

</script>

<TabBar tabs={tabs} let:tab bind:active>
	<Tab {tab} on:click={() => tab_change(tab)}>
		<Icon class="material-icons">{tab.icon}</Icon>
		<Label>{tran(tab.label)}</Label>
	</Tab>
</TabBar>

	<Route path="/view"> <DocumentView /> </Route>
	<Route path="/comments"> <DocumentComments /> </Route>
	<Route path="/edit"> <DocumentEdit /> </Route>
	<Route path="/workflow"> <DocumentWorkflow /> </Route>
	<Route path="/history"> <DocumentHistory /> </Route>
	<Route path="/review"> <DocumentReview /> </Route>

	<Route fallback> <DocumentView /> </Route>

<style>

</style>