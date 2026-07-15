<script>

import { onMount } from 'svelte';
import { afterUpdate } from 'svelte';
import { router, Route } from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import Button, { Group as ButtonGroup, Icon as ButtonIcon, Label as ButtonLabel } from '@smui/button';
import IconButton from '@smui/icon-button';
import Select, { Option } from '@smui/select';
import LinearProgress from '@smui/linear-progress';
import Paper from '@smui/paper';

import AccessDenied from './AccessDenied.svelte';

import { auth } from '../store.js';
import { screen } from '../store.js';

const mid = window.pnb.mid || 0;

const HEIGHT = 40; // vmin
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

const get_next_month_name = ( midx ) => {
	if ( ( midx -1 + 1 ) >= monthNamesShort.length ) { return monthNamesShort[0]; }
	return monthNamesShort[ midx - 1 + 1];
}

const get_prev_month_name = ( midx ) => {
	if ( ( midx - 1 - 1 ) < 0 ) { return monthNamesShort[ monthNamesShort.length - 1 ]; }
	return monthNamesShort[ midx - 1 - 1 ];
}

export let showToday = true;
export let seldays = [];
export let setup = {};

let slot_day = false,
	slot_start = false,
	slot_move = false,
	slot_end = false;

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

const get_prev_week = ( year, month, day ) => {
	return date_plus_days( year, month, day, -7 );
}

const get_next_week = ( year, month, day ) => {
	return date_plus_days( year, month, day, 7 );
}

const get_prev_month = ( year, month, day = 1 ) => {
	if ( ( month - 1 ) <= 0 ) {
		month = 12; year -= 1;
	} else {
		month -= 1;
	}
	return date_plus_days( year, month, day, 0 );
}

const get_next_month = ( year, month, day = 1 ) => {
	if ( ( month + 1 ) > 12 ) {
		month = 1; year += 1;
	} else {
		month += 1;
	}
	return date_plus_days( year, month, day, 0 );
}

const add_slot = ( year, month, day, sl_start, sl_end ) => {
	const slot = date_plus_days( Number(year), Number(month), Number(day) );
	slot.slot_start = sl_start;
	slot.slot_end   = sl_end;
	const hrs = 2 * ( setup.end_time - setup.start_time + 1 );
	slot.hr_start = setup.start_time + Math.floor( sl_start / 2 ) + ':' + ( sl_start % 2 == 0 ? '00' : '30' );
	slot.hr_end   = setup.start_time + Math.floor( ( sl_end + 1 ) / 2 ) + ':' + ( ( sl_end + 1 ) % 2 == 0 ? '00' : '30' );
	if ( !seldays.find( s => s.yyyymmdd === slot.yyyymmdd && s.slot_start == slot.slot_start ) ) {
		const tmp = [ ...seldays, slot ];
		tmp.sort( ( a, b ) => {
			if ( a.yyyymmdd < b.yyyymmdd ) { return -1; }
			else if ( a.yyyymmdd > b.yyyymmdd ) { return 1; }
			if ( a.slot_start < b.slot_start ) { return -1; }
			else if ( a.slot_start > b.slot_start ) { return 1; }
			return 0;
		});
		seldays = tmp;
		setup.marked[mid] = new Array( seldays.length ).fill(0);
	}
	return slot;
}

const remove_slot = ( yyyymmdd, sl_start ) => {
	seldays = [ ...seldays.filter( s => !( s.yyyymmdd == yyyymmdd && s.slot_start == sl_start ) ) ];
	setup.marked[mid] = new Array( seldays.length ).fill(0);
}

const get_full_week = ( year = today.year, month = today.month, day = today.day ) => {
	let ts = date_plus_days( year, month, day, 0 );
	const res = [];
	for ( let i = 0; i < 7; i++ ) {
		res.push( ts );
		ts = get_next_date( ts.year, ts.month, ts.day );
	}
	return res;
}

const onPointerDown = ( e ) => {
    e.preventDefault();
    e.stopPropagation();

    const tgt = ( e.target || e.srcElement );
	if ( !tgt || !tgt.dataset.day ) { return false; }
    const day = tgt.dataset.day;
    slot_day = day;

    const hrs = 2 * ( setup.end_time - setup.start_time + 1 );

	slot_start = Math.floor( hrs * e.offsetY / tgt.clientHeight );
	slot_end   = slot_start;
	slot_move  = slot_start;

    return false;
}

const onPointerUp = ( e ) => {
    e.preventDefault();
    e.stopPropagation();

    const tgt = ( e.target || e.srcElement );
	if ( !tgt || !tgt.dataset.day ) { return false; }
    const day = tgt.dataset.day;
	if ( slot_day !== day ) { return false; }

    // const hrs = 2 * ( setup.end_time - setup.start_time + 1 );
	// slot_end = Math.floor( hrs * e.offsetY / tgt.clientHeight );

	let [ yyyy, mm, dd ] = day.split('-');
	add_slot( yyyy, mm, dd, slot_start, slot_end );

	slot_day   = false;
	slot_start = false;
	slot_move  = false;
	slot_end   = false;

    return false;
}

const onPointerMove = ( e ) => {
    e.preventDefault();
    e.stopPropagation();

    const tgt = ( e.target || e.srcElement );
	if ( !tgt || !tgt.dataset.day ) { return false; }
    const day = tgt.dataset.day;
	if ( slot_day !== day ) { return false; }

    const hrs = 2 * ( setup.end_time - setup.start_time + 1 );
	slot_move = Math.floor( hrs * e.offsetY / tgt.clientHeight );

	if ( slot_move < slot_start ) {
		slot_start = slot_move;
	} else if ( slot_move > slot_end ) {
		slot_end = slot_move;
	}

    return false;
}

