<script>

import {router, Route} from 'tinro';

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

import { getEvents, getEventFields } from '../utils/pnb-api.js';
import { convertEvents } from '../utils/pnb-convert.js';
import { orderKeys } from '../utils/pnb-download.js';
import { find_field_id } from '../utils/pnb-search.js';

import FileSaver from '../vendor/FileSaver.js';
import { s2ab } from '../utils/s2ab.js';
import * as XLSX from 'xlsx';

import { screen, event_id } from '../store.js';

let showFiltered = false, showSpinner = false;
let allany = 'all';

let events = false,
	converted_events = false,
	filtered_events = false,
	fields = false,
	fields_ordered = false;

let field_ids = {},
	field_names = {};

let filter_options = [
	{ "option": "all", "label": "Match ALL criteria" },
	{ "option": "any", "label": "Match ANY criteria" }
];

let filters = [
	{ "field": "name_full", "op": "notempty", "value": "" }
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

let display_fields = JSON.parse(JSON.stringify( window.pnb['filter-events']['display-fields'] )),
	sort_fields = JSON.parse(JSON.stringify( window.pnb['filter-events']['sort-fields'] ));

const remove_filter = (idx) => {
	if ( idx >= 0 ) {
		filters.splice(idx, 1);
		filters = filters;
	}
}

const add_filter = (idx) => {
	filters = [ ...filters, { "field": "name_full", "op": "notempty", "value": "" } ];
}

const remove_display_field = (idx) => {
	if ( idx >= 0 ) {
		display_fields.splice(idx, 1);
		display_fields = display_fields;
	}
}

const add_display_field = (idx) => {
	display_fields = [ ...display_fields, "name_full" ];
}

const remove_sort_field = (idx) => {
	if ( idx >= 0 ) {
		sort_fields.splice(idx, 1);
		sort_fields = sort_fields;
	}
}

const add_sort_field = (idx) => {
	sort_fields = [ ...sort_fields, "name_full" ];
}

const apply_filters = async () => {
	showSpinner = true;
	showFiltered = true;
	filtered_events = [];
	for ( const i of converted_events ) {
		let pass = false, cpass;
		for ( const f of filters ) {
			switch( f.op ) {
				case 'empty':
					cpass = i[f.field] === undefined || ( i[f.field] && typeof i[f.field] === 'string' && i[f.field].length == 0 );
					break;
				case 'notempty':
					cpass =  i[f.field] !== undefined && typeof i[f.field] === 'string' && i[f.field].length != 0;
					break;
				case 'equals':
					cpass = i[f.field] !== undefined && (
						( typeof i[f.field] !== 'string' && i[f.field] == f.value )
							||
						( typeof i[f.field] === 'string' && i[f.field].toLowerCase() == f.value.toLowerCase() ) );
					break;
				case 'notequals':
					cpass = i[f.field] !== undefined && (
						( typeof i[f.field] !== 'string' && i[f.field] != f.value )
							||
						( typeof i[f.field] === 'string' && i[f.field].toLowerCase() != f.value.toLowerCase() ) );
					break;
				case 'contains':
					cpass = i[f.field] !== undefined && typeof i[f.field] === 'string' && i[f.field].toLowerCase().includes( f.value.toLowerCase() );
					break;
				case 'notcontains':
					cpass = i[f.field] !== undefined && typeof i[f.field] === 'string' && !i[f.field].toLowerCase().includes( f.value.toLowerCase() );
					break;
				case 'startswith':
					cpass = i[f.field] !== undefined && typeof i[f.field] === 'string' && i[f.field].toLowerCase().startsWith( f.value.toLowerCase() );
					break;
				case 'endswith':
					cpass = i[f.field] !== undefined && typeof i[f.field] === 'string' && i[f.field].toLowerCase().endsWith( f.value.toLowerCase() );
					break;
				default:
					console.log('ERROR, unknown filter: ' + f.op );
					break;
			}
			pass = pass || cpass;
			if ( allany === 'all' && cpass === false ) {
				pass = false; break;
			} else if (	allany === 'any' && cpass === true ) {
				pass = true; break;
				break;
			}
		}
		if ( pass === true ) {
			filtered_events.push(i);
		}
	}

	filtered_events.sort( function (a,b) {
		for ( const f of sort_fields ) {
			if ( a[f] === undefined && b[f] !== undefined ) { return 1; }
			else if ( b[f] === undefined && a[f] !== undefined ) { return -1; }
			else if ( a[f] === undefined && b[f] === undefined ) { return 0; }
			if ( a[f].toString().toLowerCase() < b[f].toString().toLowerCase() ) { return -1; }
			else if ( a[f].toString().toLowerCase() > b[f].toString().toLowerCase() ) { return 1; }
		}
		return 0;
	});

	showSpinner = false;
}

let init = async () => {
	events = await getEvents();
	fields = await getEventFields();
	for( const [k,v] of Object.entries(fields) ) {
		field_ids[ v.name_fixed ] = v.id;
		field_names[ v.id ] = v.name_fixed;
	}
	fields_ordered = orderKeys( fields, (a,b) => ( a.weight - b.weight ) );
	converted_events = await convertEvents( events, fields );
}

const handleRowClick = ( e ) => {
    $event_id = e.target.dataset.entryId;
    $screen = 'event';
    router.goto('/event/' + $event_id + '/view');
}


const exportToExcel = ( data ) => {
	var ws = XLSX.utils.aoa_to_sheet( data ),
		ws_name = window.pnb.xlsx['events-export'];
	var wb = XLSX.utils.book_new();
	wb.SheetNames.push(ws_name);
	wb.Sheets[ws_name] = ws;
	var wbout = XLSX.write(wb, {bookType:'xlsx', bookSST:true, type: 'binary'});
	saveAs( new Blob([s2ab(wbout)],{type:"application/octet-stream"}), ws_name + '-' + ( Date.now() / 1000 | 0 )+".xlsx" );
}

const prepareForExcel = () => {
	let data = [];
	for( const v of filtered_events ) {
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
<div style="text-align: center;" class="mdc-typography--headline4">FILTER EVENTS</div>
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
        <Option value={fields[field_id].name_fixed}>{fields[field_id].name_desc}</Option>
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
        <Option value="id">ID</Option>
      {#each fields_ordered as field_id}
        <Option value={fields[field_id].name_fixed}>{fields[field_id].name_desc}</Option>
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
      <Option value="id">ID</Option>
      {#each fields_ordered as field_id}
        <Option value={fields[field_id].name_fixed}>{fields[field_id].name_desc}</Option>
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

	<div style="text-align: center;" class="mdc-typography--headline4">FILTERED EVENTS:</div>

<Paper>
<DataTable table$aria-label="Event List" style="width: 100%;"
  sortable
  on:SMUIDataTableRow:click={handleRowClick}
>
    <Head>
        <Row>
            {#each display_fields.map( df => { 
				if ( df === 'id' ) {
					return {"title": "ID", field: df, "align": "left", "width": "unset" };
				} else {
					return { "title": fields[ field_ids[df] ].name_desc, "field": df, "align": "left", "width": "unset" };
				}}) as evt }
            <Cell columnId="name_full" style="text-align: {evt.align}; width: {evt.width};">
                <Label>{evt.title}</Label>
                <IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            {/each}
        </Row>
    </Head>
    <Body>
    {#each filtered_events as item (item.id)}
      <Row data-entry-id="{item.id}">
        {#each display_fields.map( df => {
			 return { "title": df, "field": df, "align": "left", "width": "unset" }; 
			}) as evt }
        <Cell style="text-align: {evt.align}; width: {evt.width};">
            {item[ evt.field ] || ''}
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