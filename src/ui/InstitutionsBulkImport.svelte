<script>

import {meta, router, Route} from 'tinro';

import LinearProgress from '@smui/linear-progress';
import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';
import Textfield from '@smui/textfield';
import Checkbox from '@smui/checkbox';
import FormField from '@smui/form-field';
import Select, { Option as SelectOption } from '@smui/select';
import Paper from '@smui/paper';
import AccessDenied from './AccessDenied.svelte';
import readXlsxFile from 'read-excel-file';

import PleaseWait from './PleaseWait.svelte';
import { screen } from '../store.js';

import { auth } from '../store.js';
import { getInstitutions, getInstitutionFields, getInstitutionFieldgroups, createInstitution } from '../utils/pnb-api.js';
import { orderKeys } from '../utils/pnb-download.js';

let valueTypeFiles = null,
	fileIsBeingParsed = false,
	fileData = false,
	fieldData = false,
	fieldValues = [];

let skipFirstRow = false;

let uploading_institutions = false,
    upload_errors = [],
    inst_uploading = false,
    inst_total = false;

const getFieldId = ( name, fields ) => {
	let field = Object.values( fields ).find( f => f.name_fixed == name );
	if ( field ) { return field.id; }
	return false;
}

const checkRORIDexists = ( rorid, inst, fields ) => {
    let rorid_field_id = getFieldId( 'ror_id', fields );
	console.log('checking rorid: ' + rorid );
	console.log('rorid_field_id: ' + rorid_field_id );
	console.log('fields', fields );
    for( const [k,v] of Object.entries( inst ) ) {
        if ( v.fields[rorid_field_id] == rorid ) {
            return true;
        }
    }
    return false;
}

const importNewData = async () => {

	console.log('...importing new data...');
	console.log( 'field values', fieldValues );
	console.log( 'field data', fieldData );
	console.log( 'skipFirstRow', skipFirstRow );
	console.log( 'file data', fileData );

	let inst = await getInstitutions();

	inst_uploading = 0;
	inst_total = fileData.length;
	uploading_institutions = true;

    let ror_id_idx = false,
        name_full_idx = false;

    for ( let i = 0, ilen = fieldValues.length; i < ilen; i++ ) {
        if ( fieldValues[i] === 'ror_id' ) {
            ror_id_idx = i;
        }
        if ( fieldValues[i] === 'name_full' ) {
            name_full_idx = i;
        }
    }
    if ( ror_id_idx === false ) {
        upload_errors = [ ...upload_errors, 'No ROR ID selected'];
        console.log('No ROR ID selected', upload_errors);
        return false;
    }
    if ( name_full_idx === false ) {
        upload_errors = [ ...upload_errors, 'Full Name must present' ];
        console.log('Full Name must present', upload_errors);
        return false;
    }

	for ( let i = 0, ilen = fileData.length; i < ilen; i++ ) {
		inst_uploading = i + 1;
		let fields = {}, fid = false, skip = false;
		for ( let j = 0, jlen = fieldValues.length; j < jlen; j++ ) {
			if ( fieldValues[j] === 'ror_id' ) {
				let ror_id_exists = checkRORIDexists( fileData[i][j], inst, fieldData.fields );
				if ( ror_id_exists ) {
					upload_errors = [ ...upload_errors, 'Institution already exists:' + fileData[i][j] + ' - skipping entry ' + i ];
					skip = true;
					break;
				}
			}
			fid = getFieldId( fieldValues[j], fieldData.fields );
			if ( fid ) {
				fields[ fid ] = fileData[i][j];
			} else {
				upload_errors = [ ...upload_errors, 'Unknown field: ' + fieldValues[j] + ' - skipping entry ' + i ];
				skip = true;
				break;
			}
		}
		if ( !skip ) {
		    let data = {
    		    "status": "active",
        		"fields": fields
	    	};
			console.log('inst data', data);
    		let rc = await createInstitution( data );
		}
	}
}

const fetchFields = async () => {
	let fields = await getInstitutionFields();
	let groups = await getInstitutionFieldgroups();
	let fields_ordered = orderKeys( fields, (a,b) => a.group == b.group ? ( a.weight - b.weight ) : groups[a.group].weight - groups[b.group].weight );
	return { fields, groups, fields_ordered };
}

