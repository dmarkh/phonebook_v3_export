<script>

import {router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';

import AccessDenied from './AccessDenied.svelte';

import { getMembersHistory } from '../utils/pnb-api.js';
import { getMembers, getMemberFields, getInstitutions, getInstitutionFields } from '../utils/pnb-api.js';

import { find_field_id } from '../utils/pnb-search.js';
import { convertField } from '../utils/pnb-convert.js';

import { screen, member_id, member_mode } from '../store.js';
import { auth } from '../store.js';

let title = false, subtitle = false;
let extra_institutions_field_id = false,
	institution_name_field_id = false;

let mem, mfields, members;
let inst, ifields;
let m_name_first_id, m_name_last_id;

const handleRowClick = ( e ) => {
    $member_mode = 'view';
    $member_id = e.target.dataset.entryId;
    $screen = 'member';
    router.goto('/member/' + $member_id + '/view');
}

const getFieldId = ( name, fields ) => {
    let field = Object.values( fields ).find( f => f.name_fixed == name );
    if ( field ) { return field.id; }
    return false;
}

const trim_ellipsis = function( str, length ) {
  return str.length > length ? str.substring(0, length) + "..." : str;
}

const getMemberName = ( mid ) => {
	if ( !mem[mid] ) { return 'N/A'; }
	return trim_ellipsis( mem[mid].fields[ m_name_first_id ] + ' ' + mem[mid].fields[ m_name_last_id ], 50 );
}

const downloadHistory = async ( id ) => {
    let data = [];

    let hmdata = await getMembersHistory();

	mem = await getMembers();
	mfields = await getMemberFields();

	m_name_first_id = getFieldId( 'name_first', mfields );
	m_name_last_id  = getFieldId( 'name_last', mfields );

    inst = await getInstitutions();
    ifields = await getInstitutionFields();

    let idctr = 0;
    for ( const [k,v] of Object.entries(hmdata) ) {
        v['id'] = ++idctr;
		if ( !v['members_fields_id'] || !mfields[ v['members_fields_id'] ] ) {
			v['field'] = 'deactive field';
			v['old_value'] = 'N/A';
			v['new_value'] = 'N/A';
			continue;
		}
        v['field'] = mfields[ v['members_fields_id'] ]['name_desc'] || '';
        if ( v['value_to_string'] || v['value_from_string'] ) {
            v['old_value'] = convertField( mfields[ v['members_fields_id'] ], v['value_from_string'] );
   	        v['new_value'] = convertField( mfields[ v['members_fields_id'] ], v['value_to_string'] );
        } else if (
            ( v['value_from_date'] && v['value_from_date'] != '0000-00-00 00:00:00' && v['value_from_date'] != '0000-00-00' )
            || ( v['value_to_date'] && v['value_to_date'] != '0000-00-00 00:00:00' && v['value_to_date'] != '0000-00-00' ) ) {
            v['old_value'] = v['value_from_date'];
            v['new_value'] = v['value_to_date'];
        } else {
            v['old_value'] = v['value_from_int'];
            v['new_value'] = v['value_to_int'];
        }
		if ( mfields[ v['members_fields_id'] ]['name_fixed'] === 'institution_id' ) {
			// convert id to the Institution Name Full
			let inst_field_id = getFieldId('name_full', ifields );
			if ( inst_field_id && inst[ Number(v['old_value']) ] && inst[ Number(v['new_value']) ] ) {
				v['old_value'] = Number(v['old_value']) ? inst[ Number(v['old_value']) ].fields[inst_field_id] : '';
				v['new_value'] = Number(v['new_value']) ? inst[ Number(v['new_value']) ].fields[inst_field_id] : '';
			}
		} else if ( mfields[ v['members_fields_id'] ]['name_fixed'] === 'extra_institution_id' ) {
			let inst_field_id = getFieldId('name_full', ifields );
			if ( inst_field_id ) {
				if ( !inst[ Number(v['old_value']) ] ) {
					v['old_value'] = 'not set';
				}
				if ( !inst[ Number(v['new_value']) ] ) {
					v['new_value'] = 'not set';
				}
			}
		}
    }

	title = 'LATEST CHANGES: MEMBERS';
    subtitle = 'LAST 1000 ENTRIES';

    return hmdata;
}

</script>

{#if $auth['grants']['members-history']}

{#await downloadHistory()}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
{#if subtitle}
    <div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>
{/if}
<Paper>
<DataTable table$aria-label="Members History Data" style="width: 100%;" on:SMUIDataTableRow:click={handleRowClick} >
    <Head>
        <Row>
            <Cell columnId="date" style="text-align: left;">
                <Label>DATE</Label>
            </Cell>
            <Cell columnId="field" style="text-align: left;">
                <Label>MEMBER NAME</Label>
            </Cell>
            <Cell columnId="field" style="text-align: left;">
                <Label>FIELD</Label>
            </Cell>
            <Cell columnId="old-value" style="text-align: left;">
                <Label>OLD VALUE</Label>
            </Cell>
            <Cell columnId="empty" style="text-align: left;">
                <Label></Label>
            </Cell>
            <Cell columnId="new-value" style="text-align: left;">
                <Label>NEW VALUE</Label>
            </Cell>
            <Cell columnId="metadata" style="text-align: left;">
                <Label>METADATA</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each data as item (item.id)}
      <Row data-entry-id="{item.members_id}">
        <Cell style="width: 16%; font-weight: bold;  white-space: normal;">{item.date}</Cell>
        <Cell style="width: 14%; white-space: normal;">{getMemberName(item.members_id)}</Cell>
        <Cell style="width: 14%; white-space: normal;">{item.field}</Cell>
        <Cell style="width: 14%; white-space: normal;">{item.old_value}</Cell>
        <Cell style="width: 14%;"> => </Cell>
        <Cell style="width: 14%; white-space: normal;">{item.new_value}</Cell>
        <Cell style="width: 14%; white-space: normal;">{item.user} {item.ip}</Cell>
      </Row>
    {/each}
    </Body>
</DataTable>
</Paper>
{/await}

{:else}
	<AccessDenied />
{/if}
