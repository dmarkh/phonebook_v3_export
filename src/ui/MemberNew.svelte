<script>

import {meta, router, Route} from 'tinro';

import MemberEditComponent from './MemberEditComponent.svelte';
import AccessDenied from './AccessDenied.svelte';
import PleaseWait from './PleaseWait.svelte';

import { screen } from '../store.js';

import { createMember } from '../utils/pnb-api.js';
import { sleep } from '../utils/sleep.js';
import { auth } from '../store.js';

let pleaseWait = false;

const recordUpdated = async ( memberId, changes, field_values ) => {
    pleaseWait = 'ADDING NEW MEMBER, PLEASE WAIT';

    let data = {
        "status": "active",
        "fields": changes.reduce( (acc,cv) => { acc[cv.field_id] = ( typeof cv.post === 'string' ? cv.post.trim() : cv.post ); return acc; }, {} )
    };

    let rc = await createMember( data );
    await sleep(1000);
    $screen = 'members';

    if ( rc ) {
        router.goto('/member/' + rc.id + '/view' );
    }

    pleaseWait = false;
}

</script>

{#if $auth['grants']['members-edit']}
	<MemberEditComponent memberId={false} institutionId={false} recordUpdated={recordUpdated} />
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
