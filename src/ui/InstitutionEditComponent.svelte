<script>

import {meta, router, Route} from 'tinro';

import LinearProgress from '@smui/linear-progress';
import Paper, { Content } from '@smui/paper';
import Select, { Option } from '@smui/select';
import IconButton from '@smui/icon-button';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';
import CharacterCounter from '@smui/textfield/character-counter';

import Dialog, { Title as DialogTitle, Content as DialogContent, Actions as DialogActions, InitialFocus as DialogInitialFocus } from '@smui/dialog';
import Button, { Label as ButtonLabel } from '@smui/button';

import { downloadInstitution, listInstitutions, listMembers } from '../utils/pnb-download.js';
import { getMembers, toggleInstitution } from '../utils/pnb-api.js';
import { convertMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';
import { find_field_id } from '../utils/pnb-search.js';
import { geocode_locate_address } from '../utils/geocode.js';

import { status } from '../store.js';

export let institutionId;
export let recordUpdated;

let geocode_open = false,
	new_lat = '',
	new_lon = '';

let members = [], imembers = [];
let i_data = false, title = false, subtitle = false, i_status = '';
let institution_ids_sorted = [];
let	country_ids_sorted = [],
	country_codes_sorted = [];
let preedit_field_values  = {},
	postedit_field_values = {};
let ifields = {};
let required_fields = false;

let virtual_fid = false, virtual_val = false;

const fetchInstitution = async () => {

	members = await listMembers();
	imembers = members.filter( m => m[2] == institutionId );

    let data = [];
    let i = await downloadInstitution( institutionId );

	i_status = i.institution.institution ? i.institution.institution.status : 'active';

	ifields = i.institution_fields;
   	country_ids_sorted = i.country_ids_sorted;
	country_codes_sorted = i.country_codes_sorted;

    virtual_fid = find_field_id( ifields, 'is_virtual' );
    virtual_val = virtual_fid ? postedit_field_values[ virtual_fid ] : false;

	if ( institutionId && i.cinstitution ) {
		title = i.cinstitution.name_full;
	} else {
		title = 'NEW INSTITUTION';
	}
	if ( i.cinstitution ) {
	    subtitle = i.cinstitution.country || 'COUNTRY NOT SET';
	} else {
		subtitle = '';
	}
    for ( const id of i.institution_fields_ordered ) {
		if ( i.institution_fields[id].is_enabled !== 'y' ) { continue; }
   	    preedit_field_values[ id ] = i.institution.fields[id] || '';
       	postedit_field_values[ id ] = i.institution.fields[id] || '';
        data.push({
   	        id: parseInt(id),
       	    field: i.institution_fields[id],
           	value: i.institution.fields[id],
	        cvalue: i.cinstitution[ i.institution_fields[id].name_fixed ],
       	    group: i.institution_groups[ i.institution_fields[id].group ].name_full
        });
   	}

	// "country" fix:
	updateField( 'country_code', 'country' );
    let fid = find_field_id( ifields, 'country' );
    preedit_field_values[ fid ] = postedit_field_values[ fid ];

	institution_ids_sorted = await listInstitutions();


	i_data = data;
	return data;
}

const toggleRecord = async () => {
    await toggleInstitution( institutionId );
    router.goto('/institutions');
}

const updateRecord = () => {

	let virtual_fid = find_field_id( ifields, 'is_virtual' );
	let virtual_val = postedit_field_values[ virtual_fid ];

    let missing = [];
    for ( let i = 0, ilen = i_data.length; i < ilen; i++ ) {
        if ( i_data[i].field.is_required === 'y' && !postedit_field_values[ i_data[i].id ] ) {
            missing.push( i_data[i].field.name_desc );
        }
    } 

    if ( virtual_val !== 'y' && missing.length ) {
        required_fields = missing;
        return;
    }

	let fid = find_field_id( ifields, 'country' );

	let changes = [];
    for ( const k of Object.keys( preedit_field_values ) ) {
        if ( preedit_field_values[k] != postedit_field_values[k] && !( preedit_field_values[k] == '' && postedit_field_values[k] === undefined ) ) {
			if ( k === fid ) {
				changes.push({ "field_id": k, "pre": findCountryName(preedit_field_values[k]), "post": findCountryName(postedit_field_values[k]) });
			} else {
				changes.push({ "field_id": k, "pre": preedit_field_values[k], "post": postedit_field_values[k] });
			}
		}
    }

	if ( recordUpdated ) {
		recordUpdated( institutionId, changes, postedit_field_values );
	}
}

const updateField = ( name_from, name_to ) => {
	let from_id = find_field_id( ifields, name_from ),
		to_id = find_field_id( ifields, name_to );
	postedit_field_values[ to_id ] = postedit_field_values[ from_id ];
}

const findCountryName = ( code = '' ) => {
	for ( let i = 0, ilen = country_ids_sorted.length; i < ilen; i++ ) {
		if ( country_ids_sorted[i][0] == code ) { return country_ids_sorted[i][1]; }
	}
	return '';
}

const get_geocode_information = async () => {
	new_lat = '';
	new_lon = '';
	const address = preedit_field_values[ find_field_id( ifields, 'name_full' ) ] + ','
					preedit_field_values[ find_field_id( ifields, 'country' ) ];
	const coord = await geocode_locate_address( address );
	if ( coord ) {
		new_lat = coord.lat;
		new_lon = coord.lon;
	} else {
		new_lat = 'GEOCODE FAILED';
		new_lon = 'GEOCODE FAILED';
	}
}

const apply_new_coordinates = async () => {
	if ( !new_lat || !new_lon || new_lat === 'GEOCODE FAILED' || new_lon === 'GEOCODE FAILED' ) { return; }
	postedit_field_values[ find_field_id( ifields, 'geo_lattitude' ) ] = new_lat;
	postedit_field_values[ find_field_id( ifields, 'geo_longitude' ) ] = new_lon;
}

</script>

<Dialog
  open={geocode_open}
  aria-labelledby="default-focus-title"
  aria-describedby="default-focus-content"
  scrimClickAction=""
  escapeKeyAction=""
>
  <DialogTitle id="default-focus-title">GEOCODE: {title}</DialogTitle>
  <DialogContent id="default-focus-content">
	<table width="100%">
		<tr>
			<td>LAT: { preedit_field_values[ find_field_id( ifields, 'geo_lattitude' ) ] }</td>
			<td>NEW LAT: { new_lat } </td>
		</tr>
		<tr>
			<td>LON: { preedit_field_values[ find_field_id( ifields, 'geo_longitude' ) ] } </td>
			<td>NEW LON: { new_lon } </td>
		</tr>
	</table>
	{#if !new_lat}
	<div>PLEASE WAIT, GEOCODING IN PROGRESS...</div>
	{/if}
  </DialogContent>
  <DialogActions>
    <Button on:click={() => { geocode_open = false; apply_new_coordinates(); }}>
      <ButtonLabel>APPLY NEW COORDINATES</ButtonLabel>
    </Button>
    <Button
      defaultAction
      use={[DialogInitialFocus]}
      on:click={() => { geocode_open = false; }}
    >
      <ButtonLabel>CLOSE</ButtonLabel>
    </Button>
  </DialogActions>
</Dialog>

<Dialog
  open={required_fields}
  aria-labelledby="default-focus-title"
  aria-describedby="default-focus-content"
  scrimClickAction=""
  escapeKeyAction=""
>
  <DialogTitle id="default-focus-title">REQUIRED FIELDS NOT FILLED</DialogTitle>
  <DialogContent id="default-focus-content">
    <p> Please fill the following required fields:</p>
    <p> {Array.isArray(required_fields) ? required_fields.join(', ') : ''} </p>
  </DialogContent>
  <DialogActions>
    <Button
      defaultAction
      use={[DialogInitialFocus]}
      on:click={ async () => { required_fields = false; }}
    >
      <ButtonLabel>OK</ButtonLabel>
    </Button>
  </DialogActions>
</Dialog>

{#await fetchInstitution() }

<LinearProgress indeterminate />

{:then data}

{#if title}
	<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
{/if}
{#if subtitle}
    <div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>
{/if}
{#if i_status === 'inactive'}
    <div style="text-align: center; color: #900;" class="mdc-typography--subtitle1">INACTIVE INSTITUTION</div>
{/if}

<Paper>

{#each data as item (item.id)}
<div>

{#if item.field.decoded_options}
    <Select 
		key={(id) => `${id ? id : ''}`}
		bind:value={postedit_field_values[ item.id ]}
        style="width: 100%;"
        label="{item.field.name_desc}"
		required={item.field.is_required === 'y'}
    >
      {#each Object.entries(item.field.decoded_options) as [opt_key, opt_val] }
        <Option value={opt_key}>{opt_val}</Option>
      {/each}
    </Select>
{:else if item.field.name_fixed == 'associated_id'}
    <Select
        key={(id) => `${id ? id : ''}`}
        bind:value={postedit_field_values[ item.id ]}
        style="width: 100%;"
        label="{item.field.name_desc}"
        required={item.field.is_required === 'y'}
    >
      <Option value="0">No Parent Institution</Option>
      {#each institution_ids_sorted as inst (inst[0]) }
        <Option value={inst[0]}>{inst[1]}</Option>
      {/each}
    </Select>
{:else if item.field.name_fixed == 'council_representative'}
    <Select
        key={(id) => `${id ? id : ''}`}
        bind:value={postedit_field_values[ item.id ]}
        style="width: 100%;"
        label="{item.field.name_desc}"
        required={item.field.is_required === 'y'}
    >
      <Option value="0">No Council Representative</Option>
      {#each imembers as m (m[0])}
        <Option value={m[0]}>{m[1]}</Option>
      {/each}
    </Select>

{:else if item.field.name_fixed == 'country_code'}
    <Select bind:value={postedit_field_values[ item.id ]}
        style="width: 100%;"
        label="{item.field.name_desc}"
		required={item.field.is_required === 'y'}
    >
      {#each country_codes_sorted as country_code (country_code[0]) }
        <Option value={country_code[0]}>{country_code[1]}</Option>
      {/each}
    </Select>

{:else if item.field.name_fixed == 'country'}
    <Select bind:value={postedit_field_values[ item.id ]} on:SMUISelect:change={ () => { updateField('country', 'country_code'); } }
        style="width: 100%;"
        label="{item.field.name_desc}"
		required={item.field.is_required === 'y'}
    >
      {#each country_ids_sorted as country (country[0]) }
        <Option value={country[0]}>{country[1]}</Option>
      {/each}
    </Select>
{:else}
    {#if item.field.type === 'number'}
        <Textfield bind:value={ postedit_field_values[ item.id ] }
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="{item.field.name_desc}"
            type="number"
			required={item.field.is_required === 'y'}
        >
          <svelte:fragment slot="helper">
            <HelperText>{item.field.hint_full}</HelperText>
          </svelte:fragment>
        </Textfield>

    {:else if item.field.type === 'date'}
        <Textfield bind:value={ postedit_field_values[ item.id ] }
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="{item.field.name_desc}"
            type="date"
			required={item.field.is_required === 'y'}
        >
          <svelte:fragment slot="helper">
            <HelperText>{item.field.hint_full}</HelperText>
          </svelte:fragment>
        </Textfield>
    {:else}
        <Textfield bind:value={ postedit_field_values[ item.id ] }
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="{item.field.name_desc}"
            input$maxlength={item.field.size_max}
			required={item.field.is_required === 'y'}
        >
          <svelte:fragment slot="helper">
            <HelperText>{item.field.hint_full}</HelperText>
            <CharacterCounter>0 / {item.field.size_max}</CharacterCounter>
          </svelte:fragment>
        </Textfield>
    {/if}

{/if}

</div>
{/each}

<br />
<hr>
<br />

{#if institutionId}
<div class="toggle-button">
    <Fab color="primary" on:click={() => { toggleRecord(); }} extended>
      <FabIcon class="material-icons">history_toggle_off</FabIcon>
      <FabLabel>TOGGLE STATUS (ACTIVE/INACTIVE)</FabLabel>
    </Fab>
</div>
{/if}

{#if i_status == 'active'}
<div class="geocode-button">
    <Fab color="primary" on:click={() => { get_geocode_information(); geocode_open = true; }} extended>
      <FabIcon class="material-icons">language</FabIcon>
	  <FabLabel>GEOCODE</FabLabel>
    </Fab>
</div>

<div class="save-button">
    <Fab color="primary" on:click={() => { updateRecord(); }} extended>
      <FabIcon class="material-icons">save</FabIcon>
{#if institutionId}
    <FabLabel>UPDATE RECORD</FabLabel>
{:else}
	<FabLabel>CREATE NEW RECORD</FabLabel>
{/if}
    </Fab>
</div>
{/if}

</Paper>


{/await}


<style>
.save-button {
    position: absolute;
    bottom: 2vmin;
    right: 2vmin;
}
.geocode-button {
    position: absolute;
    bottom: 10vmin;
    right: 2vmin;
}
</style>