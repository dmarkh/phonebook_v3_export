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

export let aitoolkit;
let q = '', question = '';
let progress = false;
let answer = false;
let streaming = false;
let model = window.pnb.ai.modes['ask-scientist'][0];

const handleKeyDown = async ( e ) => {
	if ( streaming ) { return false; }
	if (e.key === 'Enter' && !e.shiftKey && question && question.length) {
        e.preventDefault();
        streaming = true;
        q = question;
        question = '';
        answer = '';
        progress = true;
        const result = await aitoolkit.processStream( 'ask-scientist', model, q );
		try {
    	    for await ( const textPart of result.textStream ) {
	            progress = false;
            	answer = answer + textPart;
        	    setTimeout(() => {
    	            const el = document.getElementById("hidden-marker");
	                if ( el ) {
                    	el.scrollIntoView({ behavior: "instant", block: "end" });
                	}
            	}, 0);
        	}
		} catch(e) {
			progress = false;
			answer = answer + '[ERROR: CURRENT AI MODEL NOT RESPONDING, TRY ANOTHER MODEL]';
       	    setTimeout(() => {
   	            const el = document.getElementById("hidden-marker");
                if ( el ) {
                   	el.scrollIntoView({ behavior: "instant", block: "end" });
               	}
           	}, 0);
			console.log( e );
		}
        streaming = false;
	}
}

</script>

<Paper>

	{#if !aitoolkit || !aitoolkit.is_initialized() }
		<p>WARNING: AI API KEY IS NOT CONFIGURED PROPERLY OR USAGE IS OUT OF LIMIT</p>
	{:else}

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
            label={tran('_ai_qa_hint_')}
			on:keydown={handleKeyDown}
        >
          <svelte:fragment slot="helper">
            <HelperText>{tran('ask_a_scientist_')}</HelperText>
          </svelte:fragment>
		</Textfield>

      <Select variant="outlined" bind:value={model} noLabel style="min-width: 450px;">
		{#each window.pnb.ai.modes['ask-scientist'] as md}
	        <Option value={md}>{md}</Option>
		{/each}
      </Select>

	{/if}

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