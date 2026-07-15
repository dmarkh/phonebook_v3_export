<script>

import LinearProgress from '@smui/linear-progress';
import Paper, { Content } from '@smui/paper';
import Radio from '@smui/radio';
import FormField from '@smui/form-field';
import Select, { Option } from '@smui/select';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';
import IconButton from '@smui/icon-button';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';
import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';

import FileSaver from '../vendor/FileSaver.js';
import { s2ab } from '../utils/s2ab.js';
import * as XLSX from 'xlsx';

import { getMembers, getMemberFields, getMemberFieldgroups, getInstitutionFields, getInstitutionFieldgroups } from '../utils/pnb-api.js';
import { convertMembers, addInstitutionsToConvertedMembers } from '../utils/pnb-convert.js';
import { orderKeys } from '../utils/pnb-download.js';
import { find_field_id } from '../utils/pnb-search.js';

import { screen, member_id } from '../store.js';

let showFiltered = false, showSpinner = false;
let allany = 'all';

let members = false,
	converted_members = false,
    filtered_members = false,
	fields = false,
	fieldgroups = false,
	fields_ordered = false,
	ifields = false,
	ifieldgroups = false,
	ifields_ordered = false;

let field_ids = {},
	field_names = {},
	ifield_ids = {},
	ifield_names = {};

let filter_options = [
	{ "option": "all", "label": "Match ALL criteria" },
	{ "option": "any", "label": "Match ANY criteria" }
];

let filters = [
	{ "field": "name_last", "op": "notempty", "value": "" }
];

let operations = [
    { "name": "equals to", "value": "equals" },
    { "name": "does not equal to", "value": "notequals" },
    { "name": "contains", "value": "contains" },
    { "name": "does not contain", "value": "notcontains" },
    { "name": "starts with", "value": "startswith" },
    { "name": "ends with", "value": "endswith" },
    { "name": "is empty", "value": "empty" },
    { "name": "is not empty", "value": "notempty" }
];

let display_fields = JSON.parse(JSON.stringify(window.pnb['filter-members']['display-fields'])),
	sort_fields = JSON.parse(JSON.stringify(window.pnb['filter-members']['sort-fields']));

const remove_filter = (idx) => {
	if ( idx >= 0 ) {
		filters.splice(idx, 1);
		filters = filters;
	}
}

const add_filter = (idx) => {
	filters = [ ...filters, { "field": "name_last", "op": "notempty", "value": "" } ];
}

const remove_display_field = (idx) => {
	if ( idx >= 0 ) {
		display_fields.splice(idx, 1);
		display_fields = display_fields;
	}
}

const add_display_field = (idx) => {
	display_fields = [ ...display_fields, "name_last" ];
}

const remove_sort_field = (idx) => {
	if ( idx >= 0 ) {
		sort_fields.splice(idx, 1);
		sort_fields = sort_fields;
	}
}

const add_sort_field = (idx) => {
	sort_fields = [ ...sort_fields, "name_last" ];
}

const apply_filters = async () => {
	showSpinner = true;
	showFiltered = true;

    filtered_members = [];
    for ( const m of converted_members ) {
        let pass = false, cpass;
        for ( const f of filters ) {
            switch( f.op ) {
                case 'empty':
                    cpass = m[f.field] === undefined || ( m[f.field] !== undefined && typeof m[f.field] === 'string' && m[f.field].length == 0 );
                    break;
                case 'notempty':
                    cpass =  m[f.field] !== undefined && typeof m[f.field] === 'string' && m[f.field].length != 0;
                    break;
                case 'equals':
                    cpass = m[f.field] !== undefined && (
                        ( typeof m[f.field] !== 'string' && m[f.field] == f.value )
                            ||
                        ( typeof m[f.field] === 'string' && m[f.field].toLowerCase() == f.value.toLowerCase() ) );
                    break;
                case 'notequals':
                    cpass = m[f.field] !== undefined && (
                        ( typeof m[f.field] !== 'string' && m[f.field] != f.value )
                            ||
                        ( typeof m[f.field] === 'string' && m[f.field].toLowerCase() != f.value.toLowerCase() ) );
                    break;
                case 'contains':
                    cpass = m[f.field] !== undefined && typeof m[f.field] === 'string' && m[f.field].toLowerCase().includes( f.value.toLowerCase() );
                    break;
                case 'notcontains':
                    cpass = m[f.field] !== undefined && typeof m[f.field] === 'string' && !m[f.field].toLowerCase().includes( f.value.toLowerCase() );
                    break;
                case 'startswith':
                    cpass = m[f.field] !== undefined && typeof m[f.field] === 'string' && m[f.field].toLowerCase().startsWith( f.value.toLowerCase() );
                    break;
                case 'endswith':
                    cpass = m[f.field] !== undefined && typeof m[f.field] === 'string' && m[f.field].toLowerCase().endsWith( f.value.toLowerCase() );
                    break;
                default:
                    console.log('ERROR, unknown filter: ' + f.op );
                    break;
            }
            pass = pass || cpass;
            if ( allany === 'all' && cpass === false ) {
                pass = false; break;
            } else if ( allany === 'any' && cpass === true ) {
                pass = true; break;
                break;
            }
        }
        if ( pass === true ) {
            filtered_members.push( m );
        }
    }

    filtered_members.sort( function (a,b) {
        for ( const f of sort_fields ) {
            if ( a[f] === undefined && b[f] !== undefined ) { return 1; }
            else if ( b[f] === undefined && a[f] !== undefined ) { return -1; }
            else if ( a[f] === undefined && b[f] === undefined ) { return 0; }
			if ( typeof a[f] === 'number' ) {
				if ( a[f] < b[f] ) { return -1; }
				else if ( a[f] > b[f] ) { return 1; }
			} else if ( typeof a[f] === 'string' ) {
				if ( a[f].toString().toLowerCase() < b[f].toString().toLowerCase() ) { return -1; }
				else if ( a[f].toString().toLowerCase() > b[f].toString().toLowerCase() ) { return 1; }
			}
        }
        return 0;
    });

    showSpinner = false;
}

