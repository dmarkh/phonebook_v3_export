<script>

import {router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import Button, { Label as ButtonLabel } from '@smui/button';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';

import { getGroup, getGroups } from '../utils/pnb-api.js';

import { getMembers, getMemberFields, getGroupRoles } from '../utils/pnb-api.js';
import { convertMembers, addInstitutionsToConvertedMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';
import { invenio_search_community, invenio_create_community } from '../utils/pnb-graphql.js';

import { group_id, auth, member_mode, member_id, screen } from '../store.js';

let title = '';
let data = {};
let groups = false;
let members = false;
let roles = false;
let refresh = false;

let members_cache = {};
let groups_cache = {};

const handleRowClick = ( e ) => {
    $member_mode = 'view';
    $member_id = e.target.dataset.entryId;
    $screen = 'member';
    router.goto('/member/' + $member_id + '/view');
}

const handleSubGroupRowClick = async ( e ) => {
    $group_id = e.target.dataset.entryId;
    $screen = 'group';
    router.goto('/group/' + $group_id + '/view');
	refresh = !refresh;
}

const findRoleWeight = ( id ) => {
    for ( const role of roles ) {
        if ( role.id == id ) {
            return parseInt(role.weight);
        }
    }
    return 20;
}

const findRole = ( id ) => {
    for ( const role of roles ) {
        if ( role.id == id ) {
            return role.role;
        }
    }
    return '';
}

const findParentGroup = ( id ) => {
	if ( groups_cache[id] ) { return groups_cache[id].name };
	for ( const group of groups ) {
		if ( group.id == id ) {
			groups_cache[id] = group;
			return group.name;
		}
	}
	return '';
}

const findGroup = ( id ) => {
	if ( groups_cache[id] ) { return groups_cache[id]; };
	for ( const group of groups ) {
		if ( group.id == id ) {
			groups_cache[id] = group;
			return group;
		}
	}
	return false;
}

const findMember = ( id ) => {
	if ( members_cache[id] ) { return members_cache[id] };
	for ( const member of members ) {
		if ( member.id == id ) {
			members_cache[id] = member;
			return member;
		}
	}
	return false;
}

const fetchGroup = async () => {
	data = await getGroup( $group_id );
	if ( data.parent || data.groups.length ) {
		// fetch all groups for display purposes
		groups = await getGroups();
		data.groups = data.groups.filter( g => findGroup(g) );
	}

	if ( data.members.length ) {
		// fetch all members for display purposes
		let mem = await getMembers();
    	let items = await convertMembers( mem );
    	members = await addInstitutionsToConvertedMembers( items );
	    sortConvertedMembers( members );
		data.members = data.members.filter( m => findMember( m.member_id ) );
	}
	roles = await getGroupRoles( $group_id );
	data.members.sort( (a,b) => {
		const wa = findRoleWeight(a.role_id);
		const wb = findRoleWeight(b.role_id);
		if ( wa < wb ) {
			return -1;
		} else if ( wa > wb ) {
			return 1;
		} else {
			const na = findMember(a.member_id);
			const nb = findMember(b.member_id);
			if ( na && nb ) {
				if ( na.name_last < nb.name_last ) {
					return -1;
				} else if ( na.name_last > nb.name_last ) {
					return 1;
				} else {
					if ( na.name_first < nb.name_first ) {
						return -1;
					} else if ( na.name_first > na.name_first ) {
						return 1;
					}
				}
			}
		}
		return 20;
	});
	return data;
}

</script>

{#key refresh}
{#await fetchGroup()}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">Group: {data.name}</div>
<Paper>

<DataTable table$aria-label="Group Data" style="width: 100%;">
    <Body>
	{#if findParentGroup( data.parent )}
      <Row data-entry-id="group-parent">
   	    <Cell style="width: 30%; font-weight: bold;">PARENT GROUP</Cell>
       	<Cell style="width: 70%; ">{findParentGroup( data.parent )}</Cell>
   	  </Row>
	{/if}
      <Row data-entry-id="group-name">
   	    <Cell style="width: 30%; font-weight: bold;">NAME</Cell>
       	<Cell style="width: 70%;">{data.name} { data.category ? ( '( ' + data.category + ' )' ) : ''}</Cell>
   	  </Row>
      <Row data-entry-id="group-desc">
   	    <Cell style="width: 30%; font-weight: bold;">DESCRIPTION</Cell>
       	<Cell style="width: 70%; white-space: normal;">{data.desc}</Cell>
   	  </Row>
      <Row data-entry-id="group-desc">
   	    <Cell style="width: 30%; font-weight: bold;">EMAIL</Cell>
       	<Cell style="width: 70%;">{data.email}</Cell>
   	  </Row>
      <Row data-entry-id="group-url">
   	    <Cell style="width: 30%; font-weight: bold;">URL</Cell>
       	<Cell style="width: 70%;">
			{#if data.url}
                <Button color="secondary" variant="unelevated" href="{data.url}" target="_blank">
                    <ButtonLabel> {data.url} </ButtonLabel>
                </Button>
			{/if}
		</Cell>
   	  </Row>
	  <Row data-entry-id="group-invenio">
   	    <Cell style="width: 30%; font-weight: bold;">INVENIO</Cell>
       	<Cell style="width: 70%;">
		{#await invenio_search_community(data.name)}
			LOOKING UP COMMUNITY IN INVENIO, PLEASE WAIT
		{:then idata}
			{#if idata.invenioSearchCommunity == ''}
				{#await invenio_create_community(data.name)}
					CREATING COMMUNITY IN INVENIO, PLEASE WAIT
				{:then iidata}
	                <Button color="secondary" variant="unelevated" href="{iidata.invenioCreateCommunity}" target="_blank">
    	                <ButtonLabel> {iidata.invenioCreateCommunity} </ButtonLabel>
        	        </Button>
				{/await}
			{:else}
	            <Button color="secondary" variant="unelevated" href="{idata.invenioSearchCommunity}" target="_blank">
                    <ButtonLabel> {idata.invenioSearchCommunity} </ButtonLabel>
       	        </Button>
			{/if}
		{/await}
		</Cell>
	  </Row>
      <Row data-entry-id="group-privacy">
   	    <Cell style="width: 30%; font-weight: bold;">PRIVACY</Cell>
       	<Cell style="width: 70%;">{data.private == 'yes' ? 'PRIVATE' : 'PUBLIC'}</Cell>
   	  </Row>
    </Body>
</DataTable>

{#if data.groups.length}
<div style="text-align: center;" class="mdc-typography--headline4">Sub-Groups</div>
<DataTable table$aria-label="Subgroup Data" style="width: 100%;" on:SMUIDataTableRow:click={handleSubGroupRowClick}>
    <Head>
        <Row>
            <Cell columnId="value" style="text-align: center;">
                <Label>NAME</Label>
            </Cell>
            <Cell columnId="group" style="text-align: center;">
                <Label>EMAIL</Label>
            </Cell>
            <Cell columnId="group" style="text-align: center;">
                <Label>PRIVACY</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
	{#each data.groups as group_id ( group_id )}
      <Row data-entry-id="{group_id}">
   	    <Cell style="text-align: center;">{findGroup(group_id)['name']} { findGroup(group_id).category ? ( '( ' + findGroup(group_id).category + ')' ) : '' }</Cell>
       	<Cell style="text-align: center;">{findGroup(group_id)['email']}</Cell>
       	<Cell style="text-align: center;">{findGroup(group_id)['private'] == 'yes' ? 'PRIVATE' : 'PUBLIC'}</Cell>
   	  </Row>
	{/each}
    </Body>
</DataTable>
{/if}

{#if false && roles && roles.length}
<div style="text-align: center;" class="mdc-typography--headline4">GROUP ROLES</div>
<DataTable table$aria-label="Role Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="value" style="text-align: center;">
                <Label>NAME</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each roles as role ( role.id )}
      <Row data-entry-id="role">
        <Cell style="text-align: center;">{role.role}</Cell>
      </Row>
    {/each}
    </Body>
</DataTable>
{/if}

{#if $auth['grants']['group-members-view']}

{#if data.members.length}
<div style="text-align: center;" class="mdc-typography--headline4">Members</div>
<DataTable table$aria-label="Member Data" style="width: 100%;" on:SMUIDataTableRow:click={handleRowClick}>
    <Head>
        <Row>
            <Cell columnId="value" style="text-align: center;">
                <Label>ROLE</Label>
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
	{#each data.members as member (member.member_id)}
      <Row data-entry-id="{member.member_id}">
   	    <Cell style="text-align: center;">{findRole(member.role_id) || ''}</Cell>
   	    <Cell style="text-align: center;">{findMember(member.member_id).name_first} {findMember(member.member_id).name_last}</Cell>
       	<Cell style="text-align: center;">{findMember(member.member_id).email || ''}</Cell>
       	<Cell style="text-align: center;">{findMember(member.member_id).institution__name_full || ''}</Cell>
   	  </Row>
	{/each}
    </Body>
</DataTable>
{/if}

{/if}

</Paper>

{/await}
{/key}

<style>

</style>