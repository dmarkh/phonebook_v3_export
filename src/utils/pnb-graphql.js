
import { auth, serviceURI, show_stop_and_warn } from '../store.js';
import { get } from 'svelte/store';

export const invenio_search_community = async ( group_name ) => {
    if ( get(show_stop_and_warn) ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/invenio/searchcommunity/';
	const collab = window.pnb.collaboration;
	const slug = collab.toLowerCase() + '-' + group_name.toLowerCase().replace(/[^a-zA-Z0-9]/g, '_');

    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : "" )
        },
        body: JSON.stringify({ 'slug': slug })
    });
    if ( response.status !== 200 ) {
        show_stop_and_warn.set(true);
        return;
    }
	const res = await response.json();
	return res.data;
} catch ( error ) {
    show_stop_and_warn.set(true);
}
}

export const invenio_create_community = async ( group_name ) => {
    if ( get(show_stop_and_warn) ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/invenio/createcommunity/';
	const collab = window.pnb.collaboration;
	const name = collab + ': ' + group_name;
	const slug = collab.toLowerCase() + '-' + group_name.toLowerCase().replace(/[^a-zA-Z0-9]/g, '_');

    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : "" )
        },
        body: JSON.stringify({ 'slug': slug, 'name': name })
    });
    if ( response.status !== 200 ) {
        show_stop_and_warn.set(true);
        return;
    }
	const res = await response.json();
	return res.data;
} catch ( error ) {
    show_stop_and_warn.set(true);
}
}
