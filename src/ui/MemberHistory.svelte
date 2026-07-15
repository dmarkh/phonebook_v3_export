<script>

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';

import AccessDenied from './AccessDenied.svelte';

import { getMemberHistory } from '../utils/pnb-api.js';
import { getMember, getMemberFields } from '../utils/pnb-api.js';
import { convertMember, convertField } from '../utils/pnb-convert.js';
import { find_field_id } from '../utils/pnb-search.js';

import { getInstitution, getInstitutions, getInstitutionFields, getInstitutionFieldgroups } from '../utils/pnb-api.js';
import { convertInstitution } from '../utils/pnb-convert.js';

import { member_id } from '../store.js';
import { auth } from '../store.js';

let title = false, created = false, subtitle = false;
let extra_institutions_field_id = false,
	institution_name_field_id = false;

const getFieldId = ( name, fields ) => {
    let field = Object.values( fields ).find( f => f.name_fixed == name );
    if ( field ) { return field.id; }
    return false;
}

const downloadMember = async ( id ) => {
    let data = [];

    let mdata = await getMember( id );
	created = mdata.member.join_date;

    let mfields = await getMemberFields();
    let cmdata = await convertMember( mdata );
    let hmdata = await getMemberHistory( id );

	let inst = await getInstitutions({ all: true });
    let idata = await getInstitution( cmdata.institution_id );
    let ifields = await getInstitutionFields();
    let igroups = await getInstitutionFieldgroups();
    let cidata = await convertInstitution( idata );

	extra_institutions_field_id = find_field_id( mfields, 'extra_institution_id' );
	institution_name_field_id = find_field_id( ifields, 'name_full' );

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
			if ( extra_institutions_field_id && extra_institutions_field_id == v['members_fields_id'] ) {
				v['old_value'] = v['value_from_string'].split(',').map( fv => {
					if ( fv ) {
						return inst[ Number(fv) ].fields[ institution_name_field_id ];
					} else {
						return '';
					}
				}).join(', ');
				v['new_value'] = v['value_to_string'].split(',').map( fv => {
					if ( fv ) {
						return inst[ Number(fv) ].fields[ institution_name_field_id ];
					} else {
						return '';
					}
				}).join(', ');
			} else {
	            v['old_value'] = convertField( mfields[ v['members_fields_id'] ], v['value_from_string'] );
    	        v['new_value'] = convertField( mfields[ v['members_fields_id'] ], v['value_to_string'] );
			}
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
		}
    }

	title = cmdata.name_first + ' ' + cmdata.name_last;
    subtitle = cidata.name_full || 'INSTITUTION NOT SET';

    return hmdata;
}

</script>

{#if $auth['grants']['members-history']}

{#await downloadMember( $member_id )}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
{#if subtitle}
    <div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>
{/if}
{#if created}
    <div style="text-align: center; font-size: 80%;" class="mdc-typography--subtitle1"><i>member created: {created}</i></div>
{/if}
<Paper>
<DataTable table$aria-label="Member History Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="date" style="width: 16%; text-align: left;">
                <Label>DATE</Label>
            </Cell>
            <Cell columnId="field" style="width: 14%; text-align: left;">
                <Label>FIELD</Label>
            </Cell>
            <Cell columnId="old-value" style="width: 14%; text-align: left;">
                <Label>OLD VALUE</Label>
            </Cell>
            <Cell columnId="empty" style="width: 14%; text-align: left;">
                <Label></Label>
            </Cell>
            <Cell columnId="new-value" style="width: 14%; text-align: left;">
                <Label>NEW VALUE</Label>
            </Cell>
            <Cell columnId="metadata" style="width: 14%; text-align: left;">
                <Label>METADATA</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each data as item (item.id)}
      <Row>
        <Cell style="width: 16%; font-weight: bold; white-space: normal;">{item.date}</Cell>
        <Cell style="width: 14%; white-space: normal;">{item.field}</Cell>
        <Cell style="width: 14%; white-space: normal;">{item.old_value}</Cell>
        <Cell style="width: 14%; white-space: normal;"> => </Cell>
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
