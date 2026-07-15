<script>
	import L from 'leaflet';
	import { getContext } from "svelte";

	export let lat = 0;
	export let lng = 0;
	export let desc = '';

	let map = getContext('leafletMapInstance');
	let marker = false;

	if ( lat && lng ) {
		switch ( window.pnb.worldmap.marker.type ) {
			case 'dot':
				marker = L.circleMarker([lat, lng], {
	    			radius: window.pnb.worldmap.marker.radius,   // dot size, px
			    	fillColor: window.pnb.worldmap.marker.color, // dot color
			    	color: window.pnb.worldmap.marker.bcolor,    // border color
			    	weight: window.pnb.worldmap.marker.bsize,    // border width
				    opacity: 1,
		    		fillOpacity: 0.8
				});
				break;
			default:
				marker = L.marker([lat, lng]);
				break;
		}

		marker.bindPopup( desc, {maxWidth: 500} ).openPopup();
		marker.addTo(map);
	}

</script>
