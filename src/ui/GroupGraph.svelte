<script>

import {meta, router, Route} from 'tinro';

import AccessDenied from './AccessDenied.svelte';
import PleaseWait from './PleaseWait.svelte';
import LinearProgress from '@smui/linear-progress';

import Paper, { Content } from '@smui/paper';
import Select, { Option } from '@smui/select';
import IconButton from '@smui/icon-button';
import Button, { Label as ButtonLabel, Icon as ButtonIcon } from '@smui/button';
import Fab, { Label as FabLabel, Icon as FabIcon } from '@smui/fab';

import { group_id, auth, screen } from '../store.js';

import { getGroup, getGroups } from '../utils/pnb-api.js';
import { getMembers, getMemberFields, getGroupRoles } from '../utils/pnb-api.js';
import { convertMembers, addInstitutionsToConvertedMembers } from '../utils/pnb-convert.js';
import { sortConvertedMembers } from '../utils/pnb-sort.js';

import GraphNode from './GraphNode.svelte';
import GraphNodeStart from './GraphNodeStart.svelte';
import GraphNodeEnd from './GraphNodeEnd.svelte';

import GroupGraphSaveButton from './GroupGraphSaveButton.svelte';

  const nodeTypes = {
    graphnode: GraphNode,
    graphnodestart: GraphNodeStart,
    graphnodeend: GraphNodeEnd
  };

  import { writable } from 'svelte/store';
  import dagre from '@dagrejs/dagre';
  import {
    SvelteFlow,
    Position,
    ConnectionLineType,
	Panel,
	getNodesBounds,
	getViewportForBounds,
	useNodes,
	Background,
	Controls,
	MiniMap
  } from '@xyflow/svelte';

  import '@xyflow/svelte/dist/style.css';
  import '../css/svelte-flow-override.css';

import { sleep } from '../utils/sleep.js';

	let pleaseWait = false;
	let groups = [];
	let members = [];
	let members_by_id = {};

	// Svelte-Flow / Dagrejs
	const position = { x: 0, y: 0 };
	const edgeType = 'smoothstep';

	let initialNodes = [];
	let initialEdges = [];
	let direction = 'TB';

	const dagreGraph = new dagre.graphlib.Graph();
	dagreGraph.setGraph({ marginx: 50, marginy: 50 });
	dagreGraph.setDefaultEdgeLabel(() => ({}));

	const nodeWidth  = window.pnb['graph']['nodewidth'];
	const nodeHeight = window.pnb['graph']['nodeheight'];

	function getLayoutedElements(nodes = [], edges = [], direction = 'TB') {
    	const isHorizontal = direction === 'LR';
	    dagreGraph.setGraph({ rankdir: direction });

    	nodes.forEach((node) => {
	      dagreGraph.setNode(node.id, { width: nodeWidth, height: nodeHeight });
    	});

	    edges.forEach((edge) => {
    	  dagreGraph.setEdge(edge.source, edge.target);
    	});

	    dagre.layout(dagreGraph);

    	nodes.forEach((node) => {
	      const nodeWithPosition = dagreGraph.node(node.id);
    	  node.targetPosition = isHorizontal ? Position.Left : Position.Top;
	      node.sourcePosition = isHorizontal ? Position.Right : Position.Bottom;
	      node.position = {
    	    x: nodeWithPosition.x - nodeWidth / 2,
        	y: nodeWithPosition.y - nodeHeight / 2
	      };
    	});

	    return { nodes, edges };
  	}

	const nodes = writable([]);
	const edges = writable([]);

	function onLayout(direction) {
    	const layoutedElements = getLayoutedElements($nodes, $edges, direction);
		nodes.set( layoutedElements.nodes );
		edges.set( layoutedElements.edges );
	}

const drawGraph = async () => {
	await fetchGroups();
}

const hierarchy = {};
const grouplist = {};

