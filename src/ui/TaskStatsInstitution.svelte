<script>

import { writable } from 'svelte/store';
import { onDestroy } from 'svelte';

import { tran } from '../utils/tran.js';

import { afterUpdate } from 'svelte';
import {router, Route} from 'tinro';

import DataTable, { Head, Body, Row, Cell, Label, SortValue, Pagination } from '@smui/data-table';
import IconButton from '@smui/icon-button';
import AccessDenied from './AccessDenied.svelte';
import PleaseWait from './PleaseWait.svelte';

import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';
import Button from '@smui/button';
import Paper, { Content } from '@smui/paper';
import LinearProgress from '@smui/linear-progress';
import Select, { Option } from '@smui/select';

import Textfield from '@smui/textfield';
import Icon from '@smui/textfield/icon';
import HelperText from '@smui/textfield/helper-text';

import { getInstitutions } from '../utils/pnb-api.js';
import { convertInstitutions } from '../utils/pnb-convert.js';
import { sortConvertedInstitutions } from '../utils/pnb-sort.js';

import { getMembers } from '../utils/pnb-api.js';
import { convertMembers } from '../utils/pnb-convert.js';

import { getTasks, taskAssigned } from '../utils/pnb-api.js';
import { convertTasks } from '../utils/pnb-convert.js';

import { getGroups } from '../utils/pnb-api.js';

import { screen, auth } from '../store.js';

const title = 'TASK STATS';
const subtitle = 'BY INSTITUTION';

let sort = 'name';
let sortDirection = 'ascending';

let items = [];
let filteredItems = [];
let quickSearch = writable('');

import FileSaver from '../vendor/FileSaver.js';
import { s2ab } from '../utils/s2ab.js';
import * as XLSX from 'xlsx';

let instmap = false;
let insts = false, instsbyid = false;
let members = false, membersbyid = false;
let groups = false, groupsbyid = false;
let tasks = false, tasksbyid = false;
let atasks = false;
let minyeartask = false, maxyeartask = false, years = false;

let selected_years = [ ...window.pnb.stats.tasks['selected-years'] ];
let current_year = window.pnb.stats.tasks['current-year'];

const roundToOneDecimal = (num) => {
	return Math.round(num * 10) / 10;
}

const truncateString = (str, maxLength) => {
	if (str.length > maxLength) {
		return str.slice(0, maxLength - 3) + '...';
	}
	return str;
}

const get_institutions = async () => {
    insts = await getInstitutions();
    insts = await convertInstitutions( insts );
    sortConvertedInstitutions( insts );
	instsbyid = {};
	insts.forEach( i => { instsbyid[ String(i.id) ] = i; });
}

const get_members = async () => {
    members = await getMembers();
    members = await convertMembers( members );
	membersbyid = {};
	members.forEach( m => { membersbyid[ String(m.id) ] = m; });
}

const get_groups = async () => {
	groups = await getGroups();
	groupsbyid = {};
	groups.forEach( g => { groupsbyid[ String(g.id) ] = g; });
}

const get_tasks = async () => {
	tasks = await getTasks();
	tasks = await convertTasks( tasks );
	tasksbyid = {};
	tasks.forEach( t => { tasksbyid[ String(t.id) ] = t; });
}

const get_atasks = async () => {
	atasks = await taskAssigned();
	atasks = atasks.filter( t => ( tasksbyid[ t.task_id ] && groupsbyid[ t.group_id] && membersbyid[ t.member_id ] ) );

	atasks.forEach( t => {
        const btd = new Date( t.begin_time );
        t.begin_time_ts = btd.getTime() / 1000;
        t.begin_time_year = t.begin_time ? Number(t.begin_time.substring(0, 4)) : false;
        const etd = new Date( t.end_time );
        t.end_time_ts = etd.getTime() / 1000;
        t.end_time_year = t.end_time ? Number(t.end_time.substring(0, 4)) : false;
        t.fte = Number(t.fte);
		t.member_id = Number(t.member_id);
	});

	minyeartask = atasks.reduce( ( min, cur ) => { return ( cur.begin_time_year && cur.begin_time_year < min.begin_time_year ) ? cur : min; } );
	if ( minyeartask ) { minyeartask = minyeartask.begin_time_year; }
	maxyeartask = atasks.reduce( ( max, cur ) => { return ( cur.end_time_year && cur.end_time_year < max.end_time_year ) ? cur : max; } );
	if ( maxyeartask ) { maxyeartask = maxyeartask.end_time_year; } else if ( minyeartask ) { maxyeartask = minyeartask; }
	years = [];
	for ( let i = minyeartask; i <= maxyeartask; i++ ) { years.push( i ); }
}

const filterItemsQuick = () => {
    if ( $quickSearch.length ) {
        filteredItems = items.filter( item => {
            for( const val of Object.keys(item) ) {
                if ( String(item[ val ]).toLowerCase().includes( $quickSearch.toLowerCase() ) ) { return true; }
            }
            return false;
        });
    } else {
        filteredItems = items.slice();
        return filteredItems;
    }
    return filteredItems;
}

const handleSort = () => {
    filteredItems = ( filterItemsQuick() ).sort((a, b) => {
        const [aVal, bVal] = [a[sort], b[sort]][
            sortDirection === 'ascending' ? 'slice' : 'reverse'
        ]();
        if ( typeof aVal === 'string' && typeof bVal === 'string' ) {
            return aVal.localeCompare(bVal);
        }
        return Number(aVal) - Number(bVal);
    });
}

