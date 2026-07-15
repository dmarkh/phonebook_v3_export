<script>

import {meta, router, Route} from 'tinro';

import { screen, event_id } from '../store.js';

import EventEditComponent from './EventEditComponent.svelte';
import AccessDenied from './AccessDenied.svelte';

import PleaseWait from './PleaseWait.svelte';

import { updateEvent } from '../utils/pnb-api.js';
import { sleep } from '../utils/sleep.js';
import { auth } from '../store.js';

let pleaseWait = false;

const recordUpdated = async ( eventId, changes, field_values ) => {
    pleaseWait = 'UPDATING EVENT DATA, PLEASE WAIT';

    let data = { [eventId]: changes.reduce( (acc,cv) => { acc[cv.field_id] = ( typeof cv.post === 'string' ? cv.post.trim() : cv.post ); return acc; }, {} ) };
    let rc = await updateEvent( data );
    await sleep(1000);

	$event_id = eventId;
	$screen = 'event';

	if ( rc ) {
		router.goto('/event/' + $event_id + '/view' );
	}

    pleaseWait = false;
}


</script>

{#if $auth['grants']['events-edit']}
<EventEditComponent eventId={parseInt($event_id)} recordUpdated={recordUpdated} />

{#if pleaseWait}
<PleaseWait text="{pleaseWait}" />
{/if}

{:else}
	<AccessDenied />
{/if}