const processUploadedFile = async () => {
	fileIsBeingParsed = true;
	if ( !fieldData ) {
		fieldData = await fetchFields();
	}
	let rows = await readXlsxFile(valueTypeFiles[0]);
	for( let i = 0, ilen = rows[0].length; i < ilen; i++ ) {
		fieldValues[i] = "";
	}

    uploading_institutions = false;
    upload_errors = [];
    inst_uploading = false;
    inst_total = false;

	fileIsBeingParsed = false;
	fileData = rows;
}

$: if (valueTypeFiles != null && valueTypeFiles.length) {
	processUploadedFile();
}

</script>

{#if $auth['grants']['institutions-bulk-import']}

<div style="text-align: center;" class="mdc-typography--headline4">BULK IMPORT FROM <b>XLSX</b>: INSTITUTIONS</div>

<div class="columns">
	<div class="hide-file-ui">
		<Textfield variant="filled" bind:files={valueTypeFiles} label="SELECT XLSX FILE" type="file" accept=".xlsx" />
	</div>
	{#if fileData}
	<div>
	<FormField>
		<Checkbox bind:checked={skipFirstRow} variant="outlined" />
		<span slot="label">SKIP FIRST/HEADER ROW</span>
		</FormField>
	</div>
	{/if}
</div>

{#if uploading_institutions}

	<p>Uploading institutions: {inst_uploading} / {inst_total}</p>

    {#if upload_errors.length}
        <p><span style="color: #900;">UPLOAD ERRORS:</span></p>
		{#each upload_errors as val, id}
        <p>{val}</p>
		{/each}
    {/if}

    {#if inst_uploading == inst_total}
        {#if !upload_errors.length}
            <p>ALL INSTITUTIONS WERE UPLOADED SUCCESSFULLY</p>
        {:else}
            <p>INSTITUTIONS WERE UPLOADED, PLEASE CHECK ERROR MESSAGES ABOVE</p>
        {/if}
    {/if}

{:else}

{#if fileIsBeingParsed}
	<LinearProgress indeterminate />
{/if}

{#if fileData}

<Paper>
<p>PLEASE SELECT AN APPROPRIATE FIELD NAME FOR EACH COLUMN. NOTE THAT AT LEAST ONE COLUMN SHOULD BE ASSIGNED TO "ROR ID".</p>
<DataTable table$aria-label="Imported Data" style="width: 100%;">
    <Head>
        <Row>
            {#each fileData[0] as hdr, idx (idx)}
            <Cell columnId="{idx}">
				<select bind:value={fieldValues[ idx ]} style="width: 20vmin;">
					<option value="">NOT SET</option>
				{#each fieldData.fields_ordered as id (id) }
					<option value="{fieldData.fields[id].name_fixed}">{fieldData.fields[id].name_desc}</option>
				{/each}
				</select>
            </Cell>
            {/each}
        </Row>
    </Head>
    <Body>
    {#each fileData as row, idx (idx)}
		{#if !(idx == 0 && skipFirstRow)}
		<Row>
		{#each row as row_data, row_idx ( row_idx ) }
			<Cell>
				{row_data || ''}
			</Cell>
		{/each}
		</Row>
		{/if}
    {/each}
    </Body>
</DataTable>
</Paper>

<div class="save-button">
<Fab color="primary" on:click={() => { importNewData(); }} extended>
	<FabIcon class="material-icons">save</FabIcon>
	<FabLabel>IMPORT NEW INSTITUTIONS</FabLabel>
</Fab>
</div>

{/if}

{/if}

{:else}
	<AccessDenied />
{/if}

<style>
.hide-file-ui :global(input[type='file']::file-selector-button) {
	display: none;
}
.hide-file-ui :global(:not(.mdc-text-field--label-floating) input[type='file']) {
	color: transparent;
}
.columns {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
}
.columns > * {
    margin-right: 2vmin;
	margin-bottom: 5vmin;
}
.save-button {
    position: absolute;
    bottom: 2vmin;
    right: 2vmin;
}
</style>