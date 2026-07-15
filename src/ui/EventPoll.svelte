<script>

import { onMount } from 'svelte';
import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

import Autocomplete from '@smui-extra/autocomplete';
import Chip, { Set, TrailingAction, Text } from '@smui/chips';
import Select, { Option } from '@smui/select';
import IconButton from '@smui/icon-button';
import Textfield from '@smui/textfield';
import HelperText from '@smui/textfield/helper-text';
import CharacterCounter from '@smui/textfield/character-counter';

import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';

import LinearProgress from '@smui/linear-progress';
import AccessDenied from './AccessDenied.svelte';

import { auth } from '../store.js';
import { screen } from '../store.js';

import { getEventPoll } from '../utils/pnb-api.js';
import { voteEventPoll } from '../utils/pnb-api.js';
import { downloadMember } from '../utils/pnb-download.js';

import time_to_ampm from '../utils/time_to_ampm.js';

export let meta;

let event_poll_id = false;
let poll = false;
let refresh = false;
let participants = [];
let setup = {};
const HEIGHT = 45; // vmin
$screen = 'meeting-poll';
const mid = window.pnb.mid ? parseInt(window.pnb.mid) : null;

let m = false;
let pollm = false;

const weekNames = [ "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" ];
const weekNamesShort = [ "SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT" ];
const monthNames = [
    "January", "February", "March",
    "April", "May", "June",
    "July", "August", "September",
    "October", "November", "December"
];
const monthNamesShort = [
    "JAN", "FEB", "MAR",
    "APR", "MAY", "JUN",
    "JUL", "AUG", "SEP",
    "OCT", "NOV", "DEC"
];

let listOfPeople = false;

const lookup = {}; // member id => member name

const get_next_month_name = ( midx ) => {
    if ( ( midx -1 + 1 ) >= monthNamesShort.length ) { return monthNamesShort[0]; }
    return monthNamesShort[ midx - 1 + 1];
}

const get_prev_month_name = ( midx ) => {
    if ( ( midx - 1 - 1 ) < 0 ) { return monthNamesShort[ monthNamesShort.length - 1 ]; }
    return monthNamesShort[ midx - 1 - 1 ];
}

const get_new_date = ( year, month, day ) => {
    return new Date( year, month - 1, day, 0, 0, 0, 0 );
}

const get_todays_date = () => {
    const dt = new Date();
    dt.setHours(0,0,0,0);
    return {
        dow   : dt.getDay(), // day of week
        dayNameShort: weekNamesShort[ dt.getDay() ],
        dayName: weekNames[ dt.getDay() ],
        day   : dt.getDate(),
        dd    : String( dt.getDate() ).padStart(2, '0'),
        month : ( dt.getMonth() + 1 ),
        monthName: monthNames[ dt.getMonth() ],
        monthNameShort: monthNamesShort[ dt.getMonth() ],
        mm    : String( dt.getMonth() + 1 ).padStart(2, '0'),
        year  : dt.getFullYear(),
        yyyy  : dt.getFullYear(),
        yyyymmdd: ( dt.getFullYear() + '-' + String( dt.getMonth() + 1 ).padStart(2, '0') + '-' + String( dt.getDate() ).padStart(2, '0') ),
        ts    : dt.getTime()
    };
}

const date_plus_days = ( year, month, day, days = 0 ) => {
    const dt = get_new_date( year, month, day );
    if ( days ) {
        dt.setDate( dt.getDate() + days );
    }
    const today = get_todays_date();
    return {
        dow   : dt.getDay(), // day of week
        dayNameShort: weekNamesShort[ dt.getDay() ],
        dayName: weekNames[ dt.getDay() ],
        day   : dt.getDate(),
        dd    : String( dt.getDate() ).padStart(2, '0'),
        month : ( dt.getMonth() + 1 ),
        monthName: monthNames[ dt.getMonth() ],
        monthNameShort: monthNamesShort[ dt.getMonth() ],
        mm    : String( dt.getMonth() + 1 ).padStart(2, '0'),
        year  : dt.getFullYear(),
        yyyy  : dt.getFullYear(),
        today : ( today.day == day && today.month == month && today.year == year ),
        yyyymmdd: ( dt.getFullYear() + '-' + String( dt.getMonth() + 1 ).padStart(2, '0') + '-' + String( dt.getDate() ).padStart(2, '0') ),
        ts    : dt.getTime()
    };
}

const get_prev_date = ( year, month, day ) => {
    return date_plus_days( year, month, day, -1 );
}

