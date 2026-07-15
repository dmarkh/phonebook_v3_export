<script>

	import Fab, { Label, Icon } from '@smui/fab';
	import domtoimage from '../../vendor/dom-to-image/dom-to-image.min.js';
	import { tran } from '../../utils/tran.js';

	import L from 'leaflet';
	import { setContext, getContext, onMount } from "svelte";
	import "../../../node_modules/leaflet/dist/leaflet.css";
	import "../../vendor/leaflet-bigimage/Leaflet.BigImage.min.css";
	import "../../vendor/leaflet-bigimage/Leaflet.BigImage.min.js";

	let saving_map = false;

	L.Icon.Default.imagePath = 'images/';

	let mapContainer;
	let map;

	map = L.map(L.DomUtil.create('div'), {
			center: new L.LatLng(36.7528852,3.0245384),
			zoom: 2, zoomControl: false,
			preferCanvas: true,
			renderer: L.canvas()
		});

	setContext('leafletMapInstance', map);


                var Esri_NatGeoWorldMap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/NatGeo_World_Map/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri &mdash; National Geographic, Esri, DeLorme, NAVTEQ, UNEP-WCMC, USGS, NASA, ESA, METI, NRCAN, GEBCO, NOAA, iPC',
                    maxZoom: 16, zoomControl: false, renderer: L.canvas()
                });

        map.addLayer(Esri_NatGeoWorldMap);

                var Esri_WorldImagery = new L.TileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
					zoomControl: false, renderer: L.canvas()
                });
                var Esri_WorldStreetMap = new L.TileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, DeLorme, NAVTEQ, USGS, Intermap, iPC, NRCAN, Esri Japan, METI, Esri China (Hong Kong), Esri (Thailand), TomTom, 2012',
					zoomControl: false, renderer: L.canvas()
                });
                var OpenTopoMap = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                    maxZoom: 17, zoomControl: false, renderer: L.canvas(),
                    attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors,<a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'
                });

                var osm = new L.TileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {minZoom: 1, maxZoom: 20, zoomControl: false, attribution: 'Map data  <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'});


        map.addControl(new L.Control.Layers( {
                    "Geo World Map": Esri_NatGeoWorldMap,
                    "World Street Map": Esri_WorldStreetMap,
                    "Topographical Map": OpenTopoMap,
                    "Satellite Map": Esri_WorldImagery,
                    'Bi-color Map': osm,
                }, {}));

	L.control.zoom({position: 'bottomright'}).addTo(map);

	onMount(() => {
		mapContainer.appendChild(map.getContainer());
		map.getContainer().style.width = '100%';
		map.getContainer().style.height = '100%';
		map.invalidateSize();
	});

	const save_map = async () => {
		saving_map = true;

		let ctrls = document.getElementsByClassName("leaflet-control");
		for( let i = 0; i < ctrls.length; i++ ) {
			ctrls[i].style.display = 'none';
		}

	    let el = document.getElementById('worldmap-container');
		let blob = await domtoimage.toBlob( el );

		for( let i = 0; i < ctrls.length; i++ ) {
			ctrls[i].style.display = '';
		}

        window.saveAs(blob, ( window.pnb.title ).split(' ').join('-').toLowerCase() + '-'+ new Date().toISOString().slice(0, 10) +'.png');

		saving_map = false;
	}

</script>
<div id="worldmap-container" class="map" bind:this={mapContainer}>
  <slot></slot>
</div>

{#if saving_map}
<div class="worldmap-save-button">
    <Fab color="primary" extended>
      <Icon class="material-icons">save</Icon>
      <Label>SAVING MAP, PLEASE WAIT</Label>
    </Fab>
</div>
{:else}
<div class="worldmap-save-button">
    <Fab color="primary" on:click={() => { save_map(); }} extended>
      <Icon class="material-icons">save</Icon>
      <Label>{tran('_download_image_')}</Label>
    </Fab>
</div>
{/if}

<style>
    .map {
        height: 100%;
        width:  100%;
    }
	.worldmap-save-button {
	    position: absolute;
    	bottom: 5vmin;
	    left: 5vmin;
		z-index: 1000;
	}
</style>
