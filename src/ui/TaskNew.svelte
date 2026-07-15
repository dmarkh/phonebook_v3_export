<script>

import {meta, router, Route} from 'tinro';

import TaskEditComponent from './TaskEditComponent.svelte';
import AccessDenied from './AccessDenied.svelte';
import PleaseWait from './PleaseWait.svelte';

import { screen } from '../store.js';
import { createTask } from '../utils/pnb-api.js';
import { sleep } from '../utils/sleep.js';
import { auth } from '../store.js';

let pleaseWait = false;

const recordUpdated = async ( taskId, changes, field_values ) => {
    pleaseWait = 'ADDING NEW TASK, PLEASE WAIT';

    let data = {
		"status": "active",
		"fields": changes.reduce( (acc,cv) => { acc[cv.field_id] = ( typeof cv.post === 'string' ? cv.post.trim() : cv.post ); return acc; }, {} )
	};
    let rc = await createTask( data );

    await sleep(1000);

	$screen = 'tasks';
	if ( rc && rc.id ) {
		router.goto('/task/' + rc.id + '/view' );
	}
    pleaseWait = false;
}

</script>

{#if $auth['grants']['tasks-edit']}
	<TaskEditComponent taskId={false} recordUpdated={recordUpdated} />
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