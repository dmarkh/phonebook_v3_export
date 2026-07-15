<script>

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';

import { getTask, getTaskMembers, getTaskGroups, getTaskInstitutions, getTaskFields } from '../utils/pnb-api.js';
import { convertTask } from '../utils/pnb-convert.js';

import { getMembers, getGroups, getInstitutions } from '../utils/pnb-api.js';
import { convertMembers, addInstitutionsToConvertedMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';
import { convertInstitutions } from '../utils/pnb-convert.js';
import { sortConvertedInstitutions } from '../utils/pnb-sort.js';
import { getEvents } from '../utils/pnb-api.js';
import { convertEvents } from '../utils/pnb-convert.js';
import { find_field_id } from '../utils/pnb-search.js';

import { task_id, auth } from '../store.js';
import { downloadTask } from '../utils/pnb-download.js';

let title = '', subtitle = '';

let members = false, tmembers = false, groups, tgroups = false, institutions = false, tinstitutions = false;
let members_fid, event_fid;
let member_cache = {};

const find_member = ( mid ) => {
    for ( const m of members ) {
        if ( m.id == mid ) { return m.name_first + ' ' + m.name_last; }
    }
    return 'N/A';
}

const findMember = ( id ) => {
    if ( member_cache[id] ) { return member_cache[id]; }
    for ( const member of members ) {
        if ( member.id == id ) {
            member_cache[id] = member;
            return member;
        }
    }
    return false;
}

const findGroup = ( id ) => {
    for ( const group of groups ) {
        if ( group.id == id ) {
            return group;
        }
    }
    return '';
}

const findInstitution = ( id ) => {
    for ( const institution of institutions ) {
        if ( institution.id == id ) {
            return institution;
        }
    }
    return '';
}

const findTMember = ( id ) => {
    for ( const member of tmembers ) {
        if ( member.id == id ) {
            return member.member;
        }
    }
    return '';
}

const find_event = ( eid ) => {
    for ( const e of events ) {
        if ( e.id == eid ) { return e.name; }
    }
    return 'N/A';
}

const fetchTask = async ( id ) => {

/*
    const mem = await getMembers();
    members = await convertMembers( mem );
    members = await addInstitutionsToConvertedMembers( members );
    sortConvertedMembers( members );
	tmembers = await getTaskMembers( id );

	groups = await getGroups();
	tgroups = await getTaskGroups( id );

	institutions = await getInstitutions();
    institutions = await convertInstitutions( institutions );

    sortConvertedInstitutions( institutions );
	tinstitutions = await getTaskInstitutions( id );
*/

	let data = [],
		i = await downloadTask( id );

    if ( id && i.ctask) {
        title = i.ctask.title || 'N/A';
    }

	for ( const id of i.task_fields_ordered ) {
		if ( i.task_fields[ id ].is_enabled != 'y' ) { continue; }
		if ( i.task_fields[ id ].privacy !== 'public' && !( $auth['role'] == 'ADMIN' || $auth['role'] == 'EDITOR' ) ) { continue; }

       	data.push({
           	id: parseInt(id),
            desc: i.task_fields[id].name_desc,
            value: i.ctask[ i.task_fields[id].name_fixed ] || ''
        });
    }

	return data;
}

const getTaskDates = ( v ) => {
    if ( !v.begin_time && !v.end_time ) {
        return ''; // not set
    } else if ( v.begin_time && v.end_time ) {
        return ( v.begin_time + ' - ' + v.end_time );
    } else if ( v.begin_time && !v.end_time ) {
        return ( v.begin_time + ' AND BEYOND' );
    } else if ( !v.begin_time && v.end_time ) {
        return ( 'UP TO ' + v.end_time);
    }
    return 'ERROR';
}

</script>

{#await fetchTask( $task_id )}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
<div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>

<Paper>
<DataTable table$aria-label="Task Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="field" style="width: 20%; text-align: left;">
                <Label>FIELD</Label>
            </Cell>
            <Cell columnId="value" style="width: 60%; text-align: left;">
                <Label>VALUE</Label>
            </Cell>
        </Row>
    </Head>
    <Body>

    {#each data as item (item.id)}
      <Row data-entry-id="{item.id}">
        <Cell style="width: 30%; font-weight: bold;">{item.desc}</Cell>
        <Cell style="width: 70%; font-weight: bold; white-space: normal;">{item.value}</Cell>
      </Row>
    {/each}

    </Body>
</DataTable>

{#if tmembers && tmembers.length}
<div style="text-align: center;" class="mdc-typography--headline4">Task Members</div>
<DataTable table$aria-label="Member Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="value" style="text-align: center;">
                <Label>DATES</Label>
            </Cell>
            <Cell columnId="value" style="text-align: center;">
                <Label>FTE</Label>
            </Cell>
            <Cell columnId="value" style="text-align: center;">
                <Label>NAME</Label>
            </Cell>
            <Cell columnId="group" style="text-align: center;">
                <Label>EMAIL</Label>
            </Cell>
            <Cell columnId="group" style="text-align: center;">
                <Label>INSTITUTION</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each tmembers as member (member.member_id)}
      <Row data-entry-id="{member.member_id}">
		<Cell style="text-align: center;">{ getTaskDates( member ) }</Cell>
        <Cell style="text-align: center;">{member.fte || 0}</Cell>
        <Cell style="text-align: center;">{findMember(member.member_id).name_first} {findMember(member.member_id).name_last}</Cell>
        <Cell style="text-align: center;">{findMember(member.member_id).email || ''}</Cell>
        <Cell style="text-align: center;">{findMember(member.member_id).institution__name_full || ''}</Cell>
      </Row>
    {/each}
    </Body>
</DataTable>
{/if}

{#if tgroups && tgroups.length}
<div style="text-align: center;" class="mdc-typography--headline4">Task Groups</div>
<DataTable table$aria-label="Group Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="fte" style="text-align: center;">
                <Label>DATES</Label>
            </Cell>
            <Cell columnId="fte" style="text-align: center;">
                <Label>FTE</Label>
            </Cell>
            <Cell columnId="group" style="text-align: center;">
                <Label>GROUP</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each tgroups as group ( group.group_id )}
      <Row data-entry-id="group">
		<Cell style="text-align: center;">{ getTaskDates( group ) }</Cell>
        <Cell style="text-align: center;">{group.fte}</Cell>
        <Cell style="text-align: center;">{findGroup( group.group_id ).name}</Cell>
      </Row>
    {/each}
    </Body>
</DataTable>
{/if}

{#if tinstitutions && tinstitutions.length}
<div style="text-align: center;" class="mdc-typography--headline4">Task Institutions</div>
<DataTable table$aria-label="Institution Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="fte" style="text-align: center;">
                <Label>DATES</Label>
            </Cell>
            <Cell columnId="fte" style="text-align: center;">
                <Label>FTE</Label>
            </Cell>
            <Cell columnId="institution" style="text-align: center;">
                <Label>INSTITUTION</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each tinstitutions as institution ( institution.institution_id )}
      <Row data-entry-id="institution">
		<Cell style="text-align: center;">{ getTaskDates( institution ) }</Cell>
        <Cell style="text-align: center;">{institution.fte}</Cell>
        <Cell style="text-align: center;">{findInstitution( institution.institution_id ).name_full}</Cell>
      </Row>
    {/each}
    </Body>
</DataTable>
{/if}

</Paper>

{/await}

<style>

</style>