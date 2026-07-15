<script>

import {meta, router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';
import CharacterCounter from '@smui/textfield/character-counter';

import Card, {
    Content,
    PrimaryAction,
    Actions,
    ActionButtons,
    ActionIcons,
  } from '@smui/card';

import PleaseWait from './PleaseWait.svelte';
import AccessDenied from './AccessDenied.svelte';
import { sleep } from '../utils/sleep.js';

import { find_field_id } from '../utils/pnb-search.js';

import {
	getDocument,
	getDocumentFields,
	getWorkflowMap,
	getWorkflow,
	addWorkflowProgress,
	getConfiguredWorkflowBlocks,
	getWorkflowBlock,
	advanceWorkflow
} from '../utils/pnb-api.js';

import { downloadMember, downloadDocument } from '../utils/pnb-download.js';
import { document_id } from '../store.js';
import { auth } from '../store.js';

const mid = window.pnb.mid ? parseInt(window.pnb.mid) : null;

let title = '', subtitle = '';
let pleaseWait = false;
let refresh = false;

let mem = false;
let doc = false;
let wmap = false;
let workflow = false;
let comments = false;
let cblocks = [];
let cblock = false;
let block;

let comment_value = '';

const fetchDocument = async ( id ) => {

	mem = await downloadMember( mid );
    doc = await downloadDocument( id );

	wmap = await getWorkflowMap( id );											 // get workflow mapping to the document
    if ( wmap.map ) { wmap = wmap.map; }

	if ( wmap ) {

        workflow = await getWorkflow( wmap.workflow_id );						 // get workflow
        if ( workflow.workflow ) { workflow = workflow.workflow; }

		if ( workflow ) {

			cblocks = await getConfiguredWorkflowBlocks( wmap.workflow_id );	 // all blocks for this workflow
			if ( cblocks.data ) { cblocks = cblocks.data; }

			if ( cblocks ) {
				cblock = cblocks.find( b => b.step_id == wmap.current_step_id ); // configured users/groups, cblock.group_id, cblock.group_role_ids, cblock.member_ids

				if ( cblock ) {
					block = await getWorkflowBlock( cblock.block_id );
					if ( block.block ) { block = block.block; } 				 // block.block_type_id
				}
			}
		}
	}

	// FIXME: determine if this member is allowed to the review - replace the above with a single call to workflow

    title = doc.cdocument.title;
	subtitle = 'REVIEW';

    return doc;
}

const postReviewComment = async() => {
	if ( !mid ) { return; }
	pleaseWait = 'PROCESSING REVIEW, PLEASE WAIT';

	let data = {
		"document_id": $document_id,
		"workflow_id": workflow.id,
		"step_id": wmap.current_step_id,
		"member_id": mid,
		"member_name": ( mem.cmember.name_first + ' ' + mem.cmember.name_last ),
		"operation": "comment",
		"metadata": comment_value
	};

	let rc = await addWorkflowProgress( data );
	comment_value = '';

	router.goto( '/document/' + $document_id + '/workflow' );

	refresh = !refresh;
}

const postReviewAccept = async() => {
	if ( !mid ) { return; }
	pleaseWait = 'PROCESSING REVIEW, PLEASE WAIT';

	let data = {
		"document_id": $document_id,
		"workflow_id": workflow.id,
		"step_id": wmap.current_step_id,
		"member_id": mid,
		"member_name": ( mem.cmember.name_first + ' ' + mem.cmember.name_last ),
		"operation": "accept",
		"metadata": comment_value
	};

	let rc = await addWorkflowProgress( data );
	rc = await advanceWorkflow( $document_id );
	await sleep(1000);

	router.goto( '/document/' + $document_id + '/workflow' );

	comment_value = '';

	refresh = !refresh;
}

const postReviewDecline = async() => {
	if ( !mid ) { return; }
	pleaseWait = 'PROCESSING REVIEW, PLEASE WAIT';

	let data = {
		"document_id": $document_id,
		"workflow_id": workflow.id,
		"step_id": wmap.current_step_id,
		"member_id": mid,
		"member_name": ( mem.cmember.name_first + ' ' + mem.cmember.name_last ),
		"operation": "decline",
		"metadata": comment_value
	};
	let rc = await addWorkflowProgress( data );

	rc = await advanceWorkflow( $document_id );
	await sleep(1000);

	comment_value = '';

	router.goto( '/document/' + $document_id + '/workflow' );

	refresh = !refresh;
}

const checkReviewAccess = () => {
	// TODO: check if member is actually a reviewer in the workflow
	return true;
}

</script>

{#key refresh}

{#if mid && $auth['grants']['reviews-view'] }

{#await fetchDocument( $document_id )}

<LinearProgress indeterminate />

{:then}

{#if checkReviewAccess()}

<Paper>

    {#if pleaseWait}
   	    <PleaseWait text="{pleaseWait}" />
    {:else}


		<div style="text-align: center;" class="mdc-typography--headline4">REVIEW</div>
		<DataTable table$aria-label="Document Workflow" style="width: 100%;">
		    <Head>
		        <Row>
		            <Cell columnId="field" style="width: 20%; text-align: center;">
                		<Label>FIELD</Label>
        		    </Cell>
		            <Cell columnId="value" style="width: 60%; text-align: center;">
        		        <Label>VALUE</Label>
		            </Cell>
        		</Row>
		    </Head>
		    <Body>
		      <Row>
		        <Cell style="width: 20%; font-weight: bold;">
					TITLE
				</Cell>
		        <Cell style="width: 60%; font-weight: bold;">
					[ {doc.cdocument.category} ] {doc.cdocument.title}
				</Cell>
			  </Row>
		      <Row>
		        <Cell style="width: 20%; font-weight: bold;">
					ABSTRACT
				</Cell>
		        <Cell style="width: 60%; font-weight: bold;">
					{doc.cdocument.abstract}
				</Cell>
			  </Row>
		      <Row>
		        <Cell style="width: 20%; font-weight: bold;">
					URL
				</Cell>
		        <Cell style="width: 60%; font-weight: bold;">
					{#if doc.cdocument.url && doc.cdocument.url.length}
		            <Button on:click={() => { postReviewDecline(); }} variant="raised" href="{doc.cdocument.url}" target="_blank">
        		        <ButtonIcon class="material-icons">open_in_new</ButtonIcon>
                		<ButtonLabel>{doc.cdocument.url}</ButtonLabel>
		            </Button>
					{:else}
						NO URL PROVIDED YET
					{/if}
				</Cell>
			  </Row>
			</Body>
		</DataTable>

		{#if $auth['grants']['comments-add']}
		<div style="width: 100%; margin-top: 5vmin;">
			<p>POST REVIEW:</p>
        	<Textfield textarea bind:value={comment_value}
    	        style="width: 100%;"
	            helperLine$style="width: 100%;"
            	label="COMMENT TEXT"
        	    input$maxlength={1024}
    	    >
	          <svelte:fragment slot="helper">
            	<HelperText>TO BE POSTED BY: {mem.cmember.name_first + ' ' + mem.cmember.name_last}</HelperText>
        	    <CharacterCounter>{ typeof comment_value == 'string' ? comment_value.length : 0 } / 1024</CharacterCounter>
    	      </svelte:fragment>
	        </Textfield>

	    	<p style="text-align: center;">
	        <Button on:click={() => { postReviewDecline(); }} variant="raised">
    	        <ButtonIcon class="material-icons">save</ButtonIcon>
        	    <ButtonLabel>DECLINE</ButtonLabel>
	        </Button>

			&nbsp;&nbsp;

	        <Button on:click={() => { postReviewComment(); }} variant="raised">
    	        <ButtonIcon class="material-icons">save</ButtonIcon>
        	    <ButtonLabel>POST COMMENT</ButtonLabel>
	        </Button>

			&nbsp;&nbsp;

	        <Button on:click={() => { postReviewAccept(); }} variant="raised">
    	        <ButtonIcon class="material-icons">save</ButtonIcon>
        	    <ButtonLabel>ACCEPT</ButtonLabel>
	        </Button>
    		</p>
		</div>
		{/if}
	{/if}

</Paper>

{:else}

	<AccessDenied />

{/if}

{/await}

{:else}
	<AccessDenied />
{/if}

{/key}

<style>

</style>