let init = async () => {
	members = await getMembers();
	fields = await getMemberFields();
	fieldgroups = await getMemberFieldgroups();
	ifields = await getInstitutionFields();
	ifieldgroups = await getInstitutionFieldgroups();
	for( const [k,v] of Object.entries(fields) ) {
		field_ids[ v.name_fixed ] = v.id;
		field_names[ v.id ] = v.name_fixed;
	}
	for( const [k,v] of Object.entries(ifields) ) {
		ifield_ids[ 'institution__' + v.name_fixed ] = v.id;
		ifield_names[ v.id ] = v.name_fixed;
	}

	fields_ordered = orderKeys( fields, (a,b) => a.group == b.group ? ( a.weight - b.weight ) : fieldgroups[a.group].weight - fieldgroups[b.group].weight );
	ifields_ordered = orderKeys( ifields, (a,b) => a.group == b.group ? ( a.weight - b.weight ) : ifieldgroups[a.group].weight - ifieldgroups[b.group].weight );
    converted_members = await convertMembers( members, fields );
	await addInstitutionsToConvertedMembers( converted_members, ifields );
}

const handleRowClick = ( e ) => {
    $member_id = e.target.dataset.entryId;
    $screen = 'member';
    router.goto('/member/' + $member_id + '/view');
}

const exportToExcel = ( data ) => {
    var ws = XLSX.utils.aoa_to_sheet( data ),
        ws_name = window.pnb.xlsx['members-export'];
    var wb = XLSX.utils.book_new();
    wb.SheetNames.push(ws_name);
    wb.Sheets[ws_name] = ws;
    var wbout = XLSX.write(wb, {bookType:'xlsx', bookSST:true, type: 'binary'});
    saveAs( new Blob([s2ab(wbout)],{type:"application/octet-stream"}), ws_name + '-' + ( Date.now() / 1000 | 0 )+".xlsx" );
}

const prepareForExcel = () => {
    let data = [];
    for( const v of filtered_members ) {
        let row = [];
        for ( const f of display_fields ) {
            row.push( v[ f ] );
        }
        data.push( row );
    }
    return exportToExcel( data );
}

</script>

