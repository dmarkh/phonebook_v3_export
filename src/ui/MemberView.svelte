<script>

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';
import AccessDenied from './AccessDenied.svelte';

import { downloadMember } from '../utils/pnb-download.js';
import { member_id, auth } from '../store.js';
import { find_field_id } from '../utils/pnb-search.js';

let title = '', subtitle = '';

let extra_institutions_field_id = false,
	institutions = {},
	photo_field_id = false;

const fetchMember = async () => {
	let data = [];
	let m = await downloadMember( $member_id );

	extra_institutions_field_id = find_field_id( m.member_fields, 'extra_institution_id' );
	institutions = m.institution_ids_sorted.reduce( (acc,cv) => { acc[cv[0]] = cv[1]; return acc; }, {} );
    photo_field_id = find_field_id(m.member_fields, 'photo');

	title = m.cmember.name_first + ' ' + m.cmember.name_last;
	if ( m.cinstitution && m.cinstitution.name_full ) {
    	subtitle = m.cinstitution.name_full || 'INSTITUTION NOT SET';
	} else {
		subtitle = 'INSTITUTION NOT SET';
	}

	for ( const id of m.member_fields_ordered ) {
		if ( m.member_fields[id].is_enabled !== 'y' ) { continue; }
		if ( m.member_fields[id].privacy !== 'public' && !( $auth['role'] == 'ADMIN' || $auth['role'] == 'EDITOR' ) ) { continue; }

		if ( id === extra_institutions_field_id && m.cmember[ m.member_fields[id].name_fixed ] ) {
			let mval =  m.cmember[ m.member_fields[id].name_fixed ].split(',').map( fv => {
                    if ( fv ) {
                        return institutions[ Number(fv) ];
                    } else {
                        return '';
                    }
                }).join(', ');
			data.push({
				id: parseInt(id),
				desc: m.member_fields[id].name_desc,
				value: mval,
				group: m.member_groups[ m.member_fields[id].group ].name_full
			});
		} else {
			data.push({
				id: parseInt(id),
				desc: m.member_fields[id].name_desc,
				value: ( m.member_fields[id].name_fixed === 'institution_id' && m.cinstitution && m.cinstitution.name_full ) ? m.cinstitution.name_full : ( m.cmember[ m.member_fields[id].name_fixed ] || '' ),
				group: m.member_groups[ m.member_fields[id].group ].name_full
			});
		}
	}

	return data;
}

</script>

{#if !$auth['grants']['members-view-details']}
    <AccessDenied />
{:else}

{#await fetchMember()}

<LinearProgress indeterminate />

{:then data}

<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
{#if subtitle}
	<div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>
{/if}
<Paper>
<DataTable table$aria-label="Member Data" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="field" style="width: 20%; text-align: left;">
                <Label>FIELD</Label>
            </Cell>
            <Cell columnId="value" style="width: 60%; text-align: left;">
                <Label>VALUE</Label>
            </Cell>
            <Cell columnId="group" style="width: 20%; text-align: left;">
                <Label>GROUP</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each data as item (item.id)}
	  {#if item.id == photo_field_id && item.value}
	      <Row data-entry-id="{item.id}">
    	    <Cell style="width: 20%; font-weight: bold; white-space: normal;">{item.desc}</Cell>
        	<Cell style="width: 60%;  white-space: normal;"><img src="{item.value}" class="photoedit-image" /></Cell>
	        <Cell style="width: 20%;  white-space: normal;">{item.group}</Cell>
    	  </Row>
		{:else}
	      <Row data-entry-id="{item.id}">
    	    <Cell style="width: 20%; font-weight: bold;  white-space: normal;">{item.desc}</Cell>
        	<Cell style="width: 60%;  white-space: normal;">{item.value}</Cell>
	        <Cell style="width: 20%;  white-space: normal;">{item.group}</Cell>
    	  </Row>
		{/if}
    {/each}
    </Body>
</DataTable>
</Paper>

{/await}

{/if}

<style>

</style>