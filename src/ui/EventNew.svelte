<script>

import {meta, router, Route} from 'tinro';

import EventEditComponent from './EventEditComponent.svelte';
import AccessDenied from './AccessDenied.svelte';
import PleaseWait from './PleaseWait.svelte';

import { screen } from '../store.js';
import { createEvent } from '../utils/pnb-api.js';
import { sleep } from '../utils/sleep.js';
import { auth } from '../store.js';

let pleaseWait = false;

const recordUpdated = async ( eventId, changes, field_values ) => {
    pleaseWait = 'ADDING NEW EVENT, PLEASE WAIT';

    let data = {
		"status": "active",
		"fields": changes.reduce( (acc,cv) => { acc[cv.field_id] = ( typeof cv.post === 'string' ? cv.post.trim() : cv.post ); return acc; }, {} )
	};
    let rc = await createEvent( data );

    await sleep(1000);

	$screen = 'events';
	if ( rc && rc.id ) {
		router.goto('/event/' + rc.id + '/view' );
	}
    pleaseWait = false;
}

</script>

{#if $auth['grants']['events-edit']}
	<EventEditComponent eventId={false} recordUpdated={recordUpdated} />
	{#if pleaseWait}
		<PleaseWait text="{pleaseWait}" />
	{/if}
{:else}
	<AccessDenied />
{/if}

<style>
    :global(.mdc-text-field__input::-webkit-calendar-picker-indicator) {
        display: initial !important;
    }
</style>