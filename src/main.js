
import './css/styles.css';
import App from './ui/App.svelte';

let e = document.getElementById('app-container');
e.innerHTML = '';
const app = new App({
	target: document.getElementById('app-container'),
	props: {}
});

export default app;
