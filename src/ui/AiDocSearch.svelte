<script>

import { marked } from 'marked';
import { tran } from '../utils/tran.js';

import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';
import LinearProgress from '@smui/linear-progress';
import Select, { Option } from '@smui/select';

import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';

import { screen, auth } from '../store.js';
import { member_id, member_mode } from '../store.js';
import { aiDocSearch } from '../utils/pnb-api.js';

let q = '', question = '';
let progress = false, streaming = false, mode = 'title+abstract', limit = "5";
let answer = false;

const handleKeyDown = async ( e ) => {
	if ( streaming ) { return; }
	if (e.key === 'Enter' && !e.shiftKey && question && question.length) {
        e.preventDefault();
		streaming = true;
        progress = true;
		q = question;

		// JS query to vector search: /ai/docsearch/search:<bla>/limit:5
		const res = await aiDocSearch( question, mode, limit );

		// convert to markdown and set answer to it
		answer = "Found documents: " + "\n" + res.map( (v,idx) => (idx+1)+'. ['+v['title_val']+'](/document/'+v['id']+'/view)' ).join('  '+"\n");

		progress = false;
		streaming = false;
	}
}

</script>

<Paper>

       {#if q && q.length}
            <div id="answer">
                {@html ('<p><b>Q:</b> '+q+'</p>')}
                {#if progress}
                    <LinearProgress indeterminate />
                {/if}
                {#if answer}
                    {@html marked.parse('**A:** ' + answer)}
                {/if}
                <div id="hidden-marker">&nbsp;</div>
            </div>
        {/if}

		<Textfield textarea bind:value={question}
            style="width: 100%;  min-height: 20vmin; margin-top: 2vmin;"
            helperLine$style="width: 100%;"
            label={tran('_search_documents_')}
			on:keydown={handleKeyDown}
        >
          <svelte:fragment slot="helper">
            <HelperText>{tran('_search_documents_')}</HelperText>
          </svelte:fragment>
		</Textfield>

      <Select variant="outlined" bind:value={mode} noLabel style="min-width: 300px;">
      	<Option value="title+abstract">{tran('TITLE')} + {tran('ABSTRACT')}</Option>
      	<Option value="title">{tran('TITLE')}</Option>
      	<Option value="abstract">{tran('ABSTRACT')}</Option>
      </Select>

      <Select variant="outlined" bind:value={limit} noLabel style="min-width: 200px;">
      	<Option value="5">5 ENTRIES</Option>
      	<Option value="10">10 ENTRIES</Option>
      	<Option value="15">15 ENTRIES</Option>
      	<Option value="50">50 ENTRIES</Option>
      </Select>

</Paper>

<style>
	#answer {
		width: 100%;
		overflow-y: auto;
		height: 45vh;
		padding: 1vmin;
		box-sizing: border-box;
	}
</style>