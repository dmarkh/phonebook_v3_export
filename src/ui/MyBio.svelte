<script>

import {meta, router, Route} from 'tinro';
import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';
import Button, { Label as ButtonLabel } from '@smui/button';
import Dialog, { Title as DialogTitle, Content as DialogContent, Actions as DialogActions, InitialFocus as DialogInitialFocus } from '@smui/dialog';

import { updateMember } from '../utils/pnb-api.js';
import { downloadMember } from '../utils/pnb-download.js';
import { tran } from '../utils/tran.js';

import Croppie from 'croppie';
import 'croppie/croppie.css';

const mid = parseInt(window.pnb.mid);
let member = false;
let title = '', subtitle = '';
let member_fields = window.pnb['self-member-fields'] || {};
let inst_fields = window.pnb['self-institution-fields'] || {};
let edit_field = false;
let edit_field_value = false;
let refresh = false;

let photoedit_open = false,
    photo_field_id = false,
    files = false,
    valueTypeNumber = 0,
    valueTypeNumberStep = 0,
    valueTypeDate = '',
    valueTypeFiles = null,
    croppieInstance = false;

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

let on_apply_croppie = async () => {
    let cropresult = await croppieInstance.result({
        type: 'base64',
        size: 'viewport',
        format: 'jpeg',
        quality: 0.6
    });

	const data = { [mid]: { [photo_field_id]: cropresult } };
	const rc = await updateMember( data );
	if ( rc ) {

	    files = false;
    	valueTypeNumber = 0;
	    valueTypeNumberStep = 0;
    	valueTypeDate = '';
	    valueTypeFiles = null;
	    croppieInstance = false;

		refresh = !refresh;
    }
}

const getMemberInfo = async () => {
	member = await downloadMember( mid );
	title = member.cmember.name_first + ' ' + member.cmember.name_last;
	subtitle = member.cmember.email + '<br /><br />' + member.cinstitution.name_full + '<br />' + member.cinstitution.country;

    const photo_field = Object.values(member.member_fields).find( mf => mf.name_fixed === 'photo');
    if ( photo_field ) {
        photo_field_id = photo_field.id;
    }

	return member;
}

const set_edit_field = ( v ) => {
	edit_field_value = member.cmember[v] || '';
	edit_field = v;
}

const save_edited_field = async () => {
	const field = Object.values( member.member_fields ).find( o => o.name_fixed == edit_field );
	if ( !field ) { console.log('ERROR: unknown field ' + edit_field ); return; } // ERROR
	const field_id = field.id;
	const data = { [mid]: { [field_id]: edit_field_value.trim() } };
	const rc = await updateMember( data );
	edit_field = false;
	edit_field_value = false;
	if ( rc ) {
		refresh = !refresh;
    }
}

</script>

{#key refresh}

{#if !mid}
    <div style="text-align: center; padding: 5vmin;">
        MEMBER NOT FOUND BY ORCID LOOKUP. PLEASE ASK YOUR REPRESENTATIVE (OR THE PHONEBOOK ADMIN) TO UPDATE YOUR RECORD!
    </div>
{:else}

	{#await getMemberInfo()}
		<LinearProgress indeterminate />
	{:then data}

	<Paper>
		<div>
			<table class="bio-table">
				<tr>
					<td width="5%">
						<img src="{data.cmember.photo || 'images/unknown-photo.svg'}" class="my-photo" on:click={() => { photoedit_open = true; }}/>
					</td>
					<td class="flex-center">
						<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
						{#if subtitle}
					    	<div style="text-align: center;" class="mdc-typography--subtitle1">{@html subtitle}</div>
						{/if}
					</td>
				</tr>
			</table>
		</div>
		<div>
		<DataTable table$aria-label="Member Data" style="width: 100%;">
		    <Body>
			{#each Object.entries(member_fields) as [k,v]}
			        <Row>
    	    	    <Cell style="width: 25%; text-align: left;">{tran(k.toUpperCase())}</Cell>
					<Cell style="width: 70%; text-align: left;">
					{#if edit_field == v}
	                    <Textfield bind:value={ edit_field_value }
    	                    style="width: 100%;"
        	                helperLine$style="width: 100%;"
            	            label="{k}"
                	    >
                    	<svelte:fragment slot="helper">
                        	<HelperText>{tran(k.toUpperCase())}</HelperText>
	                    </svelte:fragment>
    	                </Textfield>
					{:else}
    		        	{member.cmember[v] || ''}
					{/if}
					</Cell>
    		        <Cell style="width:  5%; text-align: right;">
					{#if edit_field == v}
							<IconButton class="material-icons" on:click={()=>{ save_edited_field(); }}>save</IconButton>
					{:else}
						{#if window.pnb['self-edit'].includes( v )}
							<IconButton class="material-icons" on:click={()=>{ set_edit_field(v); }}>edit</IconButton>
						{/if}
					{/if}
					</Cell>
					</Row>
			{/each}
			</Body>
		</DataTable>
		</div>
		<br />
		<div>
		<DataTable table$aria-label="Institution Data" style="width: 100%;">
		    <Body>
			{#each Object.entries(inst_fields) as [k,v]}
				{#if typeof member.cinstitution[v] !== 'undefined' }
			        <Row>
    	    	    <Cell style="width: 25%;">{tran(k.toUpperCase())}</Cell>
    		        <Cell>{member.cinstitution[v]}</Cell>
					</Row>
				{/if}
			{/each}
			</Body>
		</DataTable>
		</div>

	</Paper>

	{/await}

	{/if}

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

{/key}

<style>
	.bio-table {
		width: 100%;
		border: 0;
		border-collapse: collapse;
		margin-bottom: 2vmin;
	}
	.my-photo {
		width : 20vmin;
		height: 20vmin;
		border: 1px solid #000;
		outline: 1px solid #FFF;
		border-radius: 2vmin;
		position: relative;
		float: left;
		cursor: pointer;
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