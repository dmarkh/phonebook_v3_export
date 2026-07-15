<script>

import {meta, router, Route} from 'tinro';

import Autocomplete from '@smui-extra/autocomplete';
import Chip, { Set, TrailingAction, Text } from '@smui/chips';

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

import { downloadTask } from '../utils/pnb-download.js';
import { toggleTask } from '../utils/pnb-api.js';
import { find_field_id } from '../utils/pnb-search.js';

import { getMembers } from '../utils/pnb-api.js';
import { convertMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';
import { getEvents } from '../utils/pnb-api.js';
import { convertEvents } from '../utils/pnb-convert.js';

export let taskId;
export let recordUpdated;

let i_data = false, title = false, subtitle = false, i_status = '';
let preedit_field_values  = {},
	postedit_field_values = {};
let ifields = {};
let required_fields = false;

let members = false, events = false;
let members_fid, event_fid;
let new_event_value = false;

let selected_members = [];
let members_value = '';
let authors_presenters = false;

const find_member = ( mid ) => {
    for ( const m of members ) {
        if ( m.id == mid ) { return m; }
    }
	return { id: mid, name_first: 'N/A', name_last: 'N/A' };
}

const find_event = ( eid ) => {
    for ( const e of events ) {
        if ( e.id == eid ) { return e; }
    }
    return { id: eid, name: 'N/A' };
}

const handle_member_selection = ( event ) => {
    event.preventDefault();
    selected_members = [ ...selected_members, event.detail ];
}

const fetchTask = async () => {

/*
	// FIXME: members, groups

    const mem = await getMembers();
    members = await convertMembers( mem );
    sortConvertedMembers( members );

    const evt = await getEvents();
    events = await convertEvents( evt );
	events = [ { id: 0, name: 'No Event' }, ...events ];
*/

    let data = [];
    let i = await downloadTask( taskId );

	i_status = i.task.task ? i.task.task.status : 'active';

	ifields = i.task_fields;

	if ( taskId && i.ctask ) {
		title = i.ctask.title;
	} else {
		title = 'NEW TASK';
	}
	subtitle = '';

    for ( const id of i.task_fields_ordered ) {
		if ( i.task_fields[id].is_enabled !== 'y' ) { continue; }
   	    preedit_field_values[ id ] = i.task.fields[id] || '';
       	postedit_field_values[ id ] = i.task.fields[id] || '';
		if ( id == event_fid && i.task.fields[id] ) {
			new_event_value = find_event( i.task.fields[id] );
		}
        data.push({
   	        id: parseInt(id),
       	    field: i.task_fields[id],
           	value: i.task.fields[id],
	        cvalue: i.ctask[ i.task_fields[id].name_fixed ]
        });
        if ( id == members_fid && preedit_field_values[ id ] ) {
            let authors = preedit_field_values[ id ].toString().split(',');
            if ( authors && authors.length ) {
                for ( const mid of authors ) {
                    if ( !mid ) { continue; }
                    selected_members.push( find_member( mid ) );
                }
            }
        }
   	}

	i_data = data;
	return data;
}

const toggleRecord = async () => {
    await toggleTask( taskId );
    router.goto('/tasks');
}

const updateRecord = () => {

	postedit_field_values[ event_fid ] = new_event_value ? new_event_value.id : 0;
    postedit_field_values[ members_fid ] = selected_members.map( m => m.id ).join(',');

    let missing = [];
    for ( let i = 0, ilen = i_data.length; i < ilen; i++ ) {
        if ( i_data[i].field.is_required === 'y' && !postedit_field_values[ i_data[i].id ] ) {
            missing.push( i_data[i].field.name_desc );
        }
    } 

    if ( missing.length ) {
        required_fields = missing;
        return;
    }

	let changes = [];
    for ( const k of Object.keys( preedit_field_values ) ) {
        if ( preedit_field_values[k] != postedit_field_values[k] && !( preedit_field_values[k] == '' && postedit_field_values[k] === undefined ) ) {
			changes.push({ "field_id": k, "pre": preedit_field_values[k], "post": postedit_field_values[k] });
		}
    }

	if ( recordUpdated ) {
		recordUpdated( taskId, changes, postedit_field_values );
	}
}

const updateField = ( name_from, name_to ) => {
	let from_id = find_field_id( ifields, name_from ),
		to_id = find_field_id( ifields, name_to );
	postedit_field_values[ to_id ] = postedit_field_values[ from_id ];
}

</script>

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

{#await fetchTask() }

<LinearProgress indeterminate />

{:then data}

{#if title}
	<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
{/if}
{#if subtitle}
    <div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>
{/if}
{#if i_status === 'inactive'}
    <div style="text-align: center; color: #900;" class="mdc-typography--subtitle1">INACTIVE task</div>
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

{:else}
	{#if item.field.name_fixed === 'event_id'}

    <Autocomplete
		style="width: 100%"
        options={events}
        bind:value={new_event_value}
        getOptionLabel={(option) =>
            option ? `${option.name}` : ''}
        label="SELECT EVENT"
    />

/*
	// FIXME: members, groups
	{:else if item.field.name_fixed === 'member_ids'}

    <div class="task-authors-list">
        <pre style="display: inline-block;">AUTHORS/PRESENTERS:</pre>
        <Set style="display: inline-block;" bind:chips={selected_members} let:chip>
            <Chip {chip}>
                <Text tabindex={0}>{chip.name_last + ', ' + chip.name_first}</Text>
                <TrailingAction icon$class="material-icons">cancel</TrailingAction>
            </Chip>
        </Set>
    </div>
    <Autocomplete
        bind:this={authors_presenters}
        options={members}
        bind:value={members_value}
        label="ADD AUTHOR/PRESENTER"
        getOptionLabel={(option) =>
            option ? `${option.name_last}, ${option.name_first}` : ''}
        on:SMUIAutocomplete:selected={handle_member_selection}
    />
*/

    {:else if item.field.type === 'number'}
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

    {:else if item.field.type === 'text'}
        <Textfield textarea bind:value={ postedit_field_values[ item.id ] }
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

{#if taskId}
<hr />
<div class="toggle-button">
    <Fab color="primary" on:click={() => { toggleRecord(); }} extended>
      <FabIcon class="material-icons">history_toggle_off</FabIcon>
      <FabLabel>TOGGLE STATUS (ACTIVE/INACTIVE)</FabLabel>
    </Fab>
</div>
{/if}

<div class="save-button">
    <Fab color="primary" on:click={() => { updateRecord(); }} extended>
      <FabIcon class="material-icons">save</FabIcon>
{#if taskId}
    <FabLabel>UPDATE RECORD</FabLabel>
{:else}
	<FabLabel>CREATE NEW RECORD</FabLabel>
{/if}
    </Fab>
</div>

</Paper>


{/await}


<style>
.save-button {
    position: absolute;
    bottom: 2vmin;
    right: 2vmin;
}
</style>