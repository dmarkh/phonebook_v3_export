<script>

import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';

import WorkflowBlockView from './WorkflowBlockView.svelte';
import WorkflowBlockEdit from './WorkflowBlockEdit.svelte';

import { auth } from '../store.js';
import { workflowblock_id, workflowblock_mode } from '../store.js';

export let meta;

let tabs = [
    { "label": "view", "icon": "view_list", "mode": "/view" },
];

if ( $auth['grants']['workflows-edit'] ) {
    tabs = [ ...tabs,
        { "label": "edit", "icon": "edit", "mode": "/edit" },
    ];
}

let active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];
if ( meta.params.id ) {
    $workflowblock_id = meta.params.id;
}

const tab_change = (t) => {
    if ( t ) {
        router.goto('/workflow-block/' + meta.params.id + t.mode);
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

    <Route path="/view"> <WorkflowBlockView /> </Route>
    <Route path="/edit"> <WorkflowBlockEdit /> </Route>
    <Route fallback> <WorkflowBlockView /> </Route>