// fetch groups
const fetchGroups = async () => {

	// fetch all members for display purposes
    let mem = await getMembers();
    let items = await convertMembers( mem );
    members = await addInstitutionsToConvertedMembers( items );
    sortConvertedMembers( members );
	for ( const mem of members ) {
		members_by_id[ mem.id ] = mem;
	}

    groups = await getGroups();
	for( const group of groups ) {
		if ( !hierarchy[group.parent] ) { hierarchy[group.parent] = []; }
		hierarchy[group.parent].push( group.id );
		grouplist[ group.id ] = group;
	}

	// set current group as root node

	const roles = await getGroupRoles( $group_id );
	const roles_by_id = { "0": { weight: 1000 } };
	for ( const role of roles ) {
		roles_by_id[ role.id ] = role;
	}
	const grp_full = await getGroup( $group_id );
	let grp_members = [];
	grp_full.members.sort( ( a , b ) => {
		if ( parseInt(roles_by_id[ a.role_id ].weight) < parseInt(roles_by_id[ b.role_id ].weight) ) { return -1; }
		if ( parseInt(roles_by_id[ a.role_id ].weight) > parseInt(roles_by_id[ b.role_id ].weight) ) { return  1; }
		return 0;
	});
	for ( const mem of grp_full.members ) {
		const member = members_by_id[ mem.member_id ];
		if ( member ) {
			grp_members.push( ( ( mem.role_id && mem.role_id != 0 ) ? ( (roles.find( r => r.id == mem.role_id )).role + ' : ' ) : '' )
				+ member.name_last + ', ' + member.name_first );
		}
	}
	initialNodes.push({
		id: $group_id,
		type: 'graphnodestart',
		data: { label: grouplist[$group_id].name, members: grp_members },
		position
	});

	let stack = hierarchy[$group_id] ? [ ...hierarchy[$group_id] ] : [];

	if ( stack.length ) {
		let grpid = undefined;
		while( grpid = stack.shift() ) {
			const grp = grouplist[grpid];

		    const roles = await getGroupRoles( grp.id );
		    const roles_by_id = { "0": { weight: 1000 } };
		    for ( const role of roles ) {
        		roles_by_id[ role.id ] = role;
		    }
		    const grp_full = await getGroup( grp.id );
		    let grp_members = [];
		    grp_full.members.sort( ( a , b ) => {
		        if ( parseInt(roles_by_id[ a.role_id ].weight) < parseInt(roles_by_id[ b.role_id ].weight) ) { return -1; }
		        if ( parseInt(roles_by_id[ a.role_id ].weight) > parseInt(roles_by_id[ b.role_id ].weight) ) { return  1; }
		        return 0;
		    });
		    for ( const mem of grp_full.members ) {
		        const member = members_by_id[ mem.member_id ];
				if ( member ) {
	        		grp_members.push( ( ( mem.role_id && mem.role_id != 0 ) ? ( (roles.find( r => r.id == mem.role_id )).role + ' : ' ) : '' )
			            + member.name_last + ', ' + member.name_first );
				}
		    }

			if ( hierarchy[ grp.id ] ) {
				stack = [ ...stack, ...hierarchy[ grp.id ] ];
				initialNodes.push({
					id: grp.id,
					type: 'graphnode',
					data: { label: grouplist[grp.id].name, members: grp_members },
					position
				});
			} else {
				initialNodes.push({
					id: grp.id,
					type: 'graphnodeend',
					data: { label: grouplist[grp.id].name, members: grp_members },
					position
				});
			}
			initialEdges.push({ id: (grp.parent + '_' + grp.id), source: grp.parent, target: grp.id, type: edgeType, animated: false });
		}
	}

	// display graph
    const { nodes: layoutedNodes, edges: layoutedEdges } = getLayoutedElements(
        initialNodes,
        initialEdges
    );
    nodes.set(layoutedNodes);
    edges.set(layoutedEdges);
	onLayout('TB');

    return groups;
}

</script>

	{#if pleaseWait}
    	<PleaseWait text="{pleaseWait}" />
	{:else}

{#await fetchGroups()}

	<LinearProgress indeterminate />

{:then}

	<div style="text-align: center;" class="mdc-typography--headline4">GROUP GRAPH</div>

	<Paper>

<div style="width: 100%; height: 70vh; position: relative;">
  <SvelteFlow
    {nodes}
    {nodeTypes}
    {edges}
    fitView
    connectionLineType={ConnectionLineType.SmoothStep}
    defaultEdgeOptions={{ type: 'smoothstep', animated: false }}
  >
    <Panel position="bottom-left">
	{#if direction === 'LR'}
      <Button on:click={() => { direction = 'TB'; onLayout(direction); }}>
		<ButtonLabel>Layout: Vertical</ButtonLabel>
	  </Button>
	{:else if direction === 'TB'}
      <Button on:click={() =>{ direction = 'LR'; onLayout(direction); }}>
		<ButtonLabel>Layout: Horizontal</ButtonLabel>
	  </Button>
	{/if}
      <GroupGraphSaveButton />

    </Panel>
    <Background />
	<MiniMap position="top-right" />
	<Controls position="bottom-right" />
  </SvelteFlow>
</div>

	</Paper>

{/await}

{/if}

<style>

</style>