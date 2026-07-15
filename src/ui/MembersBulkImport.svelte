<script>

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

import { auth } from '../store.js';
import { createMember, getMembers, getMemberFields, getMemberFieldgroups } from '../utils/pnb-api.js';
import { getInstitutions, getInstitutionFields, getInstitutionFieldgroups } from '../utils/pnb-api.js';
import { convertInstitutions } from '../utils/pnb-convert.js';
import { orderKeys } from '../utils/pnb-download.js';

let valueTypeFiles = null,
	fileIsBeingParsed = false,
	fileData = false,
	fieldData = false,
	fieldValues = [];

let skipFirstRow = false;
let uploading_members = false,
	upload_errors = [],
	members_uploading = false,
	members_total = false;

const getFieldId = ( name, fields ) => {
    let field = Object.values( fields ).find( f => f.name_fixed == name );
    if ( field ) { return field.id; }
    return false;
}

const getInstIDbyRORID = ( rorid, inst, inst_fields ) => {
	let inst_id = false,
		ror_field_id = getFieldId( 'ror_id', inst_fields );
	for( const [k,v] of Object.entries( inst ) ) {
		if ( v.fields[ror_field_id] == rorid ) {
			return k;
		}
	}
	return inst_id;
}

const checkORCIDexists = ( orcid, mem, fields ) => {
	let orcid_field_id = getFieldId( 'orcid_id', fields );
	for( const [k,v] of Object.entries( mem ) ) {
		if ( v.fields[orcid_field_id] == orcid ) {
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

	members_uploading = 0,
	members_total = fileData.length;

	uploading_members = true;

	let mem = await getMembers();
    let inst = await getInstitutions();
    let items = await convertInstitutions( inst );
	let inst_field_id = getFieldId( 'institution_id', fieldData.fields );

	let ror_id_idx = false,
		orcid_id_idx = false,
		name_first_idx = false,
		name_last_idx = false;

	for ( let i = 0, ilen = fieldValues.length; i < ilen; i++ ) {
		if ( fieldValues[i] === 'institution__ror_id' ) {
			ror_id_idx = i;
		}
		if ( fieldValues[i] === 'orcid_id' ) {
			orcid_id_idx = i;
		}
		if ( fieldValues[i] === 'name_first' ) {
			name_first_idx = i;
		}
		if ( fieldValues[i] === 'name_last' ) {
			name_last_idx = i;
		}
	}
	if ( ror_id_idx === false ) {
		upload_errors = [ ...upload_errors, 'No ROR ID selected'];
		console.log('No ROR ID selected', upload_errors);
		return false;
	}
	if ( orcid_id_idx === false ) {
		upload_errors = [ ...upload_errors, 'No ORCID selected' ];
		console.log('No ORCID selected', upload_errors);
		return false;
	}
	if ( name_first_idx === false || name_last_idx === false ) {
		upload_errors = [ ...upload_errors, 'First and Last Names must present' ];
		console.log('First and Last Names must present');
		return false;
	}

	for ( let i = 0, ilen = fileData.length; i < ilen; i++ ) {
		members_uploading = (i+1);
		let fields = {}, skip = false;
		for ( let j = 0, jlen = fieldValues.length; j < jlen; j++ ) {
			if ( fieldValues[j] == "" ) { continue; } // do not import unmarked field
			if ( fieldValues[j] === 'institution__ror_id' ) {
				let inst_id = getInstIDbyRORID( fileData[i][j], inst, fieldData.ifields );
				if ( inst_id === false ) {
					upload_errors = [ ...upload_errors, 'unrecognized institution: ' + fileData[i][j] ];
					skip = true;
					fields = false;
				} else {
					fields[ inst_field_id ] = inst_id;
				}
			} else if ( fieldValues[j] === 'orcid_id' ) {
				if ( !checkORCIDexists( fileData[i][j], mem, fieldData.fields ) ) {
					let member_field_id = getFieldId( fieldValues[j], fieldData.fields );
					if ( !member_field_id ) {
						upload_errors = [ ...upload_errors, 'unrecognized field: ' + fieldValues[j] ];
						skip = true;
						fields = false;
					} else {
						fields[ member_field_id ] = fileData[i][j];
					}
				} else {
					upload_errors = [ ...upload_errors, 'user already exists with ORCID: ' + fileData[i][j] ];
					skip = true;
					fields = false;
				}
			} else {
				let member_field_id = getFieldId( fieldValues[j], fieldData.fields );
				if ( !member_field_id ) {
					upload_errors = [ ...upload_errors, 'unrecognized field: ' + fieldValues[j] ];
					skip = true;
					fields = false;
				} else {
					fields[ member_field_id ] = fileData[i][j];
				}
			}
			if ( skip ) {
				upload_errors = [ ...upload_errors, 'skipped record ' + i + ' because of errors' ];
				fields = false;
				break;
			}
		}
		if ( fields !== false ) {
		    let data = {
	    		"status": "active",
        		"fields": fields
    		};
			console.log('member data', data );
 			let rc = await createMember( data );
		}
	}

}

const fetchFields = async () => {
	let fields = await getMemberFields();
	let groups = await getMemberFieldgroups();
	let ifields = await getInstitutionFields();
	let igroups = await getInstitutionFieldgroups();
	let fields_ordered = orderKeys( fields, (a,b) => a.group == b.group ? ( a.weight - b.weight ) : groups[a.group].weight - groups[b.group].weight );
	let ifields_ordered = orderKeys( ifields, (a,b) => a.group == b.group ? ( a.weight - b.weight ) : igroups[a.group].weight - igroups[b.group].weight );
	return { fields, groups, fields_ordered, ifields, igroups, ifields_ordered };
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

	uploading_members = false;
	upload_errors = [];
	members_uploading = false;
	members_total = false;

	fileIsBeingParsed = false;
	fileData = rows;
}

$: if (valueTypeFiles != null && valueTypeFiles.length) {
	processUploadedFile();
}

</script>

{#if $auth['grants']['members-bulk-import']}

<div style="text-align: center;" class="mdc-typography--headline4">BULK IMPORT FROM XLSX: MEMBERS</div>

{#if uploading_members}

<p>Uploading: {members_uploading} / {members_total} members uploaded.</p>

	{#if upload_errors.length}
		<p><span style="color: #900;">UPLOAD ERRORS:</span></p>
		{#each upload_errors as val, id}
			<p>{val}</p>
		{/each}
	{/if}

	{#if members_uploading == members_total}
		{#if !upload_errors.length}
			<p>ALL MEMBERS WERE UPLOADED SUCCESSFULLY</p>
		{:else}
			<p>MEMBERS WERE UPLOADED, PLEASE CHECK ERROR MESSAGES ABOVE</p>
		{/if}
	{/if}

{:else}

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

{#if fileIsBeingParsed}
	<LinearProgress indeterminate />
{/if}

{#if fileData}
<Paper>
<p>PLEASE SELECT AN APPROPRIATE FIELD NAME FOR EACH COLUMN. NOTE THAT "ROR ID" AND "ORCID" COLUMNS ARE REQUIRED.</p>
<DataTable table$aria-label="Imported Data" style="width: 100%;">
    <Head>
        <Row>
            {#each fileData[0] as hdr, idx (idx)}
            <Cell columnId="{idx}">
				<select bind:value={fieldValues[ idx ]} style="width: 20vmin;">
					<option value="">NOT SET</option>
				{#each fieldData.fields_ordered as id (id) }
					<option value="{fieldData.fields[id].name_fixed}">M: {fieldData.fields[id].name_desc}</option>
				{/each}
				{#each fieldData.ifields_ordered as id (id) }
					<option value="{'institution__' + fieldData.ifields[id].name_fixed}">I: {fieldData.ifields[id].name_desc}</option>
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
	<FabLabel>IMPORT NEW MEMBERS</FabLabel>
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