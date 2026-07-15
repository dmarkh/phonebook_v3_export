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
export let seldays = {};

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

const get_42_days = ( year = today.year, month = today.month, day = 1 ) => {
	let ts = date_plus_days( year, month, day, 0 );
	if ( ts.day !== 1 ) {
		ts = date_plus_days( ts.year, ts.month, 1, 0 ); // scroll to the first day of this month
	}

	ts = date_plus_days( ts.year, ts.month, ts.day, -( ts.dow == 0 ? 7 : ts.dow ) ); // offset to first day of the week

	if ( seldays[ ts.yyyymmdd ] ) { ts.color = '#9F9'; ts.border = 'inset 0 0 0 0.5vmin #9999'; }
	else if ( showToday && ts.year == today.year && ts.month == today.month && ts.day == today.day ) { ts.color = 'gold'; }
	else if ( ts.month !== month ) { ts.color = '#EEE'; }
	else if ( ts.dow == 0 || ts.dow == 6 ) { ts.color = '#FDD'; }

	const res = [];
	for ( let i = 0; i < 42; i++ ) {
		res.push( ts );
		ts = get_next_date( ts.year, ts.month, ts.day );

		if ( seldays[ ts.yyyymmdd ] ) { ts.color = '#9F9'; ts.border = 'inset 0 0 0 0.5vmin #9999'; }
		else if ( showToday && ts.year == today.year && ts.month == today.month && ts.day == today.day ) { ts.color = 'gold'; }
		else if ( ts.month !== month ) { ts.color = '#EEE'; }
		else if ( ts.dow == 0 || ts.dow == 6 ) { ts.color = '#FDD'; }
	}
	return res;
}

const today = get_todays_date();
let dt = get_todays_date();
let caldays = get_42_days( dt.year, dt.month, dt.day );

</script>

<DataTable table$aria-label="Event Data" style="width: 100%;">
    <Head>
        <Row>
		{#each weekNamesShort as name, idx}
            <Cell columnId="{name}" style="text-align: center;">
                <Label style="color: { ( idx == 0 || idx == 6 ) ? '#900' : '#000'}">{name}</Label>
            </Cell>
		{/each}
        </Row>
    </Head>
    <Body>
	{#each { length: 6 } as _, i}
		<Row>
		{#each { length: 7} as _, j}
	        <Cell columnId={weekNamesShort[ j ]} on:click={ () => {
					if ( caldays[ i * 7 + j ].ts < today.ts ) {
						return;
					}
					if ( !seldays[ caldays[ i * 7 + j ].yyyymmdd ] ) {
						seldays = { ...seldays, [ caldays[ i * 7 + j ].yyyymmdd ]: caldays[ i * 7 + j ] };
					} else {
						delete seldays[ caldays[ i * 7 + j ].yyyymmdd ];
						seldays = { ...seldays };
					}
					caldays = get_42_days( dt.year, dt.month, dt.day );
				}} style="text-align: center; background-color: { caldays[ i * 7 + j ].color }; { caldays[ i * 7 + j ].border ? ( 'box-shadow: ' + caldays[ i * 7 + j ].border + ';' ) : '' } cursor: pointer;">
    	    	<Label style="pointer-events: none;">{caldays[ i * 7 + j ].day}</Label>
        	</Cell>
		{/each}
		</Row>
	{/each}
	</Body>
</DataTable>

<ButtonGroup variant="unelevated" style="display: flex; justify-content: stretch;">
  <Button
    on:click={() => { dt = get_prev_month( dt.year, dt.month, dt.day ); caldays = get_42_days( dt.year, dt.month, dt.day ); }}
    variant="unelevated"
    color="secondary"
    style="flex-grow: 1;"
  >
	<ButtonIcon class="material-icons">arrow_back</ButtonIcon>
    <ButtonLabel>{ get_prev_month_name( dt.month ) }</ButtonLabel>
  </Button>
  <Button
    style="width: 70%;"
  >
    <ButtonLabel>{ dt.monthName } { dt.year }</ButtonLabel>
  </Button>
  <Button
    on:click={() => { dt = get_next_month( dt.year, dt.month, dt.day ); caldays = get_42_days( dt.year, dt.month, dt.day ); }}
    variant="unelevated"
    color="secondary"
    style="flex-grow: 1;"
  >
    <ButtonLabel>{ get_next_month_name( dt.month ) }</ButtonLabel>
	<ButtonIcon class="material-icons">arrow_forward</ButtonIcon>
  </Button>
</ButtonGroup>

<style>

</style>