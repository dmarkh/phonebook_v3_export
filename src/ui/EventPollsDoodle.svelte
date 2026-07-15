<script>

import { onMount } from 'svelte';
import { afterUpdate } from 'svelte';
import { router, Route } from 'tinro';

import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button, { Icon as ButtonIcon, Label as ButtonLabel } from '@smui/button';
import Paper, { Content } from '@smui/paper';

import Autocomplete from '@smui-extra/autocomplete';
import Chip, { Set, TrailingAction, Text } from '@smui/chips';
import LinearProgress from '@smui/linear-progress';
import Select, { Option } from '@smui/select';
import IconButton from '@smui/icon-button';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';
import CharacterCounter from '@smui/textfield/character-counter';
import Dialog, { Title as DialogTitle, Content as DialogContent, Actions as DialogActions, InitialFocus as DialogInitialFocus } from '@smui/dialog';

import AccessDenied from './AccessDenied.svelte';
import EventPollCalendarWeek from './EventPollCalendarWeek.svelte';

import { auth } from '../store.js';
import { screen } from '../store.js';

import { createEventPoll, voteEventPoll, eventPollAddMembers, eventPollAddGroups } from '../utils/pnb-api.js';
import { downloadMember } from '../utils/pnb-download.js';

import { getMembers, getGroups } from '../utils/pnb-api.js';
import { convertMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';

import time_to_ampm from '../utils/time_to_ampm.js';

const mid = window.pnb.mid || 0;

let setup = {
    "title"         : "New Event",
	"location"		: "",
    "start_time"    :  9,
    "end_time"      : 18,
    "timezone"      : ( Intl.DateTimeFormat().resolvedOptions().timeZone || "" ),
    "groups"        : [],
    "members"       : [],
    "seldays"       : [],
    "marked"        : { [mid]: [] }
};

let step = 1;

let value_group = '';
let value_member = '';

let members = false;
let members_value = '';
let members_notified = false;

let groups = false;
let groups_value = '';
let groups_notified = false;

const get_members_groups = async () => {
    const mem = await getMembers();
    members = await convertMembers( mem );
    sortConvertedMembers( members );
    groups = await getGroups();
}

const handle_member_selection = ( event ) => {
    event.preventDefault();
    if ( setup.members.find( m => m.id == event.detail.id ) ) { return; }
    setup.members = [ ...setup.members, event.detail ];
}

const handle_group_selection = ( event ) => {
    event.preventDefault();
    if ( setup.groups.find( m => m.id == event.detail.id ) ) { return; }
    setup.groups = [ ...setup.groups, event.detail ];
}

const createEPoll = async () => {

	console.log('setup', setup);

    const data = {
        mid     : ( window.pnb.mid || 0 ),
        type    : 'doodle',
        title   : setup.title,
        location: setup.location,
        timezone: setup.timezone,
        hr_start: 0,
        hr_end  : 0,
        days: JSON.parse(JSON.stringify(setup.seldays))
    };

    const res = await createEventPoll( data );

    if ( res && res.id ) {

        if ( setup.members && setup.members.length ) {
            const data = { "poll_id": res.id, "members_ids": setup.members.map( m => m.id ) };
            await eventPollAddMembers( data );
        }

        if ( setup.groups && setup.groups.length ) {
            const data = { "poll_id": res.id, "groups_ids": setup.groups.map( g => g.id ) };
            await eventPollAddGroups( data );
        }

		const m = await downloadMember( mid );

        const vote = {
            poll_id: res.id,
            member_id: mid,
            name: ( m.cmember.name_first + ' ' + m.cmember.name_last ),
            marked: setup.marked[mid]
        };
        const vres = await voteEventPoll( vote );

	    $screen = 'meeting-polls';
	    router.goto('/meeting-poll/' + res.id);

	}
}

const remove_slot = ( yyyymmdd, sl_start ) => {
	setup.seldays = [ ...setup.seldays.filter( s => !( s.yyyymmdd == yyyymmdd && s.slot_start == sl_start ) ) ];
}

onMount(async () => {
});

</script>

{#if !$auth['grants']['event-poll-create'] }

	<AccessDenied />

{:else}


    {#await get_members_groups()}

    <LinearProgress indeterminate />

    {:then}

<Paper>

{#if step == 1 }

        <Textfield bind:value={setup.title}
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="Event Title"
            input$maxlength=255
            required=true
        >
          <svelte:fragment slot="helper">
            <CharacterCounter>{ typeof setup.title == 'string' ? setup.title.length : 0 } / 255</CharacterCounter>
          </svelte:fragment>
        </Textfield>

        <Textfield bind:value={setup.location}
            style="width: 100%;"
            helperLine$style="width: 100%;"
            label="Location"
            input$maxlength=255
        >
          <svelte:fragment slot="helper">
            <CharacterCounter>{ typeof setup.location == 'string' ? setup.location.length : 0 } / 255</CharacterCounter>
          </svelte:fragment>
        </Textfield>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: top; text-align: center; width: 50%;">
                    <p style="text-align: center; margin: 0;">What date/time slots might work?</p>
					<EventPollCalendarWeek showToday=true bind:seldays={setup.seldays} bind:setup={setup} />
                </td>

                <td style="vertical-align: top; text-align: center; padding-left: 5%; padding-right: 5%; width: 50%;">

                    <p style="text-align: center; margin: 0; margin-top: 3vmin;">Notify Groups/Members?</p>

                    <div class="document-authors-list">
                        <pre style="display: inline-block;">SELECTED GROUPS:</pre>
                        <Set style="display: inline-block;" bind:chips={setup.groups} let:chip>
                            <Chip {chip}>
                            <Text tabindex={0}>{chip.name}</Text>
                            <TrailingAction icon$class="material-icons">cancel</TrailingAction>
                            </Chip>
                        </Set>
                    </div>

                    <Autocomplete
                        bind:this={groups_notified}
                        bind:value={groups_value}
                        options={groups}
                        label="ADD GROUP"
                        getOptionLabel={(option) =>
                        option ? `${option.name}` : ''}
                        style="width: 100%;"
                        textfield$style="width: 100%;"
                        on:SMUIAutocomplete:selected={handle_group_selection}
                        on:focus={(e) => { }}
                        on:blur={(e) => { }}
                    />

                    <div class="document-authors-list">
                        <pre style="display: inline-block;">SELECTED MEMBERS:</pre>
                        <Set style="display: inline-block;" bind:chips={setup.members} let:chip>
                            <Chip {chip}>
                            <Text tabindex={0}>{chip.name_last + ', ' + chip.name_first}</Text>
                            <TrailingAction icon$class="material-icons">cancel</TrailingAction>
                            </Chip>
                        </Set>
                    </div>

                    <Autocomplete
                        bind:this={members_notified}
                        bind:value={members_value}
                        options={members}
                        label="ADD MEMBER"
                        getOptionLabel={(option) =>
                        option ? `${option.name_last}, ${option.name_first}` : ''}
                        style="width: 100%;"
                        textfield$style="width: 100%;"
                        on:SMUIAutocomplete:selected={handle_member_selection}
                        on:focus={(e) => { }}
                        on:blur={(e) => { }}
                    />

                    <p style="text-align: center; margin: 0;">Selected Dates: </p>
                        <Set style="display: inline-block; margin: 0;" bind:chips={setup.seldays} let:chip>
                            <Chip {chip} on:click={() => { setTimeout( () => { remove_slot( chip.yyyymmdd, chip.slot_start); }, 0 ); }}>
                                <Text tabindex={0} style="font-size: 90%;">{chip.dayNameShort}, {chip.monthNameShort} {chip.day}, {chip.hr_start} - {chip.hr_end}</Text>
                            </Chip>
                        </Set>
				</td>
			</tr>
		</table>

		{#if Object.values(setup.seldays).length}
			<div style="position: absolute; bottom: 2vmin; right: 2vmin;">
    	    	<Fab on:click={() => { step = 2; }} color="primary" extended>
        		    <FabIcon class="material-icons">preview</FabIcon>
    	        	<FabLabel>PREVIEW POLL</FabLabel>
		        </Fab>
			</div>
		{/if}

{:else if step == 2 }

	<p style="text-align: center; margin: 0; font-size: 120%; margin-bottom: 2vmin;">{setup.title}</p>

	<table style="width: 100%; border-collapse: collapse;">
		<colgroup>
			<col style="background-color: #FFF;">
			<col span="{setup.seldays.length}" style="background-color: #dfe9eb;">
		</colgroup>
		<tr>
			<td style="border-bottom: 1px solid #000;">
				<Autocomplete
					options={Intl.supportedValuesOf('timeZone')}
					bind:value={setup.timezone}
					style="width: 80%; margin-bottom: 2vmin;"
					textfield$style="width: 80%;"
					label="TIMEZONE"
				/>

				{#if setup.location && setup.location.length}
					<p style="margin-bottom: 2vmin;">Location: {setup.location}</p>
				{/if}

				<table style="margin-bottom: 2vmin;">
					<tr>
						<td class="vote-0"> <IconButton class="material-icons" style="vertical-align: middle;" size="button"> help </IconButton> pending </td>
					</tr>
					<tr>
						<td class="vote-1"> <IconButton class="material-icons" style="vertical-align: middle;" size="button"> task_alt </IconButton> yes </td>
					</tr>
					<tr>
						<td class="vote-2"> <IconButton class="material-icons" style="vertical-align: middle;" size="button"> published_with_changes </IconButton> if need be&nbsp;&nbsp;</td>
					</tr>
					<tr>
						<td class="vote-3"> <IconButton class="material-icons" style="vertical-align: middle;" size="button"> block </IconButton> no </td>
					</tr>
				</table>


			</td>
			{#each setup.seldays as sday, idx}
			<td style="border-left: 1px solid #000; border-bottom: 1px solid #000; width: 12vmin;">
				<div class="flex-center" style="height: 100%; width: 100%;">
					<div style="margin-top: 3vmin;"> {sday.monthNameShort} </div>
					<div style="font-size: 200%;"> {sday.day} </div>
					<div style="margin-top: 2vmin; font-weight: bold;"> {sday.dayNameShort} </div>
					<div style="margin-top: 3vmin;"> {sday.hr_start} </div>
					<div style="margin-bottom: 2vmin;"> {sday.hr_end} </div>
				</div>
			</td>
			{/each}
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #000; height: 10vmin; text-align: left; vertical-align: middle;">
				PARTICIPANTS
			</td>
			{#each setup.seldays as sday, idx}
			<td style="border-left: 1px solid #000; border-bottom: 1px solid #000;">
				<div style="width: 100%; height: 100%; font-size: 150%;" class="flex-center">
				{#if setup.marked[mid][idx] == 1}
					1
				{:else if setup.marked[mid][idx] == 2}
					1 (0)
				{:else}
					0
				{/if}
				</div>
			</td>
			{/each}
		</tr>
		<tr>
			<td style="height: 15vmin;"> YOUR VOTE </td>
			{#each setup.seldays as sday, idx}
			<td style="border-left: 1px solid #000;">
				{#if setup.marked[mid][idx] == 0}
					<div style="width: 100%; height: 15vmin;" class="flex-center vote-0" on:click={() => { setup.marked[mid][idx] = 1; setup.marked[mid] = [ ...setup.marked[mid] ]; }}>
						<IconButton class="material-icons xl-button" style="font-size: 10vmin;" >help</IconButton>
					</div>
				{:else if setup.marked[mid][idx] == 1}
					<div style="width: 100%; height: 15vmin;" class="flex-center vote-1" on:click={() => { setup.marked[mid][idx] = 2; setup.marked[mid] = [ ...setup.marked[mid] ]; }}>
						<IconButton class="material-icons xl-button" style="font-size: 10vmin;">task_alt</IconButton>
					</div>
				{:else if setup.marked[mid][idx] == 2}
					<div style="width: 100%; height: 15vmin;" class="flex-center vote-2" on:click={() => { setup.marked[mid][idx] = 3; setup.marked[mid] = [ ...setup.marked[mid] ]; }}>
						<IconButton class="material-icons xl-button" style="font-size: 10vmin;">published_with_changes</IconButton>
					</div>
				{:else if setup.marked[mid][idx] == 3}
					<div style="width: 100%; height: 15vmin;" class="flex-center vote-3" on:click={() => { setup.marked[mid][idx] = 1; setup.marked[mid] = [ ...setup.marked[mid] ]; }}>
						<IconButton class="material-icons xl-button" style="font-size: 10vmin;">block</IconButton>
					</div>
				{/if}
			</td>
			{/each}
		</tr>
	</table>


    <div style="position: absolute; left: 50%; bottom: 2vmin; transform: translate(-50%,0);">
        <Fab on:click={() => { step = 1; }} color="primary" extended>
            <FabIcon class="material-icons">arrow_back</FabIcon>
            <FabLabel>BACK</FabLabel>
        </Fab>
        &nbsp;&nbsp;
        <Fab on:click={() => { createEPoll(); }} color="primary" extended>
            <FabIcon class="material-icons">save</FabIcon>
            <FabLabel>CREATE POLL</FabLabel>
        </Fab>
    </div>

{/if}

</Paper>

{/await}

{/if}

<style>

.vote-0 {
	background-color: #DDD;
}

.vote-1 {
	background-color: #BFB;
}

.vote-2 {
	background-color: #FB0;
}

.vote-3 {
	background-color: #FBB;
}

</style>