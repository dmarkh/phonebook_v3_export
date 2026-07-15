<script>

import LinearProgress from '@smui/linear-progress';

import LeafletMap from './leaflet/LeafletMap.svelte';
import LeafletMarker from './leaflet/LeafletMarker.svelte';

import { getInstitutions } from '../utils/pnb-api.js';
import { convertInstitutions } from '../utils/pnb-convert.js';
import { tran } from '../utils/tran.js';

let institutions = false, convertedInstitutions = false;

const downloadInstitutions = async () => {
    institutions = await getInstitutions();
    convertedInstitutions = await convertInstitutions( institutions );
    return convertedInstitutions;
}

const getMarkerDescription = ( id ) => {

	let inst = institutions[id];
	if ( inst['status'] != 'active' ) { return ''; }

	let lat_id = 43, lon_id = 44;
	if ( !inst['fields'][ lat_id ] || !inst['fields'][ lon_id ] ) { return ''; }

	let desc = '<div style="color: #1D78C8; font-size: 16px;"><p style="margin: 0; padding: 0;">'+inst['fields'][1]+'</p>'
			+ '<hr style="margin: 0; padding: 0; color:#999;"></hr>';
	if ( inst['fields'][10] != undefined && inst['fields'][10] != '') { desc += inst['fields'][10] + '<br />'; }
	if ( inst['fields'][11] != undefined && inst['fields'][11] != '') { desc += inst['fields'][11] + '<br />'; }
	if ( inst['fields'][12] != undefined && inst['fields'][13] != undefined ) { desc += inst['fields'][12] +', ' + inst['fields'][13] + '<br />'; }
	if ( inst['fields'][14] != undefined && inst['fields'][34] != undefined ) { desc += '<img src="images/flags_iso_3166/16/'+inst['fields'][34].toLowerCase()+'.png" border=0 style="vertical-align: text-top;"> '+inst['fields'][14] + '<br>'; }
	desc += '</div>';
	return desc;
}

</script>

{#await downloadInstitutions()}

<LinearProgress indeterminate />

{:then data}

<LeafletMap>
	{#each data as marker (marker.id)}
		{#if marker.geo_lattitude && marker.geo_lattitude.length > 3 && marker.geo_longitude && marker.geo_longitude.length > 3 }
			<LeafletMarker lat={marker.geo_lattitude} lng={marker.geo_longitude} desc={getMarkerDescription( marker.id )}></LeafletMarker>
		{/if}
	{/each}
	<div id="exp-logo" style="background-image: url(images/logo-black.svg); width: {window.pnb.logo.width}vmin; height: {window.pnb.logo.height}vmin;"></div>
</LeafletMap>

{/await}

<style>

#exp-logo {
	position: absolute;
	top : 5vmin;
	left: 5vmin;
	background-position: center;
	background-size: cover;
	z-index: 1000;
}

</style>