{#if showFiltered == false}

{#await init()}
	<LinearProgress indeterminate />
{:then}
<div style="text-align: center;" class="mdc-typography--headline4">FILTER MEMBERS</div>
<Paper>
<p>FILTERS</p>
<div>
  {#each filter_options as option}
    <FormField>
      <Radio bind:group={allany} value={option.option} touch />
      <span slot="label">{option.label}</span>
    </FormField>
  {/each}
</div>

<div>
<table style="width: 100%;">
{#each filters as filter, i (i)}
<tr>
<td>
    <Select bind:value={filters[i].field} label="FIELD" variant="outlined" style="width: 100%;">
      {#each fields_ordered as field_id}
		<Option value={fields[field_id].name_fixed}>M: {fields[field_id].name_desc}</Option>
      {/each}
      {#each ifields_ordered as field_id}
		<Option value={'institution__' + ifields[field_id].name_fixed}>I: {ifields[field_id].name_desc}</Option>
      {/each}
    </Select>
</td>
<td>
    <Select bind:value={filters[i].op} label="OPERATION" variant="outlined" style="width: 100%;">
      {#each operations as operation}
        <Option value={operation.value}>{operation.name}</Option>
      {/each}
    </Select>
</td>
<td>
    <Textfield bind:value={filters[i].value} label="VALUE" variant="outlined" style="width: 100%;"/>
</td>
<td style="width: 5%;">
	<IconButton class="material-icons" on:click={() => { add_filter(i) }} size="button">add</IconButton>
</td>
<td style="width: 5%;">
	<IconButton class="material-icons" on:click={() => { remove_filter(i); }} size="button">remove</IconButton>
</td>
</tr>
{/each}
</table>
</div>

</Paper>
<br />
<Paper>
<table style="width: 100%;">
<tr>
<td style="width: 50%; vertical-align: top;">
<p>DISPLAY FIELDS</p>
<table style="width: 100%;">
{#each display_fields as field, i (i)}
<tr>
<td>
    <Select bind:value={field} label="FIELD" variant="outlined" style="width: 100%;">
	  <Option value="id">M: ID</Option>
      {#each fields_ordered as field_id}
        <Option value={fields[field_id].name_fixed}>M: {fields[field_id].name_desc}</Option>
      {/each}
      {#each ifields_ordered as field_id}
        <Option value={'institution__' + ifields[field_id].name_fixed}>I: {ifields[field_id].name_desc}</Option>
      {/each}
    </Select>
</td>
<td style="width: 5%;">
	<IconButton class="material-icons" on:click={() => { add_display_field(i) }} size="button">add</IconButton>
</td>
<td style="width: 5%;">
	<IconButton class="material-icons" on:click={() => { remove_display_field(i); }} size="button">remove</IconButton>
</td>
</tr>
{/each}
</table>

</td>
<td style="width: 50%; vertical-align: top;">
<p>SORT FIELDS (ORDER MATTERS)</p>
<table style="width: 100%;">
{#each sort_fields as field, i (i)}
<tr>
<td>
    <Select bind:value={field} label="FIELD" variant="outlined" style="width: 100%;">
		<Option value="id">M: ID</Option>
      {#each fields_ordered as field_id}
        <Option value={fields[field_id].name_fixed}>M: {fields[field_id].name_desc}</Option>
      {/each}
      {#each ifields_ordered as field_id}
        <Option value={'institution__' + ifields[field_id].name_fixed}>I: {ifields[field_id].name_desc}</Option>
      {/each}
    </Select>
</td>
<td style="width: 5%;">
	<IconButton class="material-icons" on:click={() => { add_sort_field(i) }} size="button">add</IconButton>
</td>
<td style="width: 5%;">
	<IconButton class="material-icons" on:click={() => { remove_sort_field(i); }} size="button">remove</IconButton>
</td>
</tr>
{/each}
</table>

</td>
</tr>
</table>

</Paper>

<div class="apply-filters-button">
	<Fab color="primary" on:click={() => { apply_filters(); }} extended>
		<FabIcon class="material-icons">search</FabIcon>
    	<FabLabel>APPLY FILTERS</FabLabel>
	</Fab>
</div>

{/await}

{:else}

{#if showSpinner == true}
	<LinearProgress indeterminate />
{:else}

<div style="text-align: center;" class="mdc-typography--headline4">FILTERED MEMBERS:</div>

<Paper>
<DataTable table$aria-label="Member List" style="width: 100%;"
  sortable
  on:SMUIDataTableRow:click={handleRowClick}
>
    <Head>
        <Row>
            {#each display_fields.map( df => {
                if ( df === 'id' ) {
                    return {"title": "ID", field: df, "align": "left", "width": "unset" };
				} else if ( df.includes('__') ) {
                    return { "title": ifields[ ifield_ids[df] ].name_desc, "field": df, "align": "left", "width": "unset" };
                } else {
                    return { "title": fields[ field_ids[df] ].name_desc, "field": df, "align": "left", "width": "unset" };
                }}) as mem }
            <Cell columnId="name_full" style="text-align: {mem.align}; width: {mem.width};">
                <Label>{mem.title || ''}</Label>
                <IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            {/each}
        </Row>
    </Head>
    <Body>
    {#each filtered_members as item (item.id)}
      <Row data-entry-id="{item.id}">
        {#each display_fields.map( df => {
             return { "title": df, "field": df, "align": "left", "width": "unset" };
            }) as mem }
        <Cell style="text-align: {mem.align}; width: {mem.width};">
            {#if mem.field === 'country' && item['country_code']}
            <img src="images/flags_iso_3166/24/{item['country_code'].toLowerCase()}.png" style="vertical-align: text-bottom;"/>
            {/if}
            {item[ mem.field ] || ''}
        </Cell>
        {/each}
      </Row>
    {/each}
    </Body>
</DataTable>
</Paper>


<div class="save-button">
    <Fab color="primary" on:click={() => { prepareForExcel(); }} extended>
        <FabIcon class="material-icons">save</FabIcon>
        <FabLabel>EXPORT TO EXCEL</FabLabel>
    </Fab>
</div>
<div class="clear-button">
    <Fab color="primary" on:click={() => { showFiltered = false; }} extended>
        <FabIcon class="material-icons">arrow_back</FabIcon>
        <FabLabel>BACK TO FILTERS</FabLabel>
    </Fab>
</div>

{/if}

{/if}

<style>
.apply-filters-button {
    position: absolute;
    bottom: 2vmin;
    right: 2vmin;
}
.save-button {
    position: absolute;
    bottom: 2vmin;
    right: 2vmin;
}
.clear-button {
    position: absolute;
    bottom: 10vmin;
    right: 2vmin;
}
</style>