<script>

import { tran } from '../utils/tran.js';

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
	getDocumentComments,
	addDocumentComment
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
let comments = false;

let comment_value = '';

const fetchDocument = async ( id ) => {

	mem = await downloadMember( mid );

    doc = await downloadDocument( id );
	console.log( 'doc', doc );

	comments = await getDocumentComments( id );
	console.log('comments', comments);

    title = doc.cdocument.title;
	subtitle = 'COMMENTS SECTION';

    return doc;
}

const postNewComment = async() => {

	if ( !mid || !comment_value || !comment_value.length ) { return; }

	pleaseWait = 'ADDING NEW COMMENT, '.tran('_please_wait_');

	let data = {
		"document_id": $document_id,
		"member_id": mid,
		"member_name": ( mem.cmember.name_first + ' ' + mem.cmember.name_last ),
		"comment": comment_value
	};
	console.log('new comment data', data);

	let rc = await addDocumentComment( data );

	comment_value = '';

	pleaseWait = false;
	refresh = !refresh;
}

</script>

{#key refresh}

{#if mid && $auth['grants']['workflows-view'] }

{#await fetchDocument( $document_id )}

<LinearProgress indeterminate />

{:then}

<Paper>

    {#if pleaseWait}
   	    <PleaseWait text="{pleaseWait}" />
    {:else}

		<DataTable table$aria-label="Document Workflow" style="width: 100%;">
		    <Head>
		        <Row>
		            <Cell columnId="field" style="width: 20%; text-align: center;">
                		<Label>{tran('_field_')}</Label>
        		    </Cell>
		            <Cell columnId="value" style="width: 60%; text-align: center;">
        		        <Label>{tran('_value_')}</Label>
		            </Cell>
        		</Row>
		    </Head>
		    <Body>
		      <Row>
		        <Cell style="width: 20%; font-weight: bold;">
					{tran('TITLE')}
				</Cell>
		        <Cell style="width: 60%; font-weight: bold;">
					[ {doc.cdocument.category} ] {doc.cdocument.title}
				</Cell>
			  </Row>
		      <Row>
		        <Cell style="width: 20%; font-weight: bold;">
					{tran('ABSTRACT')}
				</Cell>
		        <Cell style="width: 60%; font-weight: bold;">
					{doc.cdocument.abstract}
				</Cell>
			  </Row>
		      <Row>
		        <Cell style="width: 20%; font-weight: bold;">
					{tran('URL')}
				</Cell>
		        <Cell style="width: 60%; font-weight: bold;">
					{#if doc.cdocument.url && doc.cdocument.url.length}
					<a target="_blank" href="{doc.cdocument.url}">{doc.cdocument.url}</a>
					{:else}
						{tran('NO URL PROVIDED YET')}
					{/if}
				</Cell>
			  </Row>
			</Body>
		</DataTable>

		{#if $auth['grants']['comments-add']}
		<div style="width: 100%; margin-top: 5vmin;">
			<p>{tran('POST NEW COMMENT')}:</p>
        	<Textfield textarea bind:value={comment_value}
    	        style="width: 100%;"
	            helperLine$style="width: 100%;"
            	label="{tran('COMMENT TEXT')}"
        	    input$maxlength={1024}
    	    >
	          <svelte:fragment slot="helper">
            	<HelperText>{tran('TO BE POSTED BY')}: {mem.cmember.name_first + ' ' + mem.cmember.name_last}</HelperText>
        	    <CharacterCounter>{ typeof comment_value == 'string' ? comment_value.length : 0 } / 1024</CharacterCounter>
    	      </svelte:fragment>
	        </Textfield>

	    	<p style="text-align: center;">
	        <Button on:click={() => { postNewComment(); }} variant="raised">
    	        <ButtonIcon class="material-icons">save</ButtonIcon>
        	    <ButtonLabel>{tran('ADD COMMENT')}</ButtonLabel>
	        </Button>
    		</p>
		</div>
		{/if}

		{#if $auth['grants']['comments-view']}
			{#if comments && comments.length}
			<p>{tran('PREVIOUS COMMENTS')}:</p>
			<div id="comment-blocks">
				{#each comments as item, idx}
                        <Card style="margin-bottom: 1vmin;">
                            <Content>
                                {item.comment}
                            </Content>
                          <Actions fullBleed>
							&nbsp;&nbsp;<b>{item.created}</b>, {item.member_name}
                          </Actions>
                        </Card>
				{/each}
			</div>
			{/if}
		{/if}

	{/if}

</Paper>

{/await}

{:else}
	<AccessDenied />
{/if}

{/key}

<style>
#comment-blocks {
    margin-top: 3vmin;
    background-color: #CCC;
    padding: 1vmin;
    box-sizing: border-box;
    border-radius: 1vmin;
}
</style>
