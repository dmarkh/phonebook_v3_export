<script>

import { writable } from 'svelte/store';
import { onDestroy } from 'svelte';
import { onMount } from 'svelte';

import { afterUpdate } from 'svelte';
import { router, Route } from 'tinro';

import * as THREE from 'three';

import AccessDenied from './AccessDenied.svelte';
import Tab, { Icon, Label } from '@smui/tab';
import TabBar from '@smui/tab-bar';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import Paper from '@smui/paper';
import Card from '@smui/card';
import LinearProgress from '@smui/linear-progress';
import IconButton from '@smui/icon-button';
import List, { Item, Text, Graphic, Separator, Subheader } from '@smui/list';
import MenuSurface from '@smui/menu-surface';
import Drawer, { AppContent, Content } from '@smui/drawer';
import Dialog, { Title as DialogTitle, Content as DialogContent, Actions as DialogActions, InitialFocus as DialogInitialFocus } from '@smui/dialog';
import Radio from '@smui/radio';
import SegmentedButton, { Segment } from '@smui/segmented-button';
import Textfield from '@smui/textfield';
import Switch from '@smui/switch';
import Select, { Option } from '@smui/select';
import FormField from '@smui/form-field';
import Checkbox from '@smui/checkbox';
import DataTable, { Head, Body, Row, Cell, SortValue, Pagination } from '@smui/data-table';
import Slider from '@smui/slider';

import { screen, auth } from '../store.js';

import Display from '../display/display.js';
import { SVGRenderer } from '../vendor/threejs/SVGRenderer';
import * as FileSaver from '../vendor/FileSaver';

import { fetchDisplayGeometries, fetchDisplayEvents } from '../utils/pnb-api.js';

$screen = 'event-display';

const mid = window.pnb.mid ? parseInt(window.pnb.mid) : null;

let display = false;
let showSettings = true;
let display_background_color = '#000';

let valueTypeGeomFiles = null;
let valueTypeEventFiles = null;

let ibox_x = 50,
	ibox_y = 50,
	ibox_touch = false;

let showProgress = false,
	showProgressStr = '',
	showProgressPct = 0;

let geometry_dialog = false,
	event_dialog = false,
	volume_dialog = false,
	clip_dialog = false,
	camera_dialog = false,
	visibility_dialog = false,
	colors_dialog = false,
	animation_dialog = false,
	screenshot_dialog = false,
	track_cuts_dialog = false,
	hit_cuts_dialog = false;

let geometry_trans_values = [ { id: 1, label: 'ALL SOLID (FAST)' }, { id: 2, label: 'NORMAL (VIEW)' }, { id: 3, label: 'ALL TRANSPARENT (HITS)' } ];
let geometry_trans_value = geometry_trans_values[2];
let valueMergeVolumes = true;

let world_visibility_values = [ { id: true, label: 'Visible' }, { id: false, label: 'Invisible' } ],
	world_visibility_value = world_visibility_values[0],
	axes_visibility_values = [ { id: 1, label: 'BBox' }, { id: 2, label: 'Arrows' }, { id: 3, label: 'Invisible' } ],
	axes_visibility_value = axes_visibility_values[2],
	containers_visibility_values = [ { id: true, label: 'Visible' }, { id: false, label: 'Invisible' } ],
	containers_visibility_value = containers_visibility_values[0],
	logs_visibility_values = [ { id: true, label: 'Visible' }, { id: false, label: 'Invisible' } ],
	logs_visibility_value = logs_visibility_values[1],
	camera_up_values = [ { id: "x", label: "X up"}, { id: "y", label: "Y up"}, { id: "z", label: "Z up"} ],
	camera_up_value = camera_up_values[1],
	camera_fieldofview_values = [ { id: "25", label: "Normal"}, { id: "50", label: "Wide"}, { id: "90", label: "X-Wide"}, { id: "ortho", label: "Ortho"} ],
	camera_fieldofview_value = camera_fieldofview_values[0],
	camera_bgcolor_values = [ { id: "black", label: "Black"}, { id: "gray", label: "Gray"}, { id: "white", label: "White"}, { id: "#002375", label: "Blue"} ],
	camera_bgcolor_value = camera_bgcolor_values[0],
	camera_x_value = 0,
	camera_y_value = 0,
	camera_z_value = 0,
	volume_worldref_value = '',
	volume_worldref_values = [],
	volume_worldrefs = [],
	volume_visdepth_value = 3,
	volume_edges_values = [ { id: false, label: "None" }, { id: "3355443", label: "Black" },
		{ id: "10066329", label: "Dark Gray" }, { id: "13421772", label: "Light Gray" },
		{ id: "16777215", label: "White" }, { id: "48127", label: "Blue" } ],
	volume_edges_value = volume_edges_values[1],
	colors_palette_values = [
		{ "id": "pastel", "size": 20, "label": "Pastel" },
		{ "id": "nature2", "size": 17, "label": "Nature" },
		{ "id": "random", "size": 15, "label": "Random" },
		{ "id": "root", "size": 50, "label": "Root" },
		{ "id": "white", "size": 4, "label": "White" }
	],
	colors_palette_value = colors_palette_values[3],
	colors_palettestart_value = 0,
	colors_trackcolor_values = [
        { id: "black", label: "Black" },
        { id: "white", label: "White" },
        { id: "yellow", label: "Yellow" },
        { id: "blue", label: "Blue" }
    ],
	colors_trackcolor_value = colors_trackcolor_values[3],
	track_cuts_p_start = 0,
	track_cuts_p_end = 1,
	track_cuts_p_min = 0,
	track_cuts_p_max = 1,
	track_cuts_pt_start = 0,
	track_cuts_pt_end = 1,
	track_cuts_pt_min = 0,
	track_cuts_pt_max = 1,
	track_cuts_eta_start = 0,
	track_cuts_eta_end = 1,
	track_cuts_eta_min = 0,
	track_cuts_eta_max = 1,
	track_cuts_phi_start = 0,
	track_cuts_phi_end = 1,
	track_cuts_phi_min = 0,
	track_cuts_phi_max = 1,
	track_cuts_preserve = false,
	hit_cuts_e_start = 0,
	hit_cuts_e_end = 1,
	hit_cuts_e_min = 0,
	hit_cuts_e_max = 1,
	hit_cuts_eta_start = 0,
	hit_cuts_eta_end = 1,
	hit_cuts_eta_min = 0,
	hit_cuts_eta_max = 1,
	hit_cuts_phi_start = 0,
	hit_cuts_phi_end = 1,
	hit_cuts_phi_min = 0,
	hit_cuts_phi_max = 1,
	hit_cuts_preserve = false,
	hit_cuts_subsystems = [],
	hit_cuts_subsystem = false;

let clip_options = [
	{ val: false, name: 'No Clip' , disabled: false },
	{ val: '1/8', name: '1/8 Clip', disabled: false },
	{ val: '1/4', name: '1/4 Clip', disabled: false },
	{ val: '1/3', name: '1/3 Clip', disabled: false },
	{ val: '1/2', name: '1/2 Clip', disabled: false }
];
let clip_selected = false;
let clip_invert = false;

let animation_selected = "0";
let screenshot_value = "1";

let gitems = [];
let gfilteredItems = [];
let gquickSearch = writable('');
let gsort = 'name';
let gsortDirection = 'ascending';
let growsPerPage = 10;
let gcurrentPage = 0;
$: gstart = gcurrentPage * growsPerPage;
$: gend = Math.min(gstart + growsPerPage, gfilteredItems.length);
$: gslice = gfilteredItems.slice(gstart, gend);
$: glastPage = Math.max(Math.ceil( gfilteredItems.length / growsPerPage) - 1, 0);
$: if (gcurrentPage > glastPage) {
    gcurrentPage = glastPage;
}

let eitems = [];
let efilteredItems = [];
let equickSearch = writable('');
let esort = 'name';
let esortDirection = 'ascending';
let erowsPerPage = 10;
let ecurrentPage = 0;
$: estart = ecurrentPage * erowsPerPage;
$: eend = Math.min(estart + erowsPerPage, efilteredItems.length);
$: eslice = efilteredItems.slice(estart, eend);
$: elastPage = Math.max(Math.ceil( efilteredItems.length / erowsPerPage) - 1, 0);
$: if (ecurrentPage > elastPage) {
    ecurrentPage = elastPage;
}

const cbOnStart = ( e ) => {
	showProgress = true;
	showProgressStr = '';
	showProgressPct = 0;
}

const cbOnProgress = ( str, pct ) => {
	showProgressStr = str;
	showProgressPct = pct;
}

const cbOnFinish = ( e ) => {
	showProgress = false;
	showProgressStr = '';
	showProgressPct = 0;
}

