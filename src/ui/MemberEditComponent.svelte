<script>

import { auth } from '../store.js';
import { meta, router, Route } from 'tinro';
import { screen, member_id } from '../store.js';

import LinearProgress from '@smui/linear-progress';
import Paper, { Content } from '@smui/paper';
import Select, { Option } from '@smui/select';
import IconButton from '@smui/icon-button';
import Fab, { Label, Icon } from '@smui/fab';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';
import CharacterCounter from '@smui/textfield/character-counter';
import Button, { Label as ButtonLabel } from '@smui/button';
import Dialog, { Title as DialogTitle, Content as DialogContent, Actions as DialogActions, InitialFocus as DialogInitialFocus } from '@smui/dialog';

import DataTable, { Head, Body, Row, Cell, Label as DataLabel, SortValue, Pagination } from '@smui/data-table';

import MultiSelect from 'svelte-select';
import Croppie from 'croppie';
import 'croppie/croppie.css';

import { downloadMember } from '../utils/pnb-download.js';
import { toggleMember } from '../utils/pnb-api.js';
import { status } from '../store.js';

import { checkMembersByOrcid, checkMembersByEmail, searchMembers } from '../utils/pnb-api.js';

export let memberId;
export let institutionId;
export let recordUpdated;

let photoedit_open = false,
	photo_field_id = false,
	files = false,
	valueTypeNumber = 0,
	valueTypeNumberStep = 0,
	valueTypeDate = '',
	valueTypeFiles = null,
	croppieInstance = false;

let duplicates = false, duplicates_open = false;
let similarsounding = false, similarsounding_open = false;

