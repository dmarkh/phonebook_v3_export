<script>

import LinearProgress from '@smui/linear-progress';

export let text = 'PLEASE WAIT';

</script>

<div class="please-wait" on:click|stopPropagation={ () => {  } }></div>
<div class="please-wait-wrapper">
	<div class="please-wait-text">
		<div>{text}</div>
		<LinearProgress indeterminate />
	</div>
</div>

<style>
	.please-wait {
		z-index: 1000000;
		position: fixed;
		top: 0;
		left: 0;
		width: 100vw;
		height: 100vh;
		background-color: #000;
		opacity: 0.5;
	}
	.please-wait-wrapper {
		z-index: 1000001;
		position: fixed;
		top: 0;
		left: 0;
		width: 100vw;
		height: 100vh;
		display: flex;
		justify-content: center;
		align-items: center;
	}
	.please-wait-text {
		background-color: #FFF;
		padding: 2vmin;
		border-radius: 1vmin;
	}
</style>