const update_track_cuts_values = () => {
    track_cuts_p_start = parseFloat( Number(display.EVENTimporter.cuts.tracks.p.cmin).toFixed(3) );
    track_cuts_p_end = parseFloat( Number(display.EVENTimporter.cuts.tracks.p.cmax).toFixed(3));
    track_cuts_p_min = parseFloat( Number(display.EVENTimporter.cuts.tracks.p.min).toFixed(3));
    track_cuts_p_max = parseFloat( Number(display.EVENTimporter.cuts.tracks.p.max).toFixed(3));

    track_cuts_pt_start = parseFloat( Number(display.EVENTimporter.cuts.tracks.pt.cmin).toFixed(3));
    track_cuts_pt_end = parseFloat( Number(display.EVENTimporter.cuts.tracks.pt.cmax).toFixed(3));
    track_cuts_pt_min = parseFloat( Number(display.EVENTimporter.cuts.tracks.pt.min).toFixed(3));
    track_cuts_pt_max = parseFloat( Number(display.EVENTimporter.cuts.tracks.pt.max).toFixed(3));

    track_cuts_eta_start = parseFloat( Number(display.EVENTimporter.cuts.tracks.eta.cmin).toFixed(3));
    track_cuts_eta_end = parseFloat( Number(display.EVENTimporter.cuts.tracks.eta.cmax).toFixed(3));
    track_cuts_eta_min = parseFloat( Number(display.EVENTimporter.cuts.tracks.eta.min).toFixed(3));
    track_cuts_eta_max = parseFloat( Number(display.EVENTimporter.cuts.tracks.eta.max).toFixed(3));

    track_cuts_phi_start = parseFloat( Number(display.EVENTimporter.cuts.tracks.phi.cmin).toFixed(3));
    track_cuts_phi_end = parseFloat( Number(display.EVENTimporter.cuts.tracks.phi.cmax).toFixed(3));
    track_cuts_phi_min = parseFloat( Number(display.EVENTimporter.cuts.tracks.phi.min).toFixed(3));
    track_cuts_phi_max = parseFloat( Number(display.EVENTimporter.cuts.tracks.phi.max).toFixed(3));

	track_cuts_preserve = !!display.EVENTimporter.cuts.tracks.preserve;
}

const update_hit_cuts_values = () => {

	hit_cuts_preserve = !!display.EVENTimporter.cuts.hits.preserve;
	hit_cuts_subsystem = display.EVENTimporter.scuts.selected_hits || false;

	if ( hit_cuts_subsystem ) {
	    hit_cuts_e_start = parseFloat( Number(display.EVENTimporter.scuts.hits[hit_cuts_subsystem].e.cmin).toFixed(3) );
    	hit_cuts_e_end = parseFloat( Number(display.EVENTimporter.scuts.hits[hit_cuts_subsystem].e.cmax).toFixed(3));
	    hit_cuts_e_min = parseFloat( Number(display.EVENTimporter.scuts.hits[hit_cuts_subsystem].e.min).toFixed(3));
    	hit_cuts_e_max = parseFloat( Number(display.EVENTimporter.scuts.hits[hit_cuts_subsystem].e.max).toFixed(3));
	    hit_cuts_eta_start = parseFloat( Number(display.EVENTimporter.scuts.hits[hit_cuts_subsystem].eta.cmin).toFixed(3) );
    	hit_cuts_eta_end = parseFloat( Number(display.EVENTimporter.scuts.hits[hit_cuts_subsystem].eta.cmax).toFixed(3));
	    hit_cuts_eta_min = parseFloat( Number(display.EVENTimporter.scuts.hits[hit_cuts_subsystem].eta.min).toFixed(3));
	    hit_cuts_eta_max = parseFloat( Number(display.EVENTimporter.scuts.hits[hit_cuts_subsystem].eta.max).toFixed(3));
    	hit_cuts_phi_start = parseFloat( Number(display.EVENTimporter.scuts.hits[hit_cuts_subsystem].phi.cmin).toFixed(3) );
    	hit_cuts_phi_end = parseFloat( Number(display.EVENTimporter.scuts.hits[hit_cuts_subsystem].phi.cmax).toFixed(3));
	    hit_cuts_phi_min = parseFloat( Number(display.EVENTimporter.scuts.hits[hit_cuts_subsystem].phi.min).toFixed(3));
    	hit_cuts_phi_max = parseFloat( Number(display.EVENTimporter.scuts.hits[hit_cuts_subsystem].phi.max).toFixed(3));
	} else {
	    hit_cuts_e_start = parseFloat( Number(display.EVENTimporter.cuts.hits.e.cmin).toFixed(3) );
    	hit_cuts_e_end = parseFloat( Number(display.EVENTimporter.cuts.hits.e.cmax).toFixed(3));
	    hit_cuts_e_min = parseFloat( Number(display.EVENTimporter.cuts.hits.e.min).toFixed(3));
    	hit_cuts_e_max = parseFloat( Number(display.EVENTimporter.cuts.hits.e.max).toFixed(3));
	    hit_cuts_eta_start = parseFloat( Number(display.EVENTimporter.cuts.hits.eta.cmin).toFixed(3) );
    	hit_cuts_eta_end = parseFloat( Number(display.EVENTimporter.cuts.hits.eta.cmax).toFixed(3));
	    hit_cuts_eta_min = parseFloat( Number(display.EVENTimporter.cuts.hits.eta.min).toFixed(3));
    	hit_cuts_eta_max = parseFloat( Number(display.EVENTimporter.cuts.hits.eta.max).toFixed(3));
	    hit_cuts_phi_start = parseFloat( Number(display.EVENTimporter.cuts.hits.phi.cmin).toFixed(3) );
    	hit_cuts_phi_end = parseFloat( Number(display.EVENTimporter.cuts.hits.phi.cmax).toFixed(3));
	    hit_cuts_phi_min = parseFloat( Number(display.EVENTimporter.cuts.hits.phi.min).toFixed(3));
    	hit_cuts_phi_max = parseFloat( Number(display.EVENTimporter.cuts.hits.phi.max).toFixed(3));
	}

	console.log('scuts', display.EVENTimporter.scuts );

	if ( !hit_cuts_subsystems.length && display.EVENTimporter.scuts ) {
		hit_cuts_subsystems = Object.keys( display.EVENTimporter.scuts.hits );
		console.log('hit_cuts_subsystems ' + Date.now(), hit_cuts_subsystems);
	}
}

const update_color_values = () => {
	if ( !display || !display.GDMLimporter ) { return; }
	colors_palette_value = colors_palette_values[ colors_palette_values.findIndex( c => c.id == display.GDMLimporter.settings.palette ) ];
	colors_palettestart_value = display.GDMLimporter.palette_index_start;
	colors_trackcolor_value = colors_trackcolor_values[ colors_trackcolor_values.findIndex( c => c.id == display.EVENTimporter.track_color_theme ) ];
}

const update_worldref_values = () => {
	if ( !display || !display.GDMLimporter ) { return; }

	if ( Array.isArray( display.GDMLimporter.startVolumeRef ) ) {
		const res = [];
		for ( let i = 0; i < display.GDMLimporter.startVolumeRef.length; i++ ) {
			if ( display.GDMLimporter.worldrefsvislevel[ display.GDMLimporter.startVolumeRef[i] ] ) {
				res.push( display.GDMLimporter.startVolumeRef[i] + '!' + display.GDMLimporter.worldrefsvislevel[display.GDMLimporter.startVolumeRef[i] ] );
			} else {
				res.push( display.GDMLimporter.startVolumeRef[i] );
			}
		}
		volume_worldref_value = res.join(' ');
	} else {
		volume_worldref_value = display.GDMLimporter.startVolumeRef + ( display.GDMLimporter.worldrefsvislevel[display.GDMLimporter.startVolumeRef ] ? 
			'!' + display.GDMLimporter.worldrefsvislevel[ display.GDMLimporter.startVolumeRef ] : '' );
		const refs = display.GDMLimporter.WorldRefs;
		const worldrefs = [];
		for ( let i = 0; i < refs.length; i++ ) {
			worldrefs.push({ i, vislevel: display.GDMLimporter.worldrefs[refs[i]], label: refs[i] });
		}
		volume_worldrefs = [ ...worldrefs ];
	}

}

const gfilterItemsQuick = () => {
    if ( $gquickSearch ) {
        gfilteredItems = gitems.filter( item => {
            if ( String( item.desc ).toLowerCase().includes( $gquickSearch.toLowerCase() ) ) { return true; }
            if ( String( item.file ).toLowerCase().includes( $gquickSearch.toLowerCase() ) ) { return true; }
            return false;
        });
    } else {
        gfilteredItems = gitems.slice();
    }
    return gfilteredItems;
}
const ghandleSort = () => {
    gfilteredItems = gfilterItemsQuick().sort((a, b) => {
        const [aVal, bVal] = [a[gsort], b[gsort]][
            gsortDirection === 'ascending' ? 'slice' : 'reverse'
        ]();
        if (typeof aVal === 'string' && typeof bVal === 'string') {
            return aVal.localeCompare(bVal);
        }
        return Number(aVal) - Number(bVal);
    });
}
const ghandleRowClick = ( e ) => {
	processUploadedGeomFile( 'display/geometries/' + e.target.dataset.entryId );
}
const unsubscribe_gquickSearch = gquickSearch.subscribe( v => {
	gfilteredItems = gfilterItemsQuick();
});

