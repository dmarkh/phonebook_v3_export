<script>

import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';

import MemberEditFieldgroup from './MemberEditFieldgroup.svelte';
import MemberViewFieldgroup from './MemberViewFieldgroup.svelte';

export let meta;
let field_id = meta.params.id;

let tabs = [
    { "label": "view", "icon": "view_list", "mode": "/view" },
    { "label": "edit", "icon": "edit", "mode": "/edit" }
];

let active = tabs.find( t => meta.url.endsWith( t.mode ) ) || tabs[0];

const tab_change = (t) => {
    if ( t ) {
        router.goto('/member-fieldgroup/' + meta.params.id + t.mode);
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

    <Route path="/view"> <MemberViewFieldgroup {meta} /> </Route>
    <Route path="/edit"> <MemberEditFieldgroup {meta} /> </Route>
    <Route fallback> <MemberViewFieldgroup {meta} /> </Route>

<style>

</style>