$: if ( valueTypeFiles != null && valueTypeFiles.length && photo_field_id ) {

	if ( !croppieInstance ) {
		let el = document.getElementById('photoedit');
		croppieInstance = new Croppie(el, {
			viewport: {
        		width:  300,
	       		height: 300,
    	        type: 'square'
        	},
		    boundary: {
    	    	width:  400,
        		height: 400
	    	},
    	    enableExif: true,
	    	enableOrientation: true
		});
	}

	if ( valueTypeFiles && valueTypeFiles[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {

		    croppieInstance.bind({
        	    url: e.target.result,
                zoom: 0
            }).then( () => {
            	console.log('croppie bind complete');
            });

        }
        reader.readAsDataURL( valueTypeFiles[0] );
     }
}

let m = false;
let m_data = false, title = false, subtitle = false, m_status = '';
let institution_ids_sorted = [],
	country_ids_sorted = [];
let preedit_field_values  = {},
	postedit_field_values = {};
let extra_institutions = [],
	extra_institutions_field_id = false;
let required_fields = false;

let on_apply_croppie = async () => {
	let cropresult = await croppieInstance.result({
		type: 'base64',
		size: 'viewport',
		format: 'jpeg',
		quality: 0.6
	});
	postedit_field_values[ photo_field_id ] = cropresult;
}

const toggleRecord = async () => {
	await toggleMember( memberId );
	router.goto('/members');
}

const fetchMember = async () => {
    let data = [];

    m = await downloadMember( memberId, institutionId );
	m_status = m.member.member ? m.member.member.status : 'active';

	photo_field_id = Object.values(m.member_fields).find( mf => mf.name_fixed === 'photo');
	if ( photo_field_id ) {
		photo_field_id = photo_field_id.id;
	}

	extra_institutions_field_id = Object.values(m.member_fields).find( mf => mf.name_fixed === 'extra_institution_id');
	if ( extra_institutions_field_id ) {
		extra_institutions_field_id = extra_institutions_field_id.id;
		extra_institutions = m.institution_ids_sorted.map( inst => { return { "value": inst[0], "label": inst[1] }; });
	} else {
		extra_institutions_field_id = false;
	}

    institution_ids_sorted = m.institution_ids_sorted;
   	country_ids_sorted = m.country_ids_sorted;
	if ( memberId && m.cmember ) {
		title = m.cmember.name_first + ' ' + m.cmember.name_last;
	} else {
		title = 'NEW MEMBER';
	}
	if ( m.cinstitution ) {
	    subtitle = m.cinstitution.name_full || 'INSTITUTION NOT SET';
	} else {
		subtitle = 'INSTITUTION NOT SET';
	}

    for ( const id of m.member_fields_ordered ) {
		if ( m.member_fields[id].is_enabled !== 'y' ) { continue; }
		if ( id === extra_institutions_field_id ) {
	   	    preedit_field_values[ id ]  = m.member.fields[id] ? m.member.fields[id].split(',').map( v => { return { "value": Number(v) }; }) : [];
   		   	postedit_field_values[ id ] = m.member.fields[id] ? m.member.fields[id].split(',').map( v => { return { "value": Number(v) }; }) : [];
	        data.push({
   		        id: parseInt(id),
				name_fixed: m.member_fields[id].name_fixed,
       		    field: m.member_fields[id],
           		value: m.member.fields[id],
		        cvalue: m.cmember[ m.member_fields[id].name_fixed ],
    	   	    group: m.member_groups[ m.member_fields[id].group ].name_full
        	});
		} else {
	   	    preedit_field_values[ id ] = m.member.fields[id] || '';
   		   	postedit_field_values[ id ] = m.member.fields[id] || '';
	        data.push({
   		        id: parseInt(id),
				name_fixed: m.member_fields[id].name_fixed,
       		    field: m.member_fields[id],
           		value: m.member.fields[id],
		        cvalue: m.cmember[ m.member_fields[id].name_fixed ],
    	   	    group: m.member_groups[ m.member_fields[id].group ].name_full
        	});
		}
   	}
	m_data = data;

	return data;
}

const updateRecord = async () => {

	let missing = [];
	for ( let i = 0, ilen = m_data.length; i < ilen; i++ ) {
		if ( m_data[i].field.is_required === 'y' && !postedit_field_values[ m_data[i].id ] ) {
			missing.push( m_data[i].field.name_desc );
		}
	}

	if ( missing.length ) {
		required_fields = missing;
		return;
	}

	let changes = [];
    for ( const k of Object.keys(preedit_field_values) ) {
        if ( preedit_field_values[k] !== postedit_field_values[k] && !( preedit_field_values[k] == '' && postedit_field_values[k] === undefined ) ) {
			if ( k === extra_institutions_field_id ) {
				let pre_v = Array.isArray( preedit_field_values[k] ) ?  preedit_field_values[k].map( fv => fv.value ).join(',') : '';
				let pos_v = Array.isArray( postedit_field_values[k] ) ? postedit_field_values[k].map( fv => fv.value ).join(',') : '';
				if ( pre_v != pos_v ) {
					changes.push({ "field_id": k,
						"name_fixed": m.member_fields[k].name_fixed,
						"pre" : pre_v,
						"post": pos_v
					});
				}
			} else {
				changes.push({
					"field_id": k,
					"name_fixed": m.member_fields[k].name_fixed,
					"pre": preedit_field_values[k],
					"post": postedit_field_values[k]
				});
			}
        }
    }

	for ( let i = 0, ilen = changes.length; i < ilen; i++ ) {
		if ( Array.isArray( changes[i].pre ) ) {
			changes[i].pre = changes[i].pre.join(',');
		}
		if ( Array.isArray( changes[i].post ) ) {
			changes[i].pre = changes[i].post.join(',');
		}
	}

	if ( recordUpdated ) {
		let found = [];
		for ( const change of changes ) {
			if ( change.name_fixed.startsWith('orcid') ) {
				found = [ ...found, ...await checkMembersByOrcid( change.post ) ];
			} else if ( change.name_fixed.startsWith('email') ) {
				found = [ ...found, ...await checkMembersByEmail( change.post ) ];
			}
		}
		if ( found.length ) {
			found = [
				...new Map( found.map( item => [item.mid, item])).values()
			];
			found.sort( (a, b) => {
				  const nameSort = a.name_last.localeCompare( b.name_last );
				  if ( nameSort !== 0 ) return nameSort;
				  return a.name_first.localeCompare( b.name_first );
				});
			duplicates = found;
			duplicates_open = true;
			console.log( 'ERROR: duplicates found, cannot update');
		} else {
			recordUpdated( memberId, changes, postedit_field_values );
		}
	}
}

</script>

<Dialog
  open={duplicates_open}
  aria-labelledby="default-focus-title"
  aria-describedby="default-focus-content"
  surface$style="width: 850px; max-width: calc(100vw - 32px);"
  scrimClickAction=""
  escapeKeyAction=""
>
  <DialogTitle id="default-focus-title">ERROR: DUPLICATES FOUND</DialogTitle>
  <DialogContent id="default-focus-content">
	<p><b>List of duplicates (ORCID or Email)</b></p>
	<DataTable table$aria-label="Duplicates" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="status" style="text-align: left;">
                <Label>STATUS</Label>
            </Cell>
            <Cell columnId="name" style="text-align: left;">
                <Label>NAME</Label>
            </Cell>
            <Cell columnId="email" style="text-align: center;">
                <Label>EMAIL</Label>
            </Cell>
            <Cell columnId="orcid" style="text-align: center;">
                <Label>ORCID</Label>
            </Cell>
            <Cell columnId="action" style="text-align: center;">
                <Label>ACTION</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
	{#if duplicates}
    {#each duplicates as item (item.mid)}
          <Row data-entry-id="{item.mid}">
            <Cell style="">{item.status}</Cell>
            <Cell style="">{item.name_last}, {item.name_first}</Cell>
            <Cell style="">{item.email}</Cell>
            <Cell style="">{item.orcid}</Cell>
            <Cell style="">
			    <Button on:click={() => { duplicates = false; duplicates_open = false; $member_id = item.mid; router.goto('/member/' + item.mid + '/edit' ); }}>
			      <ButtonLabel>EDIT THIS MEMBER</ButtonLabel>
			    </Button>
			</Cell>
          </Row>
    {/each}
	{/if}
	</Body>
	</DataTable>
  </DialogContent>
  <DialogActions>
    <Button
      defaultAction
      use={[DialogInitialFocus]}
      on:click={ async () => { duplicates = false; duplicates_open = false; }}
    >
      <ButtonLabel>CANCEL UPDATE</ButtonLabel>
    </Button>
  </DialogActions>
</Dialog>

<Dialog
  open={similarsounding_open}
  aria-labelledby="default-focus-title"
  aria-describedby="default-focus-content"
  surface$style="width: 850px; max-width: calc(100vw - 32px);"
  scrimClickAction=""
  escapeKeyAction=""
>
  <DialogTitle id="default-focus-title">SIMILAR NAMES FOUND</DialogTitle>
  <DialogContent id="default-focus-content">
	<p><b>List of POTENTIAL duplicates</b></p>
	<DataTable table$aria-label="Similar Names" style="width: 100%;">
    <Head>
        <Row>
            <Cell columnId="name" style="text-align: left;">
                <Label>NAME</Label>
            </Cell>
            <Cell columnId="email" style="text-align: center;">
                <Label>EMAIL</Label>
            </Cell>
            <Cell columnId="orcid" style="text-align: center;">
                <Label>ORCID</Label>
            </Cell>
            <Cell columnId="action" style="text-align: center;">
                <Label>ACTION</Label>
            </Cell>
        </Row>
    </Head>
    <Body>
	{#if similarsounding}
    {#each similarsounding as item (item.mid)}
          <Row data-entry-id="{item.mid}">
            <Cell style="">{item.name_last}, {item.name_first}</Cell>
            <Cell style="">{item.email}</Cell>
            <Cell style="">{item.orcid}</Cell>
            <Cell style="">
			    <Button on:click={() => { similarsounding = false; similarsounding_open = false; $member_id = item.mid; router.goto('/member/' + item.mid + '/edit' ); }}>
			      <ButtonLabel>EDIT THIS MEMBER</ButtonLabel>
			    </Button>
			</Cell>
          </Row>
    {/each}
	{/if}
	</Body>
	</DataTable>
  </DialogContent>
  <DialogActions>
    <Button
      defaultAction
      use={[DialogInitialFocus]}
      on:click={ async () => { similarsounding = false; similarsounding_open = false; }}
    >
      <ButtonLabel>IGNORE</ButtonLabel>
    </Button>
  </DialogActions>
</Dialog>

<Dialog
  open={photoedit_open}
  aria-labelledby="default-focus-title"
  aria-describedby="default-focus-content"
  scrimClickAction=""
  escapeKeyAction=""
>
  <DialogTitle id="default-focus-title">PHOTO UPLOAD</DialogTitle>
  <DialogContent id="default-focus-content">
	<div class="hide-file-ui">
    	<Textfield bind:files={valueTypeFiles} label="File" type="file" />
	</div>
	<div id="photoedit"></div>
  </DialogContent>
  <DialogActions>
    <Button on:click={() => { photoedit_open = false; }}>
      <ButtonLabel>CANCEL</ButtonLabel>
    </Button>
    <Button
      defaultAction
      use={[DialogInitialFocus]}
      on:click={ async () => { await on_apply_croppie(); photoedit_open = false; }}
    >
      <ButtonLabel>APPLY CHANGE</ButtonLabel>
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


{#await fetchMember() }

<LinearProgress indeterminate />

{:then data}

{#if title}
	<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
{/if}
{#if subtitle}
    <div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>
{/if}
{#if m_status === 'inactive'}
    <div style="text-align: center; color: #900;" class="mdc-typography--subtitle1">INACTIVE MEMBER</div>
{/if}

<Paper>

{#each data as item (item.id)}
<div>

{#if ( item.field.name_fixed == 'member_role' && !$auth['grants']['members-assign-role'] ) }

	{#if ( item.field.decoded_options ) }
	    <Select
			key={(id) => `${id ? id : ''}`}
			bind:value={postedit_field_values[ item.id ]}
	        style="width: 100%;"
    	    label="{item.field.name_desc}"
			disabled
    	>
	      {#each Object.entries(item.field.decoded_options) as [opt_key, opt_val] }
    	    <Option value={opt_key}>{opt_val}</Option>
	      {/each}
    	</Select>
	{:else}
        <Textfield bind:value={postedit_field_values[ item.id ]}
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="{item.field.name_desc}"
            input$maxlength={item.field.size_max}
			disabled
        >
          <svelte:fragment slot="helper">
            <HelperText>{item.field.hint_full}</HelperText>
          </svelte:fragment>
        </Textfield>
	{/if}

{:else if item.field.decoded_options}

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

{:else if item.field.name_fixed == 'institution_id'}
    <Select 
		key={(id) => `${id ? id : ''}`}
		bind:value={postedit_field_values[ item.id ]}
        style="width: 100%;"
        label="{item.field.name_desc}"
		required={item.field.is_required === 'y'}
    >
      {#each institution_ids_sorted as inst (inst[0]) }
        <Option value={inst[0]}>{inst[1]}</Option>
      {/each}
    </Select>

{:else if item.field.name_fixed == 'country'}
    <Select bind:value={postedit_field_values[ item.id ]}
        style="width: 100%;"
        label="{item.field.name_desc}"
		required={item.field.is_required === 'y'}
    >
      {#each country_ids_sorted as country (country[0]) }
        <Option value={country[0]}>{country[1]}</Option>
      {/each}
    </Select>
{:else if item.field.name_fixed == 'extra_institution_id'}

	<MultiSelect bind:value={postedit_field_values[ item.id ]} items={extra_institutions} multiple placeholder={item.field.name_desc} />

{:else if item.field.name_fixed == 'photo'}
	<br />
	<Button color="secondary" on:click={() => {
		photoedit_open = true;
	}} variant="unelevated">
		<ButtonLabel>CHANGE PHOTO</ButtonLabel>
	</Button>

	{#if postedit_field_values[ item.id ]}
		<img src="{postedit_field_values[item.id]}" class="photoedit-image" />
	{/if}

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
{#if memberId}
<div class="toggle-button">
    <Fab color="primary" on:click={() => { toggleRecord(); }} extended>
      <Icon class="material-icons">history_toggle_off</Icon>
   	  <Label>TOGGLE STATUS (ACTIVE/INACTIVE)</Label>
    </Fab>
</div>
{/if}

{#if m_status == 'active'}
<div class="save-button">
    <Fab color="primary" on:click={() => { updateRecord(); }} extended>
      <Icon class="material-icons">save</Icon>
{#if memberId}
      <Label>UPDATE RECORD</Label>
{:else}
      <Label>CREATE NEW RECORD</Label>
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

#photoedit {
	display: block;
	width:  400px;
	height: 400px;
	overflow: hidden;
	position: relative;
}

.hide-file-ui :global(input[type='file']::file-selector-button) {
    display: none;
}

.hide-file-ui :global(:not(.mdc-text-field--label-floating) input[type='file']) {
	color: transparent;
}

</style>