const efilterItemsQuick = () => {
    if ( $equickSearch ) {
        efilteredItems = eitems.filter( item => {
            if ( String( item.desc ).toLowerCase().includes( $equickSearch.toLowerCase() ) ) { return true; }
            if ( String( item.file ).toLowerCase().includes( $equickSearch.toLowerCase() ) ) { return true; }
            return false;
        });
    } else {
        efilteredItems = eitems.slice();
    }
    return efilteredItems;
}
const ehandleSort = () => {
    efilteredItems = efilterItemsQuick().sort((a, b) => {
        const [aVal, bVal] = [a[esort], b[esort]][
            gsortDirection === 'ascending' ? 'slice' : 'reverse'
        ]();
        if (typeof aVal === 'string' && typeof bVal === 'string') {
            return aVal.localeCompare(bVal);
        }
        return Number(aVal) - Number(bVal);
    });
}
const ehandleRowClick = ( e ) => {
	processUploadedEventFile( 'display/events/' + e.target.dataset.entryId );
}
const unsubscribe_equickSearch = equickSearch.subscribe( v => {
	efilteredItems = efilterItemsQuick();
});

const processUploadedEventFile = async ( url = false ) => {
	event_dialog = false;

	if ( url ) {
		await display.load_event_as_file( url );
	} else if ( valueTypeEventFiles[0] ) {
		await display.load_event_as_uploadfile( valueTypeEventFiles[0] );
	} else {
		valueTypeEventFiles = null;
		return;
	}

	await display.add_event();
	// display.reset_camera();
	display.render();

	valueTypeEventFiles = null;
}

const processUploadedGeomFile = async ( url = false ) => {

	geometry_dialog = false;

	display.setGDMLParameters({
		MergeVolumes: valueMergeVolumes ? true : false,
		LiquidBoundary: ( geometry_trans_value.id > 2 ? 1E10 : ( geometry_trans_value.id < 2 ? display.GDMLimporter.GasBoundary : display.GDMLimporter.LiquidBoundary ) )
	});

	display.reset_gdml( cbOnStart, cbOnProgress, cbOnFinish );

	// load data
	if ( url ) {
		await display.load_gdml_as_file( url );
	} else if ( valueTypeGeomFiles[0] ) {
		await display.load_gdml_as_uploadfile( valueTypeGeomFiles[0] );
	} else {
		valueTypeGeomFiles = null;
		return;
	}

	await display.add_detector();
	// display.reset_camera();
	display.render();

	valueTypeGeomFiles = null;
}

$: if (valueTypeGeomFiles != null && valueTypeGeomFiles.length) {
    processUploadedGeomFile();
}

$: if (valueTypeEventFiles != null && valueTypeEventFiles.length) {
    processUploadedEventFile();
}

const getCanvasSize = () => {
	const e = document.getElementById('app-main-content');
	const computed = getComputedStyle(e);
	const pad_w = parseInt(computed.paddingLeft) + parseInt(computed.paddingRight);
	const pad_h = parseInt(computed.paddingTop) + parseInt(computed.paddingBottom);
	return { width: ( e.clientWidth  - pad_w ), height: ( e.clientHeight - pad_h ) };
}

const onResize = async () => {
	const e = document.getElementById('app-main-content');
	const sz = getCanvasSize();
	const cnv = document.getElementById('webglcanvas');
	if ( cnv ) {
		cnv.width  = sz.width;
		cnv.height = sz.height;
		if ( display ) {
			await display.init();
			display.render();
		}
	}
}

const onMouseDown = ( e ) => {
	ibox_touch = { x: e.screenX, y: e.screenY };
}

const onMouseMove = ( e ) => {
	if ( !ibox_touch ) { return; }
	const dx = e.screenX - ibox_touch.x;
	const dy = e.screenY - ibox_touch.y;
	ibox_x += dx;
	ibox_y += dy;
	ibox_touch = { x: e.screenX, y: e.screenY };
}

const onMouseUp = ( e ) => {
	ibox_touch = false;
}

const callback_volume = () => {
	const params = {};
	if ( volume_worldref_value && volume_worldref_value.length > 0 ) { params.WorldRef = volume_worldref_value; }
	if ( volume_visdepth_value && !isNaN( volume_visdepth_value ) ) { params.VisLevel = parseInt( volume_visdepth_value ); }
	if ( volume_edges_value ) { params.VisEdges = isNaN(volume_edges_value.id) ? false : parseInt( volume_edges_value.id ); }
	display.updateGDMLParameters( params );
}

const callback_clip = () => {
	if ( !display ) { return; }
	display.updateGDMLParameters({
		VisClip: clip_selected,
		VisClipInvert: !clip_invert
	});
}

const callback_camera = () => {
	if ( !display ) { return; }

	const fov = camera_fieldofview_value.id;
	if ( fov == 'ortho' ) {
		display.set_camera('ortho');
		display.camera.updateProjectionMatrix();
	} else {
		display.set_camera();
		display.camera.fov = parseInt(fov);
		display.camera.updateProjectionMatrix();
	}

	display.camera.position.set( parseInt(camera_x_value), parseInt(camera_y_value), parseInt(camera_z_value) );
	display.camera.lookAt( new THREE.Vector3( 0, 0, 0 ) );

	const up = camera_up_value.id;
	switch( up ) {
		case 'x':
			display.camera.up.set( 1, 0, 0 );
			break;
		case 'y':
			display.camera.up.set( 0, 1, 0 );
			break;
		case 'z':
			display.camera.up.set( 0, 0, 1 );
			break;
		default:
			console.log('WARNING, camera UP not set: ' + up );
			break;
	}

	display_background_color = camera_bgcolor_value.id;
	display.render();
}

const callback_visibility = () => {
	if ( !display ) { return; }

	switch( axes_visibility_value.id ) {
		case 1:
			display.ShowBbox = true;
			display.ShowArrows = false;
			display.VisAxes = true;
			break;
		case 2:
			display.ShowBbox = false;
			display.ShowArrows = true;
			display.VisAxes = true;
			break;
		case 3:
		default:
			display.ShowBbox = false;
			display.ShowArrows = false;
			display.VisAxes = false;
			break;
	}

	display.updateGDMLParameters({
		VisWorld: world_visibility_value,
		VisContainers: containers_visibility_value
	});

	// TODO: logs_visibility_value

}

const callback_colors = () => {

	if ( display.GDMLimporter ) {
		display.GDMLimporter.palette_index_start = parseInt( colors_palettestart_value );
		display.GDMLimporter.palette_index = display.GDMLimporter.palette_index_start;
		display.GDMLimporter.VisPalette = colors_palette_value.id;
	}

	if ( display.EVENTimporter ) {
		display.EVENTimporter.track_color_theme = colors_trackcolor_value.id;
		display.EVENTimporter.parse_tracks( display.EVENTimporter.evt );
	}

	display.render();
}

const callback_animation = () => {
	if ( !display ) { return; }
	display.set_rotation( animation_selected );
}