const get_stats_data = async () => {

	await get_institutions();
	await get_members();
	await get_groups();
	await get_tasks();
	await get_atasks();


	// calculate ftes per year
	instmap = {};
	for ( const v of insts ) {
		const id = String(v.id);
		instmap[id] = {};
		instmap[id][current_year] = { "fte-sum": 0, "authors": 0 };
	}

	for ( const v of atasks ) {
		if ( !membersbyid[v.member_id] ) { continue; }
		const iid = membersbyid[v.member_id].institution_id;
		if ( !instsbyid[iid] || !instmap[iid] ) { continue; }
		const inst = instsbyid[iid];
		if ( !groupsbyid[v.group_id] ) { continue; }
		if ( !tasksbyid[v.task_id] ) { continue; }
		if (
			( !v.begin_time_year || v.begin_time_year >= current_year )
			&& ( !v.end_time_year || v.end_time_year <= current_year )
		) {
			instmap[ iid ][ current_year ]['fte-sum'] += Number(v.fte);
		}
	}

	const used_members = {};
	for ( const v of atasks ) {
		if ( !membersbyid[v.member_id] ) { continue; }
		const iid = membersbyid[v.member_id].institution_id;
		if ( !instsbyid[iid] || !instmap[iid] ) { continue; }
		if ( used_members[ v.member_id ] ) { continue; }
		const mem = membersbyid[v.member_id];
		if ( mem.is_author != 'Yes' ) { continue; }
		used_members[ v.member_id ] = true;
		instmap[ iid ][ current_year ]['authors'] += 1;
	}

	let data = [];
	for ( const i of insts ) {
		let item = { id: i.id, name: i.name_full,
			fte: roundToOneDecimal( instmap[ i.id ][ current_year ]['fte-sum'] ),
			authors: instmap[ i.id ][ current_year ]['authors']
		};
		data.push( item );
	}

    items = data;
    filteredItems = data;

	return data;
}

const unsubscribe_quickSearch = quickSearch.subscribe( v => {
    filteredItems = filterItemsQuick();
});

onDestroy(() => {
    unsubscribe_quickSearch();
});

const exportToExcel = ( data ) => {
	data = [ ["INSTITUTION", "FTE", "N-AUTHORS", "FTE/N-AUTHORS"], ...data ];
    var ws = XLSX.utils.aoa_to_sheet( data );
    var wb = XLSX.utils.book_new();
    wb.SheetNames.push('task-stats');
    wb.Sheets['task-stats'] = ws;
    var wbout = XLSX.write(wb, {bookType:'xlsx', bookSST:true, type: 'binary'});
    saveAs( new Blob([s2ab(wbout)],{type:"application/octet-stream"}), 'task-stats-' + ( Date.now() / 1000 | 0 )+".xlsx" );
}

const prepareForExcel = () => {
    let data = [];
    for( const v of filteredItems ) {
        let row = [ v.name, v.fte, v.authors, ( v.authors > 0 ? roundToOneDecimal( v.fte / v.authors ) : 0 ) ];
        data.push( row );
    }
    return exportToExcel( data );
}

</script>

<div style="text-align: center;" class="mdc-typography--headline4">{title}</div>
<div style="text-align: center;" class="mdc-typography--subtitle1">{subtitle}</div>

{#await get_stats_data()}
    <LinearProgress indeterminate />

{:then}

<Paper>

<table style="border: 0;">
	<tr>
	<td>
	    <Textfield variant="outlined" bind:value={$quickSearch} label="Quick Search">
    	    <Icon class="material-icons" slot="trailingIcon">search</Icon>
        	<HelperText slot="helper">search by matching substring</HelperText>
	    </Textfield>
	</td>
<!--
	<td>
		<Select variant="outlined" bind:value={current_year} noLabel>
    	  {#each selected_years as year}
        	<Option value={year}>{year}</Option>
	      {/each}
    	</Select>
       	<HelperText>select desired year</HelperText>
	</td>
//-->
</table>

<DataTable table$aria-label="Task Stats By Institution" style="width: 100%;"
  sortable
  bind:sort
  bind:sortDirection
  on:SMUIDataTable:sorted={handleSort}
>
    <Head>
        <Row>
            <Cell columnId="name" style="text-align: center;">
                <Label>INSTITUTION</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="{current_year}" style="text-align: center; width: 10%;">
                <Label>FTE: {current_year}</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="{current_year}" style="text-align: center; width: 10%;">
                <Label>N-AUTHORS: {current_year}</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
            <Cell columnId="{current_year}" style="text-align: center; width: 10%;">
                <Label>FTE / N-AUTHORS : {current_year}</Label>
				<IconButton class="material-icons">arrow_upward</IconButton>
            </Cell>
        </Row>
    </Head>
    <Body>
    {#each filteredItems as item}
      <Row data-entry-id="{item.id}">
        <Cell style="text-align: left;">{truncateString( item.name, 70 )}</Cell>
        <Cell style="text-align: center; width: 10%;">{item.fte}</Cell>
        <Cell style="text-align: center; width: 10%;">{item.authors}</Cell>
        <Cell style="text-align: center; width: 10%;">{ item.authors > 0 ? roundToOneDecimal(item.fte / item.authors) : 0 }</Cell>
      </Row>
    {/each}
    </Body>
</DataTable>
</Paper>

<div class="save-button">
    <Fab color="primary" on:click={() => { prepareForExcel(); }} extended>
        <FabIcon class="material-icons">save</FabIcon>
        <FabLabel>EXPORT TO EXCEL</FabLabel>
    </Fab>
</div>


{/await}

<style>
.save-button {
    position: absolute;
    bottom: 2vmin;
    left: 2vmin;
}
</style>