<script>

import {meta, router, Route} from 'tinro';

import DocumentEditComponent from './DocumentEditComponent.svelte';
import AccessDenied from './AccessDenied.svelte';
import PleaseWait from './PleaseWait.svelte';

import { screen } from '../store.js';
import { createDocument } from '../utils/pnb-api.js';
import { sleep } from '../utils/sleep.js';
import { auth } from '../store.js';

let pleaseWait = false;

const recordUpdated = async ( documentId, changes, field_values ) => {
    pleaseWait = 'ADDING NEW DOCUMENT, PLEASE WAIT';

    let data = {
		"status": "active",
		"fields": changes.reduce( (acc,cv) => { acc[cv.field_id] = ( typeof cv.post === 'string' ? cv.post.trim() : cv.post ); return acc; }, {} )
	};
    let rc = await createDocument( data );

    await sleep(1000);

	$screen = 'documents';
	if ( rc && rc.id ) {
		router.goto('/document/' + rc.id + '/view' );
	}
    pleaseWait = false;
}

</script>

{#if $auth['grants']['documents-edit']}
	<DocumentEditComponent documentId={false} recordUpdated={recordUpdated} />
	{#if pleaseWait}
		<PleaseWait text="{pleaseWait}" />
	{/if}
{:else}
	<AccessDenied />
{/if}