const callback_screenshot = () => {

	let size = parseInt( screenshot_value );
	let { width: oldsize_x, height: oldsize_y } = display.renderer.getSize();
	let left, right, top, bottom;
	if ( display.camera.type === 'OrthographicCamera' ) {
		left   = display.camera.left;
		right  = display.camera.right;
		top    = display.camera.top;
		bottom = display.camera.bottom;
	}
	let canvas, svgRenderer, XMLS, file, blob;

	switch (size) {
		case 1:
			// do not resize screen
			break;
		case 2:
			if ( display.camera.type === 'PerspectiveCamera' ) {
				display.camera.aspect = 1.0;
			} else {
				display.camera.left   = -512;
				display.camera.right  =  512;
				display.camera.top    =  512;
				display.camera.bottom = -512;
			}
			display.camera.updateProjectionMatrix();
			display.renderer.setSize( 512, 512 );
			display.render();
			break;
		case 3:
			if ( display.camera.type === 'PerspectiveCamera' ) {
				display.camera.aspect = 1.0;
			} else {
				display.camera.left   = -1024;
				display.camera.right  =  1024;
				display.camera.top    =  1024;
				display.camera.bottom = -1024;
			}
			display.camera.updateProjectionMatrix();
			display.renderer.setSize( 1024, 1024 );
			display.render();
			break;
		case 4:
			if ( display.camera.type === 'PerspectiveCamera' ) {
				display.camera.aspect = 1.0;
			} else {
				display.camera.left   = -2048;
				display.camera.right  =  2048;
				display.camera.top    =  2048;
				display.camera.bottom = -2048;
			}
			display.camera.updateProjectionMatrix();
			display.renderer.setSize( 2048, 2048 );
			display.render();
			break;
		case 5:
			if ( display.camera.type === 'PerspectiveCamera' ) {
				display.camera.aspect = 1.0;
			} else {
				display.camera.left   = -4096;
				display.camera.right  =  4096;
				display.camera.top    =  4096;
				display.camera.bottom = -4096;
			}
			display.camera.updateProjectionMatrix();
			display.renderer.setSize( 4096, 4096 );
			display.render();
			break;
		case 6:
			if ( display.camera.type === 'PerspectiveCamera' ) {
				display.camera.aspect = 1.0;
			} else {
				display.camera.left   = -8192;
				display.camera.right  =  8192;
				display.camera.top    =  8192;
				display.camera.bottom = -8192;
			}
			display.camera.updateProjectionMatrix();
			display.renderer.setSize( 8192, 8192 );
			display.render();
			break;
		case 7:
			canvas = document.getElementById( display.parameters.canvas_id );
			svgRenderer = new SVGRenderer();
			if ( display.camera.type === 'PerspectiveCamera' ) {
				display.camera.aspect = 1.0;
			} else {
				display.camera.left   = -canvas.height;
				display.camera.right  =  canvas.height;
				display.camera.top    =  canvas.height;
				display.camera.bottom = -canvas.height;
			}
			display.camera.updateProjectionMatrix();

			svgRenderer.setClearColor( 0xf0f0f0 );
			svgRenderer.setSize( canvas.height, canvas.height );
			svgRenderer.setQuality( 'low' );
			svgRenderer.render( display.scene, display.camera );
			XMLS = new XMLSerializer();
			file = XMLS.serializeToString( svgRenderer.domElement );
			if ( display.camera.type === 'PerspectiveCamera' ) {
				display.camera.aspect = oldsize_x / oldsize_y;
			} else {
				display.camera.left   = left;
				display.camera.right  = right;
				display.camera.top    = top;
				display.camera.bottom = bottom;
			}
			display.camera.updateProjectionMatrix();
			blob = new Blob( [ file ], { type: "image/svg+xml" } );
			FileSaver.saveAs( blob, 'screenshot-'+Date.now()+'.svg' );
			return;
		default:
			break;
	}

	let image = display.renderer.domElement.toDataURL('image/png').replace("image/png", "image/octet-stream");

	const hscr = document.getElementById('hidden-screenshot');
	hscr.href = image;
	hscr.download = 'screenshot-'+Date.now()+'.png';
	hscr.click();

	display.renderer.setSize( oldsize_x, oldsize_y );
	if ( display.camera.type === 'PerspectiveCamera' ) {
		display.camera.aspect = oldsize_x / oldsize_y;
	} else {
		display.camera.left = left;
		display.camera.right = right;
		display.camera.top = top;
		display.camera.bottom = bottom;
	}
	display.camera.updateProjectionMatrix();
	display.render();

}

const callback_track_cuts = async () => {
	display.EVENTimporter.cuts.tracks.p.cmin = track_cuts_p_start;
	display.EVENTimporter.cuts.tracks.p.cmax = track_cuts_p_end;
	display.EVENTimporter.cuts.tracks.p.min = track_cuts_p_min;
	display.EVENTimporter.cuts.tracks.p.max = track_cuts_p_max;

	display.EVENTimporter.cuts.tracks.pt.cmin = track_cuts_pt_start;
	display.EVENTimporter.cuts.tracks.pt.cmax = track_cuts_pt_end;
	display.EVENTimporter.cuts.tracks.pt.min = track_cuts_pt_min;
	display.EVENTimporter.cuts.tracks.pt.max = track_cuts_pt_max;

	display.EVENTimporter.cuts.tracks.eta.cmin = track_cuts_eta_start;
	display.EVENTimporter.cuts.tracks.eta.cmax = track_cuts_eta_end;
	display.EVENTimporter.cuts.tracks.eta.min = track_cuts_eta_min;
	display.EVENTimporter.cuts.tracks.eta.max = track_cuts_eta_max;

	display.EVENTimporter.cuts.tracks.phi.cmin = track_cuts_phi_start;
	display.EVENTimporter.cuts.tracks.phi.cmax = track_cuts_phi_end;
	display.EVENTimporter.cuts.tracks.phi.min = track_cuts_phi_min;
	display.EVENTimporter.cuts.tracks.phi.max = track_cuts_phi_max;

	display.EVENTimporter.cuts.tracks.preserve = track_cuts_preserve;

	display.EVENTimporter.parse_hits( display.EVENTimporter.evt );
	display.EVENTimporter.parse_tracks( display.EVENTimporter.evt );
	display.render();
}

const callback_hit_cuts = async () => {
	if ( hit_cuts_subsystem ) {
		display.EVENTimporter.scuts.hits[hit_cuts_subsystem].e.cmin = hit_cuts_e_start;
		display.EVENTimporter.scuts.hits[hit_cuts_subsystem].e.cmax = hit_cuts_e_end;
		display.EVENTimporter.scuts.hits[hit_cuts_subsystem].e.min = hit_cuts_e_min;
		display.EVENTimporter.scuts.hits[hit_cuts_subsystem].e.max = hit_cuts_e_max;

		display.EVENTimporter.scuts.hits[hit_cuts_subsystem].eta.cmin = hit_cuts_eta_start;
		display.EVENTimporter.scuts.hits[hit_cuts_subsystem].eta.cmax = hit_cuts_eta_end;
		display.EVENTimporter.scuts.hits[hit_cuts_subsystem].eta.min = hit_cuts_eta_min;
		display.EVENTimporter.scuts.hits[hit_cuts_subsystem].eta.max = hit_cuts_eta_max;

		display.EVENTimporter.scuts.hits[hit_cuts_subsystem].phi.cmin = hit_cuts_phi_start;
		display.EVENTimporter.scuts.hits[hit_cuts_subsystem].phi.cmax = hit_cuts_phi_end;
		display.EVENTimporter.scuts.hits[hit_cuts_subsystem].phi.min = hit_cuts_phi_min;
		display.EVENTimporter.scuts.hits[hit_cuts_subsystem].phi.max = hit_cuts_phi_max;

	} else {
		display.EVENTimporter.cuts.hits.e.cmin = hit_cuts_e_start;
		display.EVENTimporter.cuts.hits.e.cmax = hit_cuts_e_end;
		display.EVENTimporter.cuts.hits.e.min = hit_cuts_e_min;
		display.EVENTimporter.cuts.hits.e.max = hit_cuts_e_max;

		display.EVENTimporter.cuts.hits.eta.cmin = hit_cuts_eta_start;
		display.EVENTimporter.cuts.hits.eta.cmax = hit_cuts_eta_end;
		display.EVENTimporter.cuts.hits.eta.min = hit_cuts_eta_min;
		display.EVENTimporter.cuts.hits.eta.max = hit_cuts_eta_max;

		display.EVENTimporter.cuts.hits.phi.cmin = hit_cuts_phi_start;
		display.EVENTimporter.cuts.hits.phi.cmax = hit_cuts_phi_end;
		display.EVENTimporter.cuts.hits.phi.min = hit_cuts_phi_min;
		display.EVENTimporter.cuts.hits.phi.max = hit_cuts_phi_max;
	}

	display.EVENTimporter.cuts.hits.preserve = hit_cuts_preserve;

	display.EVENTimporter.parse_hits( display.EVENTimporter.evt );
	display.EVENTimporter.parse_tracks( display.EVENTimporter.evt );
	display.render();
}

const fetchGeometries = async () => {
	gitems = await fetchDisplayGeometries();
	gfilterItemsQuick();
}

const fetchEvents = async () => {
	eitems = await fetchDisplayEvents();
	efilterItemsQuick();
}

onDestroy(() => {
    unsubscribe_gquickSearch();
    unsubscribe_equickSearch();
});

onMount( async () => {
	onResize();

	const e = document.getElementById('webglcanvas');

	display = new Display({ canvas_id: 'webglcanvas' });

	display.cbOnStart    = cbOnStart;
	display.cbOnProgress = cbOnProgress;
	display.cbOnFinish   = cbOnFinish;

	await display.init();

	display.start();
});

</script>

	<svelte:window on:resize={onResize} />

