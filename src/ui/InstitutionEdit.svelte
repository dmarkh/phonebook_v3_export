<script>

import {meta, router, Route} from 'tinro';

import { screen, institution_id } from '../store.js';

import InstitutionEditComponent from './InstitutionEditComponent.svelte';
import AccessDenied from './AccessDenied.svelte';

import PleaseWait from './PleaseWait.svelte';

import { updateInstitution } from '../utils/pnb-api.js';
import { sleep } from '../utils/sleep.js';
import { auth } from '../store.js';

let pleaseWait = false;

const recordUpdated = async ( institutionId, changes, field_values ) => {
    pleaseWait = 'UPDATING INSTITUTION DATA, PLEASE WAIT';

    let data = { [institutionId]: changes.reduce( (acc,cv) => { acc[cv.field_id] = ( typeof cv.post === 'string' ? cv.post.trim() : cv.post ); return acc; }, {} ) };
    let rc = await updateInstitution( data );
    await sleep(1000);

	$institution_id = institutionId;
	$screen = 'institution';

	if ( rc ) {
		router.goto('/institution/' + $institution_id + '/view' );
	}

    pleaseWait = false;
}


</script>

{#if $auth['grants']['institutions-edit']}
<InstitutionEditComponent institutionId={parseInt($institution_id)} recordUpdated={recordUpdated} />

{#if pleaseWait}
<PleaseWait text="{pleaseWait}" />
{/if}

{:else}
	<AccessDenied />
{/if}