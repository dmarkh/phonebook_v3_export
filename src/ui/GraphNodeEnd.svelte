<script>
	import { Handle, Position } from '@xyflow/svelte';
	export let data;
	export let targetPosition = undefined;
	export let width;
	export let height;
	if ( width && height ) {
		// noop
	}
	const { label, members, flowState } = data;
</script>

<div class="graphnode" style="min-width: {window.pnb.graph.nodewidth}px;">
	<div class="graphnode-label">{label}</div>
    <div class="graphnode-text">
    {#each members as member}
        <div>{member}</div>
    {/each}
    </div>
	<Handle type="target" position={targetPosition ?? Position.Top} />
</div>

<style>
	.graphnode {
		border: 3px solid #000;
		border-radius: 10px;
		padding: 10px;
		box-sizing: border-box;
		height: fit-content;
	}
	.graphnode-label {
		width: 100%;
		text-align: center;
		border-bottom: 3px dashed #000;
		padding-bottom: 10px;
		margin-bottom: 10px;
		font-size: 150% !important;
	}
    .graphnode-text {
        font-size: 120% !important;
    }
</style>