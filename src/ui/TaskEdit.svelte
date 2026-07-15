<script>

import {meta, router, Route} from 'tinro';

import { screen, task_id } from '../store.js';

import TaskEditComponent from './TaskEditComponent.svelte';
import AccessDenied from './AccessDenied.svelte';

import PleaseWait from './PleaseWait.svelte';

import { updateTask } from '../utils/pnb-api.js';
import { sleep } from '../utils/sleep.js';
import { auth } from '../store.js';

let pleaseWait = false;

const recordUpdated = async ( taskId, changes, field_values ) => {
    pleaseWait = 'UPDATING TASK DATA, PLEASE WAIT';

    let data = { [taskId]: changes.reduce( (acc,cv) => { acc[cv.field_id] = ( typeof cv.post === 'string' ? cv.post.trim() : cv.post ); return acc; }, {} ) };
    let rc = await updateTask( data );
    await sleep(1000);

	$task_id = taskId;
	$screen = 'task';

	if ( rc ) {
		router.goto('/task/' + $task_id + '/view' );
	}

    pleaseWait = false;
}


</script>

{#if $auth['grants']['tasks-edit']}
<TaskEditComponent taskId={parseInt($task_id)} recordUpdated={recordUpdated} />

{#if pleaseWait}
<PleaseWait text="{pleaseWait}" />
{/if}

{:else}
	<AccessDenied />
{/if}