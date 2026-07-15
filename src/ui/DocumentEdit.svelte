<script>

import { tran } from '../utils/tran.js';

import {meta, router, Route} from 'tinro';

import { screen, document_id } from '../store.js';

import DocumentEditComponent from './DocumentEditComponent.svelte';
import AccessDenied from './AccessDenied.svelte';

import PleaseWait from './PleaseWait.svelte';

import { updateDocument } from '../utils/pnb-api.js';
import { sleep } from '../utils/sleep.js';
import { auth } from '../store.js';

let pleaseWait = false;

const recordUpdated = async ( documentId, changes, field_values ) => {
    pleaseWait = 'UPDATING DOCUMENT DATA, ' + tran('_please_wait_');

    let data = { [documentId]: changes.reduce( (acc,cv) => { acc[cv.field_id] = ( typeof cv.post === 'string' ? cv.post.trim() : cv.post ); return acc; }, {} ) };
    let rc = await updateDocument( data );
    await sleep(1000);

	$document_id = documentId;
	$screen = 'document';

	if ( rc ) {
		router.goto('/document/' + $document_id + '/view' );
	}

    pleaseWait = false;
}


</script>

{#if $auth['grants']['documents-edit']}
<DocumentEditComponent documentId={parseInt($document_id)} recordUpdated={recordUpdated} />

{#if pleaseWait}
	<PleaseWait text="{pleaseWait}" />
{/if}

{:else}
	<AccessDenied />
{/if}