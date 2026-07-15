<script>

import { tran } from '../utils/tran.js';

import { onMount } from 'svelte';
import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

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
import EventPollCalendarMonth from './EventPollCalendarMonth.svelte';

import { auth } from '../store.js';
import { screen } from '../store.js';

import { createEventPoll, voteEventPoll } from '../utils/pnb-api.js';
import { downloadMember } from '../utils/pnb-download.js';

import { getMembers, getGroups, eventPollAddMembers, eventPollAddGroups } from '../utils/pnb-api.js';
import { convertMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';

import time_to_ampm from '../utils/time_to_ampm.js';

const mid = window.pnb.mid || 0;

let step = 1;
let refresh = false;

let setup = {
	"title"			: "New Event",
	"location"		: "",
	"start_time"	:  9,
	"end_time"		: 17,
	"timezone"		: ( Intl.DateTimeFormat().resolvedOptions().timeZone || "" ),
	"groups"		: [],
	"members"		: [],
	"seldays"		: {},
	"marked"		: { [mid]: {} }
};

let value_group = '';
let value_member = '';

const HEIGHT = 45; // vmin

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

const convert_24time_12time = ( time24 ) => {
  if ( typeof time24 != 'string' ) { time24 = time24.toString(); }
  var ts = time24;
  var H = +ts.substr(0, 2);
  var h = (H % 12) || 12;
  h = (h < 10)?("0"+h):h;  // leading 0 at the left for 1 digit hours
  var ampm = H < 12 ? " AM" : " PM";
  ts = h + ts.substr(2, 3) + ampm;
  return ts;
};

const createEPoll = async () => {

	console.log('setup', setup);

	const data = {
		mid     : ( window.pnb.mid || 0 ),
		type    : 'when2meet',
		title   : setup.title,
		location: setup.location,
		timezone: setup.timezone,
		hr_start: setup.start_time,
		hr_end  : setup.end_time,
		days    : JSON.parse(JSON.stringify(setup.seldays))
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

const mark_hour = ( day, pct, onoff = 1 ) => {
	if ( !setup.seldays[ day ] ) { return; }
	const hrs = 2 * ( setup.end_time - setup.start_time + 1 );
	if ( !setup.marked[mid][ day ] ) {
		setup.marked[mid][day] = [...Array( hrs )].map(x => 0);
	}
	const idx = Math.floor( hrs * pct );
	setup.marked[mid][day][idx] = onoff;
	refresh = !refresh;
}

let pointer = false;
let pointer_onoff = 1;

const onPointerDown = ( e ) => {
	e.preventDefault();
	e.stopPropagation();
	const tgt = ( e.target || e.srcElement );
	const day = tgt.dataset.day;
	pointer = day;

	const hrs = 2 * ( setup.end_time - setup.start_time + 1 );
	if ( setup.marked && setup.marked[mid][day] && setup.marked[mid][day][ Math.floor( hrs * e.offsetY / tgt.clientHeight ) ] ) {
		pointer_onoff = 0;
	} else {
		pointer_onoff = 1;
	}

	mark_hour( day, e.offsetY / tgt.clientHeight, pointer_onoff );
	return false;
}

const onPointerUp = ( e ) => {
	e.preventDefault();
	e.stopPropagation();
	pointer = false;
	return false;
}

const onPointerMove = ( e ) => {
	e.preventDefault();
	e.stopPropagation();

	if ( !pointer ) { return false; }
	const tgt = ( e.target || e.srcElement );
	const day = tgt.dataset.day;
	if ( pointer !== day ) { return false; }

	mark_hour( day, e.offsetY / tgt.clientHeight, pointer_onoff );
	return false;
}

const onPointerCancel = ( e ) => {
	e.preventDefault();
	e.stopPropagation();
	pointer = false;
	return false;
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
					<p style="text-align: center; margin: 0;">What dates might work?</p>
					<EventPollCalendarMonth showToday=true bind:seldays={setup.seldays} />
				</td>

				<td style="vertical-align: top; text-align: center; padding-left: 5%; padding-right: 5%; width: 50%;">
					<p style="text-align: center; margin: 0;">What times might work?</p>

							    <Select
									key={(id) => `${id ? id : ''}`}
							        bind:value={setup.start_time}
							        style="width: 100%;"
							        label="NO EARLIER THAN"
							        required=true
							    >
						    	  {#each {length: 24} as _, i }
						        	<Option value={i}>{convert_24time_12time(i)}</Option>
							      {/each}
							    </Select>

							    <Select
									key={(id) => `${id ? id : ''}`}
							        bind:value={setup.end_time}
							        style="width: 100%;"
							        label="NO LATER THAN"
							        required=true
							    >
						    	  {#each {length: 24} as _, i }
						        	<Option value={i}>{convert_24time_12time(i)}</Option>
							      {/each}
							    </Select>

								<Autocomplete
    							    options={Intl.supportedValuesOf('timeZone')}
							        bind:value={setup.timezone}
							        style="width: 100%;"
							        textfield$style="width: 100%;"
							        label="SELECT TIMEZONE"
							    />

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

	<p style="text-align: center; margin: 0; font-size: 120%;">{setup.title}</p>
	{#if setup.location && setup.location.length}
		<p style="text-align: center; margin: 0; font-size: 90%;">{setup.location}</p>
	{/if}

	<table style="border-collapse: collapse; width: 100%;">
		<tr>
			<td style="vertical-align: top; text-align: center; width: 50%; padding-left: 5vmin; padding-right: 5vmin;">
				{#key refresh}
				<table style="width: 100%; table-layout: fixed;">
					<tr>
						<td></td>
						<td colspan=3 style="text-align: center;"> Your Availability </td>
					</tr>
					<tr>
						<td></td>
					{#each Object.values(setup.seldays).sort( (a,b) => ( a.ts - b.ts ) ) as day, idx}
						<td>
							<div style="font-size:  90%; text-align: center; width: 100%;">{day.monthNameShort} {day.day}</div>
							<div style="font-size: 110%; text-align: center; width: 100%;">{day.dayNameShort}</div>
						</td>
					{/each}
					</tr>
					<tr>
						<td style="height: {HEIGHT}vmin;">
						{#each { length: ( 2 * ( setup.end_time - setup.start_time + 1 ) ) } as _, idx }
							<div style="height: { HEIGHT / ( 2 * ( setup.end_time - setup.start_time ) ) }vmin;
								width: 100%; box-sizing: border-box; vertical-align: top; text-align: right; {idx == 0 ? ( 'margin-top: -3vmin;' ) :''}">
								{#if idx % 2 == 0}
									<span style="font-size: 80%; white-space: nowrap;">{ time_to_ampm( ( setup.start_time + idx / 2 ) + ':00' ) }</span>
								{/if}
							</div>
						{/each}
						</td>
					{#each Object.values(setup.seldays).sort( (a,b) => ( a.ts - b.ts ) ) as day, idx}
						<td style="text-align: center; height: {HEIGHT}vmin; padding-right: 1vmin; box-sizing: border-box;"
							on:mousedown={onPointerDown} on:mouseup={onPointerUp} on:mousemove={onPointerMove}
							on:mousecancel={onPointerCancel} on:mouseout={onPointerCancel} on:mouseleave={onPointerCancel}
							data-day={day.yyyymmdd}
						>
						{#each { length: ( 2 * ( setup.end_time - setup.start_time + 1 ) ) } as _, idx }
							<div data-day={day.yyyymmdd} style="height: { HEIGHT / ( 2 * ( setup.end_time - setup.start_time ) ) }vmin;
								pointer-events: none;
								border-top: 1px { idx % 2 == 0 ? 'solid':'dashed'} #000; width: 100%; box-sizing: border-box;
								background-color: { ( setup.marked && setup.marked[mid][ day.yyyymmdd ] && setup.marked[mid][ day.yyyymmdd ][ idx ] ) ? '#393' : ( ( Math.floor( idx / 2 ) % 2 ) == 0 ? '#ffdede' : '#f2d3d3' ) };
							"></div>
						{/each}
						</td>
					{/each}
					</tr>
				</table>
				{/key}
			</td>
			<td style="vertical-align: top; text-align: center; width: 50%; padding-left: 5vmin; padding-right: 5vmin;">
				<table style="width: 100%; table-layout: fixed;">
					<tr>
						<td></td>
						<td colspan=3 style="text-align: center;"> Group's Availability </td>
					</tr>
					<tr>
						<td></td>
					{#each Object.values(setup.seldays).sort( (a,b) => ( a.ts - b.ts ) ) as day, idx}
						<td>
							<div style="font-size:  90%; text-align: center; width: 100%;">{day.monthNameShort} {day.day}</div>
							<div style="font-size: 110%; text-align: center; width: 100%;">{day.dayNameShort}</div>
						</td>
					{/each}
					</tr>
					<tr>
						<td style="height: {HEIGHT}vmin;">
						{#each { length: ( 2 * ( setup.end_time - setup.start_time + 1 ) ) } as _, idx }
							<div style="height: { HEIGHT / ( 2 * ( setup.end_time - setup.start_time ) ) }vmin;
								width: 100%; box-sizing: border-box; vertical-align: top; text-align: right; {idx == 0 ? ( 'margin-top: -3vmin;' ) :''}">
								{#if idx % 2 == 0}
									<span style="font-size: 80%; white-space: nowrap;">{ time_to_ampm( ( setup.start_time + idx / 2 ) + ':00' ) }</span>
								{/if}
							</div>
						{/each}
						</td>
					{#each Object.values(setup.seldays).sort( (a,b) => ( a.ts - b.ts ) ) as day, idx}
						<td style="text-align: center; height: {HEIGHT}vmin; padding-right: 1vmin;">
						{#each { length: ( 2 * ( setup.end_time - setup.start_time + 1 ) ) } as _, idx }
							<div data-day={day.yyyymmdd} style="height: { HEIGHT / ( 2 * ( setup.end_time - setup.start_time ) ) }vmin;
								pointer-events: none;
								border-top: 1px { idx % 2 == 0 ? 'solid':'dashed'} #000; width: 100%; box-sizing: border-box;
								background-color: { ( setup.marked && setup.marked[mid][ day.yyyymmdd ] && setup.marked[mid][ day.yyyymmdd ][ idx ] ) ? '#6F6' : ( ( Math.floor( idx / 2 ) % 2 ) == 0 ? '#dfe9eb' : '#EEE' ) };
							"></div>
						{/each}
						</td>
					{/each}
					</tr>
				</table>

			</td>
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

</style>