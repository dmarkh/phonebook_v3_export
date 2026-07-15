<script>

import {meta, router, Route} from 'tinro';
import { screen, member_id } from '../store.js';

import MemberEditComponent from './MemberEditComponent.svelte';
import PleaseWait from './PleaseWait.svelte';
import AccessDenied from './AccessDenied.svelte';

import { updateMember } from '../utils/pnb-api.js';
import { sleep } from '../utils/sleep.js';
import { auth } from '../store.js';

let pleaseWait = false;

const recordUpdated = async ( memberId, changes, field_values ) => {
	pleaseWait = 'UPDATING MEMBER DATA, PLEASE WAIT';

	let data = { [memberId]: changes.reduce( (acc,cv) => { acc[cv.field_id] = ( typeof cv.post === 'string' ? cv.post.trim() : cv.post ); return acc; }, {} ) };

	let rc = await updateMember( data );
	await sleep(1000);

	$member_id = memberId;
	$screen = 'member';

    if ( rc ) {
        router.goto('/member/' + $member_id + '/view' );
    }

	pleaseWait = false;
}

</script>

{#if $auth['grants']['members-edit']}

<MemberEditComponent memberId={parseInt($member_id)} recordUpdated={recordUpdated} />

{#if pleaseWait}
<PleaseWait text="{pleaseWait}" />
{/if}

{:else}
	<AccessDenied />
{/if}