const get_next_date = ( year, month, day ) => {
    return date_plus_days( year, month, day, 1 );
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

const saveData = async () => {
	if ( !m ) {
		m = await downloadMember( mid );
	}
    const vote = {
            poll_id: event_poll_id,
            member_id: mid,
            name: ( m.cmember.name_first + ' ' + m.cmember.name_last ),
            marked: setup.marked[mid]
    };
    const vres = await voteEventPoll( vote );
}

const onPointerUp = ( e ) => {
    e.preventDefault();
    e.stopPropagation();
    pointer = false;

	saveData();

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


if ( meta.params.id ) {
	event_poll_id = meta.params.id;
}

const getDoodleVoteCount = ( idx ) => {
	let cnt1 = 0, cnt2 = 0, extra = false;
	for ( const [memid,memvote] of Object.entries(setup.marked) ) {
		if ( memvote[idx] == 1 ) { cnt1 += 1; cnt2 +=1 }
		else if ( memvote[idx] == 2 ) { cnt1 += 1; extra = true; }
	}
	return { cnt1, cnt2, extra };
}

const getWhen2MeetColor = ( dt, idx ) => {
	let cnt = 0, available = [], notavailable = [];
	for ( const [memid,memvote] of Object.entries(setup.marked) ) {
		if ( !memvote[dt] || !memvote[dt][idx] ) {
			notavailable.push( memid );
			continue;
		}
		cnt += 1;
		available.push( memid );
	}
	const color = cnt > 0 ? ( 'rgb('+ 128 / cnt + ', ' + 255 / cnt + ', ' + 128 / cnt + ')' ) : ( ( Math.floor( idx / 2 ) % 2 ) == 0 ? '#dfe9eb' : '#EEE' );
	return { cnt, available, notavailable, color };
}

const getWhen2MeetPeople = ( dt, idx, tm = '' ) => {
	let available = [];
	let notavailable = [];
	for ( const [memid,memvote] of Object.entries(setup.marked) ) {
		if ( !memvote[dt] || !memvote[dt][idx] ) {
			notavailable.push( lookup[memid] );
			continue;
		}
		available.push( lookup[memid] );
	}
	return { available, notavailable, tm }
}

const getPoll = async () => {
	if ( event_poll_id ) {
		poll = await getEventPoll( event_poll_id );
		if ( poll ) {
			setup = {
				"title"		: poll.title,
				"location"	: poll.location,
				"start_time": poll.hr_start|0,
				"end_time"	: poll.hr_end|0,
				"timezone"	: poll.timezone,
				"groups"        : [],
				"members"       : [],
				"seldays"       : poll.days,
				"marked"        : {}
			};

			if ( !m ) {
				m = await downloadMember( mid );
				lookup[mid] = m.cmember.name_first + ' ' + m.cmember.name_last;
				participants.push( lookup[mid] );
			}

			if ( !pollm ) {
				pollm = await downloadMember( poll.mid );
				lookup[poll.mid] = pollm.cmember.name_first + ' ' + pollm.cmember.name_last;
				participants.push( lookup[poll.mid] );
			}

			if ( poll.votes ) {
				for ( const vote of poll.votes ) {
					participants.push( vote.name );
					setup.marked[vote.member_id] = JSON.parse( vote.marked );
					lookup[vote.member_id] = vote.name;
				}
			}

			if ( participants && participants.length ) {
				participants = participants.filter((item, index) => participants.indexOf(item) === index);
			}

			if ( !setup.marked[mid] ) {
				if ( poll.type == 'doodle' ) {
					setup.marked[mid] = new Array( setup.seldays.length ).fill(0);
				} else if ( poll.type == 'when2meet' ) {
					setup.marked[mid] = {}
				}
			}

		}
	}
}

onMount(async () => {

});

</script>

{#if !$auth['grants']['event-poll-view'] }

    <AccessDenied />

{:else}

    {#await getPoll()}
	    <div style="text-align: center;" class="mdc-typography--headline4">MEETING POLL</div>

        <LinearProgress indeterminate />

    {:then}
	    <div style="text-align: center;" class="mdc-typography--headline4">{poll.title}</div>
		{#if poll}
			<div style="text-align: center;" class="mdc-typography--subtitle1">Created by: {lookup[poll.mid]}</div>
		{/if}

		<Paper>

	{#if poll.type == 'doodle'}

	{#key refresh}

    <table style="width: 100%; border-collapse: collapse;">
        <colgroup>
            <col style="background-color: #FFF;">
            <col span="{setup.seldays.length}" style="background-color: #dfe9eb;">
        </colgroup>
        <tr>
            <td style="border-bottom: 1px solid #000;">

                {#if setup.location && setup.location.length}
                    <p style="margin-bottom: 2vmin;">Location: {setup.location}</p>
                {/if}

				{#if setup.timezone}
                    <p style="margin-bottom: 2vmin;">Timezone: {setup.timezone}</p>
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
                PARTICIPANTS: {participants.join(', ')}
            </td>
            {#each setup.seldays as sday, idx}
			{@const res = getDoodleVoteCount(idx)}
            <td style="border-left: 1px solid #000; border-bottom: 1px solid #000;">
                <div style="width: 100%; height: 100%; font-size: 150%;" class="flex-center">
				{#if res.extra}
					{res.cnt1} ( {res.cnt2} )
				{:else}
					{res.cnt1}
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
                    <div style="width: 100%; height: 15vmin;" class="flex-center vote-0" on:click={() => { setup.marked[mid][idx] = 1; setup.marked[mid] = [ ...setup.marked[mid] ]; saveData(); refresh = !refresh; }}>
                        <IconButton class="material-icons xl-button" style="font-size: 10vmin;" >help</IconButton>
                    </div>
                {:else if setup.marked[mid][idx] == 1}
                    <div style="width: 100%; height: 15vmin;" class="flex-center vote-1" on:click={() => { setup.marked[mid][idx] = 2; setup.marked[mid] = [ ...setup.marked[mid] ]; saveData(); refresh = !refresh; }}>
                        <IconButton class="material-icons xl-button" style="font-size: 10vmin;">task_alt</IconButton>
                    </div>
                {:else if setup.marked[mid][idx] == 2}
                    <div style="width: 100%; height: 15vmin;" class="flex-center vote-2" on:click={() => { setup.marked[mid][idx] = 3; setup.marked[mid] = [ ...setup.marked[mid] ]; saveData(); refresh = !refresh; }}>
                        <IconButton class="material-icons xl-button" style="font-size: 10vmin;">published_with_changes</IconButton>
                    </div>
                {:else if setup.marked[mid][idx] == 3}
                    <div style="width: 100%; height: 15vmin;" class="flex-center vote-3" on:click={() => { setup.marked[mid][idx] = 1; setup.marked[mid] = [ ...setup.marked[mid] ]; saveData(); refresh = !refresh; }}>
                        <IconButton class="material-icons xl-button" style="font-size: 10vmin;">block</IconButton>
                    </div>
                {/if}
            </td>
            {/each}
        </tr>
    </table>

	{/key}

	{:else if poll.type == 'when2meet'}

	{#key refresh}

    	{#if setup.location && setup.location.length}
        	<p style="text-align: center; margin: 0; font-size: 90%;">Location: {setup.location}</p>
	    {/if}

		{#if setup.timezone}
	        <p style="margin: 0; text-align: center;">Timezone: {setup.timezone}</p>
		{/if}

	    <table style="border-collapse: collapse; width: 100%;">
        <tr>
            <td style="vertical-align: top; text-align: center; width: 50%; padding-left: 5vmin; padding-right: 5vmin;">
                {#key refresh}

				{#if listOfPeople}

                <table style="width: 100%; table-layout: fixed;">
					<tr><th colspan=2>{ listOfPeople.tm }</th></tr>
                    <tr>
                        <td><b>AVAILABLE</b></td>
						<td><b>NOT AVAILABLE</b></td>
					<tr>
                    <tr>
                        <td style="vertical-align: top;">
						{#each listOfPeople.available as name}
							{name} <br/>
						{/each}
						</td>
						<td style="vertical-align: top;">
						{#each listOfPeople.notavailable as name}
							{name} <br/>
						{/each}
						</td>
					<tr>
				</table>

				{:else}

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

				{/if}

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
							{@const res = getWhen2MeetColor(day.yyyymmdd, idx)}
                            <div on:mouseenter={()=>{ listOfPeople = getWhen2MeetPeople( day.yyyymmdd, idx, day.dayNameShort + ' ' + day.monthNameShort + ' ' + day.day + ', ' + time_to_ampm( Math.floor( setup.start_time + idx / 2 ) + ( (idx % 2 == 0 ) ? ':00' : ':30' ) ) ); }}
								 on:mouseleave={()=>{ listOfPeople = false; }}
								data-day={day.yyyymmdd} style="height: { HEIGHT / ( 2 * ( setup.end_time - setup.start_time ) ) }vmin;
                                border-top: 1px { idx % 2 == 0 ? 'solid':'dashed'} #000; width: 100%; box-sizing: border-box;
                                background-color: { res.color };
                            "></div>
                        {/each}
                        </td>
                    {/each}
                    </tr>
                </table>
            </td>
        </tr>
    </table>

	{/key}

	{:else}
		UNRECOGNIZED POLL TYPE
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