{#if $auth['grants']['event-display-view'] }

	<canvas id="webglcanvas" style="display: block; background-color: {display_background_color};"></canvas>


	<div id="display-infobox" style="left: {ibox_x}px; top: {ibox_y}px;" on:mousedown={onMouseDown} on:mousemove={onMouseMove} on:mouseup={onMouseUp} on:mouseout={onMouseUp} on:mousecancel={onMouseUp}>
		<div id="display-infobox-logo" style="background-image: url(images/logo-white.svg); width: {window.pnb.logo.width}vmin;height: {window.pnb.logo.height}vmin;"></div>
		<div id="display-infobox-content"></div>
	</div>

	{#if !showSettings}
		<div id="display-settings" style="color: #FFF; right: 5vmin;">
		  <IconButton class="material-icons" on:click={() => { showSettings = true; }} size="normal">settings</IconButton>
		</div>
	{:else}
		<div id="display-settings" style="width: 40vmin;">
		<Drawer style="border-radius: 1vmin;">
		    <Content>
			<List>
				<Item href="javascript:void(0)" on:click={() => { showSettings = false; }}>
					<Graphic class="material-icons" aria-hidden="true">cancel</Graphic>
					<Text style="color: #900;">CLOSE SETTINGS</Text>
				</Item>
				<Item href="javascript:void(0)" on:click={() => { geometry_dialog = true; }}>
					<Graphic class="material-icons" aria-hidden="true">category</Graphic>
					<Text>SELECT GEOMETRY</Text>
				</Item>
				<Item href="javascript:void(0)" on:click={() => { event_dialog = true; }}>
					<Graphic class="material-icons" aria-hidden="true">timeline</Graphic>
					<Text>SELECT EVENT</Text>
				</Item>
				<Item href="javascript:void(0)" on:click={() => {
						volume_dialog = true;
						update_worldref_values();
					}}>
					<Graphic class="material-icons" aria-hidden="true">check_box_outline_blank</Graphic>
					<Text>VOLUME SETUP</Text>
				</Item>
				<Item href="javascript:void(0)" on:click={() => { clip_dialog = true; }}>
					<Graphic class="material-icons" aria-hidden="true">content_cut</Graphic>
					<Text>CLIP</Text>
				</Item>
				<Item href="javascript:void(0)" on:click={() => {
						const c = display.camera.position;
						camera_x_value = Math.floor( display.camera.position.x );
						camera_y_value = Math.floor( display.camera.position.y );
						camera_z_value = Math.floor( display.camera.position.z );
						camera_dialog = true;
					}}>
					<Graphic class="material-icons" aria-hidden="true">camera</Graphic>
					<Text>CAMERA</Text>
				</Item>
				<Item href="javascript:void(0)" on:click={() => { visibility_dialog = true; }}>
					<Graphic class="material-icons" aria-hidden="true">visibility</Graphic>
					<Text>VISIBILITY</Text>
				</Item>
				<Item href="javascript:void(0)" on:click={() => { setTimeout( () => { update_color_values(); colors_dialog = true; }, 0); }}>
					<Graphic class="material-icons" aria-hidden="true">palette</Graphic>
					<Text>COLORS</Text>
				</Item>
				<Item href="javascript:void(0)" on:click={() => { animation_dialog = true; }}>
					<Graphic class="material-icons" aria-hidden="true">3d_rotation</Graphic>
					<Text>ANIMATION</Text>
				</Item>
				<Item href="javascript:void(0)" on:click={() => { screenshot_dialog = true; }}>
					<Graphic class="material-icons" aria-hidden="true">screenshot</Graphic>
					<Text>SCREENSHOT</Text>
				</Item>
				<Item href="javascript:void(0)" on:click={() => { update_track_cuts_values(); track_cuts_dialog = true; }}>
					<Graphic class="material-icons" aria-hidden="true">data_thresholding</Graphic>
					<Text>TRACK CUTS</Text>
				</Item>
				<Item href="javascript:void(0)" on:click={() => { update_hit_cuts_values(); hit_cuts_dialog = true; }}>
					<Graphic class="material-icons" aria-hidden="true">data_thresholding</Graphic>
					<Text>HIT CUTS</Text>
				</Item>
			</List>
			</Content>
		</Drawer>
		</div>
	{/if}

	{#if showProgress}
		<div id="show-progress">
			<Card variant="outlined" padded style="width: 100%;">
				<p style="text-align: center;">{showProgressStr}</p>
				<LinearProgress progress={showProgressPct} />
			</Card>
		</div>
	{/if}

	<Dialog
		open={geometry_dialog}
		aria-labelledby="default-focus-title"
		aria-describedby="default-focus-content"
		scrimClickAction=""
		escapeKeyAction=""
		fullscreen
		style="z-index: 10000;"
	>
		<DialogTitle id="default-focus-title"><p style="text-align: center; margin: 0; padding: 0;">SELECT GEOMETRY</p></DialogTitle>
		<DialogContent id="default-focus-content">
		<div style="width: 100%; height: 60vmin;">

		<p style="text-align: center; margin: 0; padding: 0;">
		<SegmentedButton segments={geometry_trans_values} singleSelect let:segment bind:selected={geometry_trans_value}
			key={(segment) => segment.id}  variant="unelevated" color="primary">
			<Segment {segment}>
				<Label>{segment.label}</Label>
			</Segment>
		</SegmentedButton>
		</p>
		<p style="width: 100%; text-align: center; margin: 0; padding: 0; margin-top: 1vmin; margin-bottom: 2vmin;">
			<FormField>
				<Label>
					Merge volumes
				</Label>
				<Switch bind:checked={valueMergeVolumes} />
			</FormField>
		</p>

		{#await fetchGeometries()}
			<LinearProgress indeterminate />
		{:then}

			<DataTable table$aria-label="Geometry List" style="width: 100%;"
			  sortable
			  bind:gsort
			  bind:gsortDirection
			  on:SMUIDataTable:sorted={ghandleSort}
			  on:SMUIDataTableRow:click={ghandleRowClick}
			>
				<Head>
					<Row>
						<Cell columnId="name">
							<div>
								<Textfield variant="filled" bind:value={$gquickSearch} label="SEARCH">
								    <Icon class="material-icons" slot="trailingIcon">search</Icon>
								</Textfield>
							</div>
						</Cell>
						<Cell columnId="name">FILE</Cell>
					</Row>
				</Head>
				<Body>
				{#each gfilteredItems as item, idx}
					<Row data-entry-id="{item.file}">
						<Cell style="">{item.desc}</Cell><Cell>{item.file}</Cell>
					</Row>
				{/each}
				</Body>
				<Pagination slot="paginate">
					<svelte:fragment slot="rowsPerPage">
					<Label>Rows Per Page</Label>
					<Select variant="outlined" bind:value={growsPerPage} noLabel>
						<Option value={10}>10</Option>
						<Option value={25}>25</Option>
						<Option value={100}>100</Option>
					</Select>
					</svelte:fragment>
					<svelte:fragment slot="total">
						{gstart + 1}-{gend} of {gitems.length}
					</svelte:fragment>
					<IconButton
						class="material-icons"
						action="first-page"
						title="First page"
						on:click={() => (gcurrentPage = 0)}
						disabled={gcurrentPage === 0}>first_page</IconButton
					>
					<IconButton
						class="material-icons"
						action="prev-page"
						title="Prev page"
						on:click={() => gcurrentPage--}
						disabled={gcurrentPage === 0}>chevron_left</IconButton
					>
					<IconButton
						class="material-icons"
						action="next-page"
						title="Next page"
						on:click={() => gcurrentPage++}
						disabled={gcurrentPage === glastPage}>chevron_right</IconButton
					>
					<IconButton
						class="material-icons"
						action="last-page"
						title="Last page"
						on:click={() => (gcurrentPage = glastPage)}
						disabled={gcurrentPage === glastPage}>last_page</IconButton
					>
				</Pagination>
			</DataTable>

		{:catch error}
			<p style="color: red">{error.message}</p>
		{/await}

		<div class="hide-file-ui" style="width: 100%; text-align: center;">
			<Textfield bind:files={valueTypeGeomFiles} label="Upload Geometry File" type="file" />
		</div>

		</div>
		</DialogContent>
		<DialogActions>
			<Button
				defaultAction
				use={[DialogInitialFocus]}
				on:click={() => { geometry_dialog = false; }}
			>
				<ButtonLabel>CLOSE</ButtonLabel>
			</Button>
		</DialogActions>
	</Dialog>

	<Dialog
		open={event_dialog}
		aria-labelledby="default-focus-title"
		aria-describedby="default-focus-content"
		scrimClickAction=""
		escapeKeyAction=""
	>
		<DialogTitle id="default-focus-title">SELECT EVENT</DialogTitle>
		<DialogContent id="default-focus-content">
		<div style="width: 100%; height: 60vmin;">

		{#await fetchEvents()}
			<LinearProgress indeterminate />
		{:then}

			<DataTable table$aria-label="Event List" style="width: 100%;"
			  sortable
			  bind:esort
			  bind:esortDirection
			  on:SMUIDataTable:sorted={ehandleSort}
			  on:SMUIDataTableRow:click={ehandleRowClick}
			>
				<Head>
					<Row>
						<Cell columnId="name">
							<div>
								<Textfield variant="filled" bind:value={$equickSearch} label="SEARCH">
								    <Icon class="material-icons" slot="trailingIcon">search</Icon>
								</Textfield>
							</div>
						</Cell>
						<Cell columnId="name">FILE</Cell>
					</Row>
				</Head>
				<Body>
				{#each efilteredItems as item, idx}
					<Row data-entry-id="{item.file}">
						<Cell style="">{item.desc}</Cell><Cell>{item.file}</Cell>
					</Row>
				{/each}
				</Body>
				<Pagination slot="paginate">
					<svelte:fragment slot="rowsPerPage">
					<Label>Rows Per Page</Label>
					<Select variant="outlined" bind:value={erowsPerPage} noLabel>
						<Option value={10}>10</Option>
						<Option value={25}>25</Option>
						<Option value={100}>100</Option>
					</Select>
					</svelte:fragment>
					<svelte:fragment slot="total">
						{estart + 1}-{eend} of {eitems.length}
					</svelte:fragment>
					<IconButton
						class="material-icons"
						action="first-page"
						title="First page"
						on:click={() => (ecurrentPage = 0)}
						disabled={ecurrentPage === 0}>first_page</IconButton
					>
					<IconButton
						class="material-icons"
						action="prev-page"
						title="Prev page"
						on:click={() => ecurrentPage--}
						disabled={ecurrentPage === 0}>chevron_left</IconButton
					>
					<IconButton
						class="material-icons"
						action="next-page"
						title="Next page"
						on:click={() => ecurrentPage++}
						disabled={ecurrentPage === elastPage}>chevron_right</IconButton
					>
					<IconButton
						class="material-icons"
						action="last-page"
						title="Last page"
						on:click={() => (ecurrentPage = elastPage)}
						disabled={ecurrentPage === elastPage}>last_page</IconButton
					>
				</Pagination>
			</DataTable>

		{:catch error}
			<p style="color: red">{error.message}</p>
		{/await}

		<div class="hide-file-ui" style="width: 100%; text-align: center;">
			<Textfield bind:files={valueTypeEventFiles} label="Upload Event File" type="file" />
		</div>

		</div>
		</DialogContent>
		<DialogActions>
			<Button
				defaultAction
				use={[DialogInitialFocus]}
				on:click={() => { event_dialog = false; }}
			>
				<ButtonLabel>CLOSE</ButtonLabel>
			</Button>
		</DialogActions>
	</Dialog>

	<Dialog
		open={volume_dialog}
		aria-labelledby="default-focus-title"
		aria-describedby="default-focus-content"
		scrimClickAction=""
		escapeKeyAction=""
	>
		<DialogTitle id="default-focus-title">VOLUME SETUP</DialogTitle>
		<DialogContent id="default-focus-content" style="text-align: center;">
			<Textfield bind:value={volume_worldref_value} label="Starting Volume / WorldRef" style="width: 100%; margin-bottom: 3vmin;" />
			<br/>
			{#each volume_worldrefs as item (item.i)}
            <Button on:click={() => { volume_worldref_value = item.label; }} variant="unelevated">
                <ButtonLabel>{item.label}</ButtonLabel>
            </Button>
			{/each}
			<div width="90%">
				<Textfield bind:value={volume_visdepth_value} label="Visibility Depth" type="number" style="width: 40%;" />
				<Slider bind:value={volume_visdepth_value} min={0} max={10} step={1} style="width: 40%; display: inline-block; vertical-align: middle;" />
			</div>
			<p style="text-align: center; margin: 0; padding: 0; margin-top: 3vmin;">Edges Visibility</p>
			<SegmentedButton segments={volume_edges_values} singleSelect let:segment bind:selected={volume_edges_value}
				key={(segment) => segment.id}  variant="unelevated" color="primary">
				<Segment {segment}>
					<Label>{segment.label}</Label>
				</Segment>
			</SegmentedButton>

		</DialogContent>
		<DialogActions>
            <Button on:click={() => { volume_dialog = false; }}>
                <ButtonLabel>CLOSE</ButtonLabel>
            </Button>
			<Button
				defaultAction
				use={[DialogInitialFocus]}
				on:click={() => { volume_dialog = false; callback_volume(); }}
			>
				<ButtonLabel>APPLY</ButtonLabel>
			</Button>
		</DialogActions>
	</Dialog>

	<Dialog
		open={clip_dialog}
		aria-labelledby="default-focus-title"
		aria-describedby="default-focus-content"
		scrimClickAction=""
		escapeKeyAction=""
	>
		<DialogTitle id="default-focus-title">CLIPPING</DialogTitle>
		<DialogContent id="default-focus-content">

            <List radioList>
			{#each clip_options as option}
                <Item use={[DialogInitialFocus]}>
                    <Graphic>
                        <Radio bind:group={clip_selected} value="{option.val}" />
                    </Graphic>
                    <Text>{option.name}</Text>
                </Item>
			{/each}
			</List>

			<FormField>
				<Checkbox bind:checked={clip_invert} />
				<Label>
					Invert Clipping
				</Label>
			</FormField>

		</DialogContent>
		<DialogActions>
            <Button on:click={() => { clip_dialog = false; }}>
                <ButtonLabel>CLOSE</ButtonLabel>
            </Button>
            <Button
                defaultAction
                use={[DialogInitialFocus]}
                on:click={() => { clip_dialog = false; callback_clip(); }}
            >
                <ButtonLabel>APPLY</ButtonLabel>
            </Button>

		</DialogActions>
	</Dialog>

	<Dialog
		open={camera_dialog}
		aria-labelledby="default-focus-title"
		aria-describedby="default-focus-content"
		scrimClickAction=""
		escapeKeyAction=""
	>
		<DialogTitle id="default-focus-title">CAMERA SETUP</DialogTitle>
		<DialogContent id="default-focus-content" style="min-width: 80vmin; text-align: center;">
			<table style="width: 90%;">
				<tr>
					<td width="50%">
						<Textfield bind:value={camera_x_value} label="Camera X" type="number" />
					</td>
					<td width="50%">
						<Slider bind:value={camera_x_value} min={-50000} max={50000} step={1} style="width: 100%;" />
					</td>
				</tr>
				<tr>
					<td>
						<Textfield bind:value={camera_y_value} label="Camera Y" type="number" />
					</td>
					<td>
						<Slider bind:value={camera_y_value} min={-50000} max={50000} step={1} style="width: 100%;" />
					</td>
				</tr>
				<tr style="">
					<td>
						<Textfield bind:value={camera_z_value} label="Camera Z" type="number" />
					</td>
					<td>
						<Slider bind:value={camera_z_value} min={-50000} max={50000} step={1} style="width: 100%;" />
					</td>
				</tr>
				<tr>
					<td colspan=2 style="text-align: center; margin: 0; padding: 0; padding-bottom: 1vmin;">
						<p style="text-align: center; margin: 0; padding: 0; margin-top: 3vmin;">Camera UP direction</p>
				        <SegmentedButton segments={camera_up_values} singleSelect let:segment bind:selected={camera_up_value}
				            key={(segment) => segment.id}  variant="unelevated" color="primary">
				            <Segment {segment}>
				                <Label>{segment.label}</Label>
				            </Segment>
				        </SegmentedButton>
					</td>
				</tr>
				<tr>
					<td colspan=2 style="text-align: center; margin: 0; padding: 0; padding-bottom: 1vmin;">
						<p style="text-align: center; margin: 0; padding: 0;">Field Of View</p>
				        <SegmentedButton segments={camera_fieldofview_values} singleSelect let:segment bind:selected={camera_fieldofview_value}
				            key={(segment) => segment.id}  variant="unelevated" color="primary">
				            <Segment {segment}>
				                <Label>{segment.label}</Label>
				            </Segment>
				        </SegmentedButton>
					</td>
				</tr>
				<tr>
					<td colspan=2 style="text-align: center; margin: 0; padding: 0; padding-bottom: 1vmin;">
						<p style="text-align: center; margin: 0; padding: 0;">Background Color</p>
				        <SegmentedButton segments={camera_bgcolor_values} singleSelect let:segment bind:selected={camera_bgcolor_value}
				            key={(segment) => segment.id}  variant="unelevated" color="primary">
				            <Segment {segment}>
				                <Label>{segment.label}</Label>
				            </Segment>
				        </SegmentedButton>
					</td>
				</tr>
			</table>
		</DialogContent>
		<DialogActions>
            <Button on:click={() => { camera_dialog = false; }}>
                <ButtonLabel>CLOSE</ButtonLabel>
            </Button>
			<Button
				defaultAction
				use={[DialogInitialFocus]}
				on:click={() => { camera_dialog = false; callback_camera(); }}
			>
				<ButtonLabel>APPLY</ButtonLabel>
			</Button>
		</DialogActions>
	</Dialog>

	<Dialog
		open={visibility_dialog}
		aria-labelledby="default-focus-title"
		aria-describedby="default-focus-content"
		scrimClickAction=""
		escapeKeyAction=""
	>
		<DialogTitle id="default-focus-title">VISIBILITY SETUP</DialogTitle>
		<DialogContent id="default-focus-content">

        <p style="text-align: center; padding-bottom: 1vmin; border-bottom: 1px dashed #000;">
		World Visibility <br/>
        <SegmentedButton segments={world_visibility_values} singleSelect let:segment bind:selected={world_visibility_value}
            key={(segment) => segment.id}  variant="unelevated" color="primary">
            <Segment {segment}>
                <Label>{segment.label}</Label>
            </Segment>
        </SegmentedButton>
        </p>

        <p style="text-align: center; padding-bottom: 1vmin; border-bottom: 1px dashed #000;">
		XYZ Axes Visibility <br/>
        <SegmentedButton segments={axes_visibility_values} singleSelect let:segment bind:selected={axes_visibility_value}
            key={(segment) => segment.id}  variant="unelevated" color="primary">
            <Segment {segment}>
                <Label>{segment.label}</Label>
            </Segment>
        </SegmentedButton>
        </p>

        <p style="text-align: center; padding-bottom: 1vmin; border-bottom: 1px dashed #000;">
		Containers Visibility <br/>
        <SegmentedButton segments={containers_visibility_values} singleSelect let:segment bind:selected={containers_visibility_value}
            key={(segment) => segment.id}  variant="unelevated" color="primary">
            <Segment {segment}>
                <Label>{segment.label}</Label>
            </Segment>
        </SegmentedButton>
        </p>

        <p style="text-align: center; padding-bottom: 1vmin; border-bottom: 1px dashed #000;"> Logs Visibility </p>
        <SegmentedButton segments={logs_visibility_values} singleSelect let:segment bind:selected={logs_visibility_value}
            key={(segment) => segment.id}  variant="unelevated" color="primary">
            <Segment {segment}>
                <Label>{segment.label}</Label>
            </Segment>
        </SegmentedButton>

		</DialogContent>
		<DialogActions>
            <Button on:click={() => { visibility_dialog = false; }}>
                <ButtonLabel>CLOSE</ButtonLabel>
            </Button>
			<Button
				defaultAction
				use={[DialogInitialFocus]}
				on:click={() => { visibility_dialog = false; callback_visibility(); }}
			>
				<ButtonLabel>APPLY</ButtonLabel>
			</Button>
		</DialogActions>
	</Dialog>

	<Dialog
		open={colors_dialog}
		aria-labelledby="default-focus-title"
		aria-describedby="default-focus-content"
		scrimClickAction=""
		escapeKeyAction=""
	>
		<DialogTitle id="default-focus-title">COLOR PALETTE</DialogTitle>
		<DialogContent id="default-focus-content" style="height: 40vmin; width: 60vmin; text-align: center;">

        <p style="text-align: center; padding: 0; margin: 0; margin-top: 2vmin;"> Detector Color Palette </p>
        <SegmentedButton segments={colors_palette_values} singleSelect let:segment bind:selected={colors_palette_value}
            key={(segment) => segment.id}  variant="unelevated" color="primary">
            <Segment {segment}>
                <Label>{segment.label}</Label>
            </Segment>
        </SegmentedButton>

		<div width="90%" style="margin-top: 3vmin; margin-bottom: 3vmin;">
			<Textfield bind:value={colors_palettestart_value} label="Palette Starting Index" type="number" style="width: 40%;" />
			<Slider bind:value={colors_palettestart_value} min={0} max={colors_palette_value.size} step={1} style="width: 40%; display: inline-block; vertical-align: middle;" />
		</div>

        <p style="text-align: center; padding: 0; margin: 0;"> Track Color </p>
        <SegmentedButton segments={colors_trackcolor_values} singleSelect let:segment bind:selected={colors_trackcolor_value}
            key={(segment) => segment.id}  variant="unelevated" color="primary">
            <Segment {segment}>
                <Label>{segment.label}</Label>
            </Segment>
        </SegmentedButton>

		</DialogContent>
		<DialogActions>
			<Button on:click={() => { colors_dialog = false; }}>
				<ButtonLabel>CLOSE</ButtonLabel>
			</Button>
			<Button
				defaultAction
				use={[DialogInitialFocus]}
				on:click={() => { colors_dialog = false; callback_colors(); }}
			>
				<ButtonLabel>APPLY</ButtonLabel>
			</Button>
		</DialogActions>
	</Dialog>

	<Dialog
		open={animation_dialog}
		aria-labelledby="default-focus-title"
		aria-describedby="default-focus-content"
		scrimClickAction=""
		escapeKeyAction=""
	>
		<DialogTitle id="default-focus-title">ANIMATION</DialogTitle>
		<DialogContent id="default-focus-content">

            <List radioList>
                <Item use={[DialogInitialFocus]}>
                    <Graphic>
                        <Radio bind:group={animation_selected} value="0" />
                    </Graphic>
                    <Text>No Rotation</Text>
                </Item>
                <Item use={[DialogInitialFocus]}>
                    <Graphic>
                        <Radio bind:group={animation_selected} value="1" />
                    </Graphic>
                    <Text>Around Y Axis</Text>
                </Item>
                <Item use={[DialogInitialFocus]}>
                    <Graphic>
                        <Radio bind:group={animation_selected} value="2" />
                    </Graphic>
                    <Text>Around X Axis</Text>
                </Item>
                <Item use={[DialogInitialFocus]}>
                    <Graphic>
                        <Radio bind:group={animation_selected} value="3" />
                    </Graphic>
                    <Text>Around Z Axis</Text>
                </Item>
			</List>

		</DialogContent>
		<DialogActions>
            <Button on:click={() => { animation_dialog = false; }}>
                <ButtonLabel>CLOSE</ButtonLabel>
            </Button>
			<Button
				defaultAction
				use={[DialogInitialFocus]}
				on:click={() => { animation_dialog = false; callback_animation(); }}
			>
				<ButtonLabel>APPLY</ButtonLabel>
			</Button>
		</DialogActions>
	</Dialog>

	<Dialog
		open={screenshot_dialog}
		aria-labelledby="default-focus-title"
		aria-describedby="default-focus-content"
		scrimClickAction=""
		escapeKeyAction=""
	>
		<DialogTitle id="default-focus-title">SCREENSHOT</DialogTitle>
		<DialogContent id="default-focus-content">
			<List radioList>
				<Item use={[DialogInitialFocus]}>
					<Graphic>
						<Radio bind:group={screenshot_value} value="1" />
					</Graphic>
					<Text>SCREEN SIZE</Text>
				</Item>
				<Item>
					<Graphic>
						<Radio bind:group={screenshot_value} value="2" />
					</Graphic>
					<Text>512 x 512 px</Text>
				</Item>
				<Item>
					<Graphic>
						<Radio bind:group={screenshot_value} value="3" />
					</Graphic>
					<Text>1024 x 1024 px</Text>
				</Item>
				<Item>
					<Graphic>
						<Radio bind:group={screenshot_value} value="4" />
					</Graphic>
					<Text>2048 x 2048 px</Text>
				</Item>
				<Item>
					<Graphic>
						<Radio bind:group={screenshot_value} value="5" />
					</Graphic>
					<Text>4096 x 4096 px (*)</Text>
				</Item>
				<Item>
					<Graphic>
						<Radio bind:group={screenshot_value} value="6" />
					</Graphic>
					<Text>8192 x 8192 px (*)</Text>
				</Item>
				<Item>
					<Graphic>
						<Radio bind:group={screenshot_value} value="7" />
					</Graphic>
					<Text>SVG Format (vector image)</Text>
				</Item>
			</List>
		<p><i>(*) if your video card supports it</i></p>
		<a id="hidden-screenshot" href="" style="visibility: hidden;">image placeholder</a>
		</DialogContent>
		<DialogActions>
			<Button on:click={() => { screenshot_dialog = false; }}>
				<ButtonLabel>CLOSE</ButtonLabel>
			</Button>
			<Button
				defaultAction
				use={[DialogInitialFocus]}
				on:click={() => { screenshot_dialog = false; callback_screenshot(); }}
			>
				<ButtonLabel>TAKE SCREENSHOT</ButtonLabel>
			</Button>
		</DialogActions>
	</Dialog>

	<Dialog
		open={track_cuts_dialog}
		aria-labelledby="default-focus-title"
		aria-describedby="default-focus-content"
		scrimClickAction=""
		escapeKeyAction=""
	>
		<DialogTitle id="default-focus-title">TRACK CUTS</DialogTitle>
		<DialogContent id="default-focus-content" style="width: 80vmin; height: 60vmin;">
		<table style="width: 90%;">
			<tr><td colspan=3 style="text-align: center;"> Momentum </td></tr>
			<tr>
				<td width="25%">
					<Textfield bind:value={ track_cuts_p_start }
						style="width: 100%;"
						helperLine$style="width: 100%;"
						label="Min"
						type="number">
					</Textfield>
				</td>
				<td width="50%">
					<Slider
						range
						bind:start={track_cuts_p_start}
						bind:end={track_cuts_p_end}
						min={track_cuts_p_min}
						max={track_cuts_p_max}
						step={0.1}
						input$aria-label="Momentum"
					/>
				</td>
				<td width="25%">
					<Textfield bind:value={ track_cuts_p_end }
						style="width: 100%;"
						helperLine$style="width: 100%;"
						label="Max"
						type="number">
					</Textfield>
				</td>
			</tr>
			<tr><td colspan=3 style="text-align: center;"> Transverse Momentum </td></tr>
			<tr>
				<td>
					<Textfield bind:value={ track_cuts_pt_start }
						style="width: 100%;"
						helperLine$style="width: 100%;"
						label="Min"
						type="number">
					</Textfield>
				</td>
				<td>
					<Slider
						range
						bind:start={track_cuts_pt_start}
						bind:end={track_cuts_pt_end}
						min={track_cuts_pt_min}
						max={track_cuts_pt_max}
						step={0.1}
						input$aria-label="Transverse Momentum"
					/>
				</td>
				<td>
					<Textfield bind:value={ track_cuts_pt_end }
						style="width: 100%;"
						helperLine$style="width: 100%;"
						label="Max"
						type="number">
					</Textfield>
				</td>
			</tr>
			<tr><td colspan=3 style="text-align: center;"> Pseudorapidity </td></tr>
			<tr>
				<td>
					<Textfield bind:value={ track_cuts_eta_start }
						style="width: 100%;"
						helperLine$style="width: 100%;"
						label="Min"
						type="number">
					</Textfield>
				</td>
				<td>
					<Slider
						range
						bind:start={track_cuts_eta_start}
						bind:end={track_cuts_eta_end}
						min={track_cuts_eta_min}
						max={track_cuts_eta_max}
						step={0.1}
						input$aria-label="Pseudorapidity"
					/>
				</td>
				<td>
					<Textfield bind:value={ track_cuts_eta_end }
						style="width: 100%;"
						helperLine$style="width: 100%;"
						label="Max"
						type="number">
					</Textfield>
				</td>
			</tr>
			<tr><td colspan=3 style="text-align: center;"> Phi </td></tr>
			<tr>
				<td>
					<Textfield bind:value={ track_cuts_phi_start }
						style="width: 100%;"
						helperLine$style="width: 100%;"
						label="Min"
						type="number">
					</Textfield>
				</td>
				<td>
					<Slider
						range
						bind:start={track_cuts_phi_start}
						bind:end={track_cuts_phi_end}
						min={track_cuts_phi_min}
						max={track_cuts_phi_max}
						step={0.1}
						input$aria-label="Phi"
					/>
				</td>
				<td>
					<Textfield bind:value={ track_cuts_phi_end }
						style="width: 100%;"
						helperLine$style="width: 100%;"
						label="Max"
						type="number">
					</Textfield>
				</td>
			</tr>
		</table>

	    <div>
    	<FormField>
        	<Checkbox bind:checked={track_cuts_preserve} variant="outlined" />
	        <span slot="label">Preserve cuts across events</span>
        </FormField>
    	</div>

		</DialogContent>
		<DialogActions>
            <Button on:click={() => { track_cuts_dialog = false; }}>
                <ButtonLabel>CLOSE</ButtonLabel>
            </Button>
			<Button
				defaultAction
				use={[DialogInitialFocus]}
				on:click={() => { track_cuts_dialog = false; callback_track_cuts(); }}
			>
				<ButtonLabel>APPLY</ButtonLabel>
			</Button>
		</DialogActions>
	</Dialog>

	<Dialog
		open={hit_cuts_dialog}
		aria-labelledby="default-focus-title"
		aria-describedby="default-focus-content"
		scrimClickAction=""
		escapeKeyAction=""
	>
		<DialogTitle id="default-focus-title">HIT CUTS</DialogTitle>
		<DialogContent id="default-focus-content" style="width: 80vmin; height: 60vmin;">

		<Select bind:value={hit_cuts_subsystem} label="Select Subsystem" on:change={() => { display.EVENTimporter.scuts.selected_hits = hit_cuts_subsystem; update_hit_cuts_values(); }}>
			<Option value="">--- Select Subsystem ---</Option>
			{#each hit_cuts_subsystems as subsys}
				<Option value={subsys}>{subsys}</Option>
			{/each}
		</Select>

		<table style="width: 90%;">
			<tr><td colspan=3 style="text-align: center;"> Energy </td></tr>
			<tr>
				<td width="25%">
					<Textfield bind:value={ hit_cuts_e_start }
						style="width: 100%;"
						helperLine$style="width: 100%;"
						label="Min"
						type="number">
					</Textfield>
				</td>
				<td width="50%">
					<Slider
						range
						bind:start={hit_cuts_e_start}
						bind:end={hit_cuts_e_end}
						min={hit_cuts_e_min}
						max={hit_cuts_e_max}
						step={0.1}
						input$aria-label="Energy"
					/>
				</td>
				<td width="25%">
					<Textfield bind:value={ hit_cuts_e_end }
						style="width: 100%;"
						helperLine$style="width: 100%;"
						label="Max"
						type="number">
					</Textfield>
				</td>
			</tr>
			<tr><td colspan=3 style="text-align: center;"> Pseudorapidity </td></tr>
			<tr>
				<td>
					<Textfield bind:value={ hit_cuts_eta_start }
						style="width: 100%;"
						helperLine$style="width: 100%;"
						label="Min"
						type="number">
					</Textfield>
				</td>
				<td>
					<Slider
						range
						bind:start={hit_cuts_eta_start}
						bind:end={hit_cuts_eta_end}
						min={hit_cuts_eta_min}
						max={hit_cuts_eta_max}
						step={0.1}
						input$aria-label="Pseudorapidity"
					/>
				</td>
				<td>
					<Textfield bind:value={ hit_cuts_eta_end }
						style="width: 100%;"
						helperLine$style="width: 100%;"
						label="Max"
						type="number">
					</Textfield>
				</td>
			</tr>
			<tr><td colspan=3 style="text-align: center;"> Phi </td></tr>
			<tr>
				<td>
					<Textfield bind:value={ hit_cuts_phi_start }
						style="width: 100%;"
						helperLine$style="width: 100%;"
						label="Min"
						type="number">
					</Textfield>
				</td>
				<td>
					<Slider
						range
						bind:start={hit_cuts_phi_start}
						bind:end={hit_cuts_phi_end}
						min={hit_cuts_phi_min}
						max={hit_cuts_phi_max}
						step={0.1}
						input$aria-label="Phi"
					/>
				</td>
				<td>
					<Textfield bind:value={ hit_cuts_phi_end }
						style="width: 100%;"
						helperLine$style="width: 100%;"
						label="Max"
						type="number">
					</Textfield>
				</td>
			</tr>
		</table>

	    <div>
    	<FormField>
        	<Checkbox bind:checked={hit_cuts_preserve} variant="outlined" />
	        <span slot="label">Preserve cuts across events</span>
        </FormField>
    	</div>

		</DialogContent>
		<DialogActions>
            <Button on:click={() => { hit_cuts_dialog = false; }}>
                <ButtonLabel>CLOSE</ButtonLabel>
            </Button>
			<Button
				defaultAction
				use={[DialogInitialFocus]}
				on:click={() => { hit_cuts_dialog = false; callback_hit_cuts(); }}
			>
				<ButtonLabel>APPLY</ButtonLabel>
			</Button>
		</DialogActions>
	</Dialog>

{:else}
    <AccessDenied />
{/if}

<style>

#display-infobox {
	position: absolute;
	color: #FFF;
}

#display-infobox-logo {
    background-position: center;
    background-size: cover;
	pointer-events: none;
	display: inline-block;
	vertical-align: middle;
}

#display-infobox-content {
	pointer-events: none;
	display: inline-block;
}

#show-progress {
	position: absolute;
	z-index: 1000000;
	top : 50%;
	left: 50%;
	transform: translate(-50%,-50%);
	min-width: 30vmin;
}

#display-settings {
	position: absolute;
	top  : 5vmin;
	right: 0;
}

  .hide-file-ui :global(input[type='file']::file-selector-button) {
    display: none;
  }

  .hide-file-ui
    :global(:not(.mdc-text-field--label-floating) input[type='file']) {
    color: transparent;
  }

</style>