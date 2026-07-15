
import { writable } from 'svelte/store';

export const screen = writable('stats');
export const themeMode = writable('light');
export const serviceURI = window.pnb.service; // 'https://127.0.0.1/phonebook-mui/service/';

export const event_id = writable(false);
export const document_id = writable(false);
export const task_id = writable(false);
export const member_id = writable(false);
export const institution_id = writable(false);
export const group_id = writable(false);
export const workflow_id = writable(false);
export const workflowblock_id = writable(false);

export const status = writable('active'); // active, inactive

export const show_stop_and_warn = writable(false); // http error detected, stop all activities

export const event_mode = writable('view'); // view, history, edit
export const document_mode = writable('view'); // view, history, edit
export const task_mode = writable('view'); // view, history, edit
export const institution_mode = writable('view'); // view, history, edit
export const member_mode = writable('view'); // view, history, edit
export const group_mode = writable('view'); // view, edit
export const workflow_mode = writable('view'); // view, edit
export const workflowblock_mode = writable('view'); // view, edit

export const auth = writable(
	{ token: "", role: "", grants: {} }
);

export const localization = writable( 'en' );