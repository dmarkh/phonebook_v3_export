<script>

import LinearProgress from '@smui/linear-progress';
import Paper, { Content } from '@smui/paper';
import Button, { Label } from '@smui/button';

import { getInstitutions, getInstitutionFields } from '../utils/pnb-api.js';
import { convertInstitutions } from '../utils/pnb-convert.js';
import { sortConvertedInstitutions } from '../utils/pnb-sort.js';

import { getMembers, getMemberFields } from '../utils/pnb-api.js';
import { convertMembers, addInstitutionsToConvertedMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';

import { find_field_id, find_field_name } from '../utils/pnb-search.js';
import { strtotime } from '../vendor/strtotime.js';
import { tran } from '../utils/tran.js';

import { Pie } from 'svelte-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, CategoryScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale);

let data = false, regions_data = false;

const downloadData = async () => {
	let res = {};

    res.institutions = await getInstitutions();
    res.ifields = await getInstitutionFields();
    res.convertedInstitutions = await convertInstitutions( res.institutions, res.ifields );
    sortConvertedInstitutions( res.convertedInstitutions );

    res.members = await getMembers();
    res.mfields = await getMemberFields();
    res.convertedMembers = await convertMembers( res.members, res.mfields );
	res.convertedMembers = res.convertedMembers.filter( m => res.institutions[ m.institution_id ] );
	res.convertedMembers = await addInstitutionsToConvertedMembers( res.convertedMembers );
    sortConvertedMembers( res.convertedMembers );

	return res;
}

const getStats = async () => {

	data = await downloadData();

	let res = {};
	res.institutions = data.convertedInstitutions.length;
	res.members = data.convertedMembers.length;
	res.authors = ( data.convertedMembers.filter( m => m.is_author == 'Yes' )).length;
	res.experts = ( data.convertedMembers.filter( m => m.is_expert == 'Yes' )).length;
	res.shifters = ( data.convertedMembers.filter( m => m.is_shifter == 'Yes' )).length;
	res.emeritus = ( data.convertedMembers.filter( m => m.is_emeritus == 'Yes' )).length;
	res.countries = [ ...new Set( data.convertedInstitutions.map( i => i.country || 'UNSPECIFIED' ) ) ].length;
	res.regions = [ ...new Set( data.convertedInstitutions.map( i => i.region || 'UNSPECIFIED' ) ) ].length;

	// prepare data for plots: pie chart of regions

	let regions_data_points = data.convertedMembers.reduce( ( acc, cv, idx ) => {
		if ( acc[ ( cv.institution__region || 'UNSPECIFIED' ).toUpperCase() ] ) {
			++acc[ ( cv.institution__region || 'UNSPECIFIED' ).toUpperCase() ];
		} else {
			acc[ ( cv.institution__region || 'UNSPECIFIED' ).toUpperCase() ] = 1; 
		}
		return acc;
	}, {});
	for( const [k,v] of Object.entries( regions_data_points ) ) {
		regions_data_points[k] = ( v * 100. / data.convertedMembers.length ).toFixed(2);
	}
	res.regions_data = {
		labels: Object.entries( regions_data_points ).map( v => v[0] + ', ' + v[1] +'%'),
		datasets: [
			{
				data: Object.values( regions_data_points ),
				backgroundColor: [ "#fd7f6f", "#7eb0d5", "#b2e061", "#bd7ebe", "#ffb55a", "#ffee65", "#beb9db", "#fdcce5", "#8bd3c7", "#fd7f6f", "#7eb0d5", "#b2e061", "#bd7ebe", "#ffb55a", "#ffee65", "#beb9db", "#fdcce5", "#8bd3c7" ],
				hoverBackgroundColor: [ "#fd7f6f", "#7eb0d5", "#b2e061", "#bd7ebe", "#ffb55a", "#ffee65", "#beb9db", "#fdcce5", "#8bd3c7", "#fd7f6f", "#7eb0d5", "#b2e061", "#bd7ebe", "#ffb55a", "#ffee65", "#beb9db", "#fdcce5", "#8bd3c7" ]
			}
		]
	};

	let countries_data_points = data.convertedMembers.reduce( ( acc, cv, idx ) => {
		if ( acc[ ( cv.institution__country || 'UNSPECIFIED' ).toUpperCase() ] ) {
			++acc[ ( cv.institution__country || 'UNSPECIFIED' ).toUpperCase() ];
		} else {
			acc[ ( cv.institution__country || 'UNSPECIFIED' ).toUpperCase() ] = 1;
		}
		return acc;
	}, {});
	for( const [k,v] of Object.entries( countries_data_points ) ) {
		countries_data_points[k] = ( v * 100. / data.convertedMembers.length ).toFixed(2);
	}
	res.countries_data = {
		labels: Object.entries( countries_data_points ).map( c => c[0] + ', ' + c[1] + '%'),
		datasets: [
			{
				data: Object.values( countries_data_points ),
				backgroundColor: [ "#fd7f6f", "#7eb0d5", "#b2e061", "#bd7ebe", "#ffb55a", "#ffee65", "#beb9db", "#fdcce5", "#8bd3c7", "#fd7f6f", "#7eb0d5", "#b2e061", "#bd7ebe", "#ffb55a", "#ffee65", "#beb9db", "#fdcce5", "#8bd3c7" ],
				hoverBackgroundColor: [ "#fd7f6f", "#7eb0d5", "#b2e061", "#bd7ebe", "#ffb55a", "#ffee65", "#beb9db", "#fdcce5", "#8bd3c7", "#fd7f6f", "#7eb0d5", "#b2e061", "#bd7ebe", "#ffb55a", "#ffee65", "#beb9db", "#fdcce5", "#8bd3c7" ]
			}
		]
	};

/*
	let gender_data_points = data.convertedMembers.reduce( ( acc, cv, idx ) => {
		if ( !cv.gender ) { return acc; }
		if ( acc[ ( cv.gender || 'UNSPECIFIED' ).toUpperCase() ] ) {
			++acc[ ( cv.gender || 'UNSPECIFIED' ).toUpperCase() ];
		} else {
			acc[ ( cv.gender || 'UNSPECIFIED' ).toUpperCase() ] = 1;
		}
		return acc;
	}, {});
	for( const [k,v] of Object.entries( gender_data_points ) ) {
		gender_data_points[k] = ( v * 100 / data.convertedMembers.length ).toFixed(2);
	}

	res.gender_data = {
		labels: Object.entries( gender_data_points ).map( l => l[0] + ', ' + l[1] + '%' ),
		datasets: [
			{
				data: Object.values( gender_data_points ),
				backgroundColor: [ "#fd7f6f", "#7eb0d5", "#b2e061", "#bd7ebe", "#ffb55a", "#ffee65", "#beb9db", "#fdcce5", "#8bd3c7", "#fd7f6f", "#7eb0d5", "#b2e061", "#bd7ebe", "#ffb55a", "#ffee65", "#beb9db", "#fdcce5", "#8bd3c7" ],
				hoverBackgroundColor: [ "#fd7f6f", "#7eb0d5", "#b2e061", "#bd7ebe", "#ffb55a", "#ffee65", "#beb9db", "#fdcce5", "#8bd3c7", "#fd7f6f", "#7eb0d5", "#b2e061", "#bd7ebe", "#ffb55a", "#ffee65", "#beb9db", "#fdcce5", "#8bd3c7" ]
			}
		]
	};
*/

	return res;
}


</script>

{#await getStats()}
    <LinearProgress indeterminate />
{:then data}

<Paper square elevation={1}>
	<table style="width: 98%;">
		<tr>
			<td style="width: 25%;">
				<Button variant="unelevated" style="width: 95%;">
					<Label> {tran('_members_')}: <b>{data.members || 0}</b></Label>
				</Button>
			</td>
			<td style="width: 25%;">
				<Button variant="unelevated" style="width: 95%;">
					<Label> {tran('_institutions_')}: <b>{data.institutions || 0}</b> </Label>
				</Button>
			</td>
			<td style="width: 25%;">
				<Button variant="unelevated" style="width: 95%;">
					<Label> {tran('COUNTRIES')}: <b>{data.countries || 0}</b> </Label>
				</Button>
			</td>
			<td style="width: 25%;">
				<Button variant="unelevated" style="width: 95%;">
					<Label> {tran('REGIONS')}: <b>{data.regions || 0}</b></Label>
				</Button>
			</td>
		</tr>
<!--
		<tr>
			<td style="width: 25%;">
				<Button color="secondary" variant="unelevated" style="width: 95%;">
					<Label>{tran('AUTHORS')}: {data.authors || 0} </Label>
				</Button>
			</td>
			<td style="width: 25%;">
				<Button color="secondary" variant="unelevated" style="width: 95%;">
					<Label>{tran('EXPERTS')}: {data.experts || 0} </Label>
				</Button>
			</td>
			<td style="width: 25%;">
				<Button color="secondary" variant="unelevated" style="width: 95%;">
					<Label>{tran('EMERITUS')}: {data.emeritus || 0} </Label>
				</Button>
			</td>
			<td style="width: 25%;">
				<Button color="secondary" variant="unelevated" style="width: 95%;">
					<Label>{tran('SHIFTERS')}: {data.shifters || 0} </Label>
				</Button>
			</td>
		</tr>
//-->
	</table>
</Paper>
<br />
<Paper square elevation={1}>
	<div style="width: 100%;">
		<div style="width: 49%; display: inline-block;"> <Pie data={data.regions_data} options={{ responsive: true, maintainAspectRatio: true }} /> </div>
<!--		<div style="width: 33%; display: inline-block;"> <Pie data={data.gender_data} options={{ responsive: true, maintainAspectRatio: true }} /> </div> //-->
		<div style="width: 49%; display: inline-block;"> <Pie data={data.countries_data} options={{ responsive: true, maintainAspectRatio: true }} /> </div>
	</div>
</Paper>

{/await}


<style>

</style>