const onPointerCancel = ( e ) => {
    e.preventDefault();
    e.stopPropagation();

	slot_day = false;
	slot_start = false;
	slot_move = false;
	slot_end = false;

    return false;
}


const today = get_todays_date();
let dt = get_todays_date();
let caldays = get_full_week( dt.year, dt.month, dt.day );

</script>

<table style="width: 100%; table-layout: fixed;">
	<tr>
		<td></td>
		{#each caldays.sort( (a,b) => ( a.ts - b.ts ) ) as day, idx}
		<td style="padding-right: 1vmin;">
			<div style="width: 100%; height: 100%; background-color: { ( showToday && today.yyyymmdd == day.yyyymmdd ) ? 'gold': '#FFF'}; color: { ( day.dow == 0 || day.dow == 6 ) ? '#A33' : '#000'};">
				<div style="font-size:  80%; text-align: center; width: 100%;">{day.monthNameShort} {day.day}</div>
				<div style="font-size: 110%; text-align: center; width: 100%;">{day.dayNameShort}</div>
			</div>
		</td>
		{/each}
	</tr>
	<tr>
		<td style="height: {HEIGHT}vmin;">
			{#each { length: ( 2 * ( setup.end_time - setup.start_time + 1 ) ) } as _, idx }
				<div style="height: { HEIGHT / ( 2 * ( setup.end_time - setup.start_time ) ) }vmin;
					width: 100%; box-sizing: border-box; vertical-align: top; text-align: right; {idx == 0 ? ( 'margin-top: -3vmin;' ) :''}">
					{#if idx % 2 == 0}
						<span style="font-size: 80%;">{ setup.start_time + idx / 2 }:00</span>
					{/if}
				</div>
			{/each}
		</td>
	{#each caldays.sort( (a,b) => ( a.ts - b.ts ) ) as day, idx}
		<td style="text-align: center; height: {HEIGHT}vmin; padding-right: 1vmin; box-sizing: border-box; position: relative;"
			on:mousedown={onPointerDown} on:mouseup={onPointerUp} on:mousemove={onPointerMove}
			on:mousecancel={onPointerCancel} on:mouseout={onPointerCancel} on:mouseleave={onPointerCancel}
			data-day={day.yyyymmdd}
		>
		{#each { length: ( 2 * ( setup.end_time - setup.start_time + 1 ) ) } as _, idx }
			<div data-day={day.yyyymmdd} style="height: { HEIGHT / ( 2 * ( setup.end_time - setup.start_time ) ) }vmin;
				pointer-events: none;
				border-top: 1px { idx % 2 == 0 ? 'solid':'dashed'} #000; width: 100%; box-sizing: border-box;
				background-color: { ( slot_day && slot_day == day.yyyymmdd && idx >= slot_start && idx <= slot_end ) ? '#393' : ( ( Math.floor( idx / 2 ) % 2 ) == 0 ? '#dfe9eb' : '#EEE' ) };
			"></div>
		{/each}
		{#each seldays as sday, idx}
		{#if sday.yyyymmdd == day.yyyymmdd}
				<div style="position: absolute; z-index: 100;
					top: { sday.slot_start * HEIGHT / ( 2 * ( setup.end_time - setup.start_time ) ) }vmin;
					height: { ( sday.slot_end - sday.slot_start + 1 ) * HEIGHT / ( 2 * ( setup.end_time - setup.start_time ) ) }vmin;
					width: 100%;
					font-size: 1.5vmin;
					box-sizing: border-box;
					padding-right: 1vmin;
				">
					<div data-day={sday.yyyymmdd} data-slot={sday.slot_start} on:mousedown={(e) =>{ e.preventDefault(); e.stopPropagation(); return false; }} on:mouseup={(e) => { e.preventDefault(); e.stopPropagation(); remove_slot( sday.yyyymmdd, sday.slot_start ); return false; }}
						style="
						background-color: #F99;
						width: 100%;
						height: 100%;
						font-size: 1.5vmin;
						display:flex;
						flex-direction: column;
						justify-content: center;
						align-items: center;
					">
						<div style="line-height: 1.5vmin;"> {sday.hr_start} </div>
						<div style="line-height: 1.5vmin;"> {sday.hr_end} </div>
					</div>
				</div>
		{/if}
		{/each}
		</td>
	{/each}
	</tr>
	<tr>
		<td></td>
		<td colspan=7>

<ButtonGroup variant="unelevated" style="display: flex; justify-content: stretch;">
  <Button
    on:click={() => { dt = get_prev_week( dt.year, dt.month, dt.day ); caldays = get_full_week( dt.year, dt.month, dt.day ); }}
    variant="unelevated"
    color="secondary"
    style="flex-grow: 1;"
  >
	<ButtonIcon class="material-icons">arrow_back</ButtonIcon>
    <ButtonLabel>PREV</ButtonLabel>
  </Button>
  <Button
    style="width: 70%;"
  >
    <ButtonLabel>{ dt.monthName } { dt.year }</ButtonLabel>
  </Button>
  <Button
    on:click={() => { dt = get_next_week( dt.year, dt.month, dt.day ); caldays = get_full_week( dt.year, dt.month, dt.day ); }}
    variant="unelevated"
    color="secondary"
    style="flex-grow: 1;"
  >
    <ButtonLabel>NEXT</ButtonLabel>
	<ButtonIcon class="material-icons">arrow_forward</ButtonIcon>
  </Button>
</ButtonGroup>

		</td>
	</tr>
	</table>


<style>

</style>