<script>

import { tran } from '../utils/tran.js';

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

import AccessDenied from './AccessDenied.svelte';

import { downloadDocument } from '../utils/pnb-download.js';
import { toggleDocument } from '../utils/pnb-api.js';
import { find_field_id } from '../utils/pnb-search.js';

import { getMembers } from '../utils/pnb-api.js';
import { convertMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';
import { getEvents } from '../utils/pnb-api.js';
import { convertEvents } from '../utils/pnb-convert.js';

import { getGroups, getMemberGroups } from '../utils/pnb-api.js';
import { auth } from '../store.js';

export let documentId;
export let recordUpdated;

const mid = window.pnb.mid ? parseInt(window.pnb.mid) : null;

let groups, mgroups;

let i_data = false, title = false, subtitle = false, i_status = '';
let preedit_field_values  = {},
	postedit_field_values = {};
let ifields = {};
let required_fields = false;

let members = false,
	events = false;
let members_fid, reviewers_fid, event_fid, author_fid, group_fid, ts_fid, reference_fid;
let new_event_value = false;
let doc = false;

let selected_members = [];
let selected_reviewers = [];
let members_value = '';
let reviewers_value = '';
let authors_presenters = false;
let members_reviewers = false;
let group_value = false;
let setting_group = false;

const find_member = ( mid ) => {
    for ( const m of members ) {
        if ( m.id == mid ) { return m; }
    }
	return { id: mid, name_first: 'N/A', name_last: 'N/A' };
}

const is_member_group = ( gid ) => {
	if ( !mgroups ) { return false; }
    for ( const m of mgroups ) {
        if ( m.group_id == gid ) { return true; }
    }
	return false;
}

const find_event = ( eid ) => {
    for ( const e of events ) {
        if ( e.id == eid ) { return e; }
    }
    return { id: eid, name: 'N/A' };
}

const handle_member_selection = ( event ) => {
    event.preventDefault();
	if ( selected_members.find( m => m.id == event.detail.id ) ) { return; }
    selected_members = [ ...selected_members, event.detail ];
}

const handle_reviewer_selection = ( event ) => {
    event.preventDefault();
	if ( selected_reviewers.find( m => m.id == event.detail.id ) ) { return; }
    selected_reviewers = [ ...selected_reviewers, event.detail ];
}

const handle_group_selection = ( event ) => {
	postedit_field_values[ group_fid ] = event.detail.id;
    event.preventDefault();
    group_value = event.detail;
}

const get_todays_date = () => {
	let yourDate = new Date();
	const offset = yourDate.getTimezoneOffset()
	yourDate = new Date(yourDate.getTime() - (offset*60*1000))
	return yourDate.toISOString().split('T')[0]
}

const fetchDocument = async () => {

    const mem = await getMembers();
    members = await convertMembers( mem );
    sortConvertedMembers( members );

	groups = await getGroups();
	mgroups = mid ? await getMemberGroups(mid) : {};

    const evt = await getEvents();
    events = await convertEvents( evt );
	events = [ { id: 0, name: 'No Event' }, ...events ];

    let data = [];

    let i = await downloadDocument( documentId );
	doc = i;
	i_status = i.document.document ? i.document.document.status : 'active';
	ifields = i.document_fields;

	members_fid = find_field_id( ifields, 'member_ids' );
	reviewers_fid = find_field_id( ifields, 'reviewer_ids' );
	event_fid   = find_field_id( ifields, 'event_id' );
	author_fid = find_field_id( ifields, 'author_id' );
	group_fid = find_field_id( ifields, 'group_id' );
	ts_fid = find_field_id( ifields, 'ts' );
	reference_fid = find_field_id( ifields, 'reference_id' );

	if ( documentId && i.cdocument ) {
		title = i.cdocument.title;
	} else {
		title = 'NEW DOCUMENT';
	}
	subtitle = '';

	if ( !documentId && mid ) {
		selected_members.push( find_member( mid ) );
	}

    for ( const id of i.document_fields_ordered ) {
		if ( i.document_fields[id].is_enabled !== 'y' ) { continue; }
   	    preedit_field_values[ id ] = i.document.fields[id] || '';
       	postedit_field_values[ id ] = i.document.fields[id] || '';
		if ( id == event_fid && i.document.fields[id] ) {
			new_event_value = find_event( i.document.fields[id] );
		} else if ( id === author_fid && mid && !postedit_field_values[ id ] ) {
			postedit_field_values[ id ] = mid;
		} else if ( id === group_fid && postedit_field_values[ id ] ) {
			group_value = groups.find( grp => grp.id == postedit_field_values[ id ] );
		} else if ( id === ts_fid && !preedit_field_values[ id ] ) {
			postedit_field_values[ id ] = get_todays_date();
		}
        data.push({
   	        id: parseInt(id),
       	    field: i.document_fields[id],
           	value: i.document.fields[id],
	        cvalue: i.cdocument[ i.document_fields[id].name_fixed ]
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
        if ( id == reviewers_fid && preedit_field_values[ id ] ) {
            let reviewers = preedit_field_values[ id ].toString().split(',');
            if ( reviewers && reviewers.length ) {
                for ( const mid of reviewers ) {
                    if ( !mid ) { continue; }
                    selected_reviewers.push( find_member( mid ) );
                }
            }
        }
   	}

	i_data = data;
	return data;
}

const toggleRecord = async () => {
    await toggleDocument( documentId );
    router.goto('/documents');
}

const updateRecord = () => {

	postedit_field_values[ event_fid ] = new_event_value ? new_event_value.id : 0;
    postedit_field_values[ members_fid ] = selected_members.map( m => m.id ).join(',');
    postedit_field_values[ reviewers_fid ] = selected_reviewers.map( m => m.id ).join(',');

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
		recordUpdated( documentId, changes, postedit_field_values );
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

{#await fetchDocument() }

<LinearProgress indeterminate />

{:then data}

{#if ( !mid || ( documentId && mid != doc.cdocument.author_id ) ) && !$auth['grants']['documents-admin'] }
	<AccessDenied />
{:else}

{#if title}
	<div style="text-align: center;" class="mdc-typography--headline4">{tran(title)}</div>
{/if}
{#if subtitle}
    <div style="text-align: center;" class="mdc-typography--subtitle1">{tran(subtitle)}</div>
{/if}
{#if i_status === 'inactive'}
    <div style="text-align: center; color: #900;" class="mdc-typography--subtitle1">{tran('INACTIVE document')}</div>
{/if}

<Paper>

{#each data as item (item.id)}

<div>

{#if item.field.decoded_options}
    <Select 
		key={(id) => `${id ? id : ''}`}
		bind:value={postedit_field_values[ item.id ]}
        style="width: 100%;"
        label="{tran(item.field.name_desc)}"
		required={item.field.is_required === 'y'}
    >
      {#each Object.entries(item.field.decoded_options) as [opt_key, opt_val] }
        <Option value={opt_key}>{opt_val}</Option>
      {/each}
    </Select>

{:else}
	{#if item.field.name_fixed === 'event_id'}

    <Autocomplete
        options={events}
        bind:value={new_event_value}
        getOptionLabel={(option) =>
            option ? `${option.name}` : ''}
        style="width: 100%;"
        textfield$style="width: 100%;"
        label="{tran('SELECT EVENT')}"
    />

	{:else if item.field.name_fixed === 'author_id'}
		{@const imem = find_member(postedit_field_values[ item.id ])}
		{#if imem && imem.id}
			<div> <span style="display: inline-block; min-width: 12vmin;">{tran('_owner_')}:</span> {imem.name_first} {imem.name_last} </div>
		{:else}
			<div> <span style="display: inline-block; min-width: 12vmin;">{tran('_owner_')}:</span> {tran('NOT SET')}</div>
		{/if}

	{:else if item.field.name_fixed === 'ts'}
		<div> <span style="display: inline-block; min-width: 12vmin;">{tran('_timestamp_')}:</span> {postedit_field_values[ item.id ]} </div>
	{:else if item.field.name_fixed === 'reference_id'}
		<div> <span style="display: inline-block; min-width: 12vmin;">{tran('DocID')}:</span> {postedit_field_values[ item.id ] || tran('NOT ASSIGNED')} </div>
	{:else if item.field.name_fixed === 'group_id'}

	    <Autocomplete
        	options={groups}
	        bind:value={group_value}
    	    label="{tran('_select_group_')}"
        	getOptionLabel={(option) =>
            	option ? ( ( is_member_group(option.id) ? '(*) ' : '' ) + `${option.name}` ) : ''}
		    style="width: 100%;"
		    textfield$style="width: 100%;"
			required={item.field.is_required === 'y'}
	        on:SMUIAutocomplete:selected={handle_group_selection}
    	/>

	{:else if item.field.name_fixed === 'member_ids'}

    <div class="document-authors-list">
        <pre style="display: inline-block;">{tran('_authors_')}:</pre>
        <Set style="display: inline-block;" bind:chips={selected_members} let:chip>
            <Chip {chip}>
                <Text tabindex={0}>{chip.name_last + ', ' + chip.name_first}</Text>
                <TrailingAction icon$class="material-icons">cancel</TrailingAction>
            </Chip>
        </Set>
    </div>
    <Autocomplete
        bind:this={authors_presenters}
		bind:value={members_value}
        options={members}
        label="{tran('_add_author_')}"
        getOptionLabel={(option) =>
            option ? `${option.name_last}, ${option.name_first}` : ''}
        style="width: 100%;"
        textfield$style="width: 100%;"
        on:SMUIAutocomplete:selected={handle_member_selection}
		on:focus={(e) => { }}
	    on:blur={(e) => { }}
    />

	{:else if item.field.name_fixed === 'reviewer_ids'}

    <div class="document-reviewer-list">
        <pre style="display: inline-block;">{tran('_reviewers_')}:</pre>
        <Set style="display: inline-block;" bind:chips={selected_reviewers} let:chip>
            <Chip {chip}>
                <Text tabindex={0}>{chip.name_last + ', ' + chip.name_first}</Text>
                <TrailingAction icon$class="material-icons">cancel</TrailingAction>
            </Chip>
        </Set>
    </div>
    <Autocomplete
        bind:this={members_reviewers}
        bind:value={reviewers_value}
        options={members}
        label="ADD REVIEWERS"
        getOptionLabel={(option) =>
            option ? `${option.name_last}, ${option.name_first}` : ''}
        style="width: 100%;"
        textfield$style="width: 100%;"
        on:SMUIAutocomplete:selected={handle_reviewer_selection}
    />

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
			input$maxlength={item.field.size_max}
			required={item.field.is_required === 'y'}
        >
          <svelte:fragment slot="helper">
            <HelperText>{item.field.hint_full}</HelperText>
            <CharacterCounter>{ typeof postedit_field_values[ item.id ] == 'string' ? postedit_field_values[item.id].length : 0 } / {item.field.size_max}</CharacterCounter>
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
            <CharacterCounter>{ typeof postedit_field_values[ item.id ] == 'string' ? postedit_field_values[item.id].length : 0 } / {item.field.size_max}</CharacterCounter>
          </svelte:fragment>
        </Textfield>
    {/if}

{/if}

</div>
{/each}

{#if documentId}
<hr />
<div class="toggle-button">
    <Fab color="primary" on:click={() => { toggleRecord(); }} extended>
      <FabIcon class="material-icons">history_toggle_off</FabIcon>
      <FabLabel>{tran('_toggle_status_active_inactive_')}</FabLabel>
    </Fab>
</div>
{/if}

<div class="save-button">
    <Fab color="primary" on:click={() => { updateRecord(); }} extended>
      <FabIcon class="material-icons">save</FabIcon>
{#if documentId}
    <FabLabel>{tran('_update_record_')}</FabLabel>
{:else}
	<FabLabel>{tran('_create_new_record_')}</FabLabel>
{/if}
    </Fab>
</div>

</Paper>

{/if}
{/await}


<style>
.save-button {
    position: absolute;
    bottom: 2vmin;
    right: 2vmin;
}
</style>