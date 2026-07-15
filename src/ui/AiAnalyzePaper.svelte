<script>

import { tran } from '../utils/tran.js';

import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';

import { screen, auth } from '../store.js';
import { member_id, member_mode } from '../store.js';

export let aitoolkit;

</script>

<Paper>

    {#if !aitoolkit || !aitoolkit.is_initialized() }
        <p>WARNING: AI API KEY IS NOT CONFIGURED PROPERLY OR USAGE IS OUT OF LIMIT</p>
    {:else}
		<ul>TODO: ANALYZE SCIENTIFIC PAPERS
			<li>UPLOAD A PDF FILE</li>
			<li>SEARCH DOCUMENTS IN INVENIO</li>
		</ul>
	{/if}

</Paper>

<style>

</style>