
import { auth, serviceURI } from '../store.js';
import { get } from 'svelte/store';

export const getInstitution = async ( id ) => {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/institutions/get/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	return await response.json();
}

export const getMember = async ( id ) => {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/get/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	return await response.json();
}

export const getInstitutionHistory = async ( id ) => {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/institutions/history/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	return await response.json();
}

export const getMemberHistory = async ( id ) => {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/history/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	return await response.json();
}

export const getInstitutions = async ( details = 'full' ) => {
	// details: name, compact, full - TBD
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/institutions/list/status:active/details:' + details;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	return await response.json();
}

export const getMembers = async ( details = 'full' ) => {
	// details: name, compact, full
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/list/status:active/details:' + details;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	return await response.json();
}

export const getInstitutionFields = async () => {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/list/object:fields/type:institutions';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	return await response.json();
}

export const getMemberFields = async () => {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/list/object:fields/type:members';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	return await response.json();
}

export const getInstitutionFieldgroups = async () => {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/list/object:fieldgroups/type:institutions';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
		headers: window.pnb && token ? { 'Authorization': 'Bearer ' + token } : {}
	});
	return await response.json();
}

export const getMemberFieldgroups = async () => {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/list/object:fieldgroups/type:members';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	return await response.json();
}

export const getCountries = async () => {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/countries/list';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	return await response.json();
}

export const createMemberField = async ( data = false ) => {
	if ( !data ) { return; }
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/create/object:fields/type:members';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	return await response.json();
}

export const createInstitutionField = async ( data = false ) => {
	if ( !data ) { return; }
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/create/object:fields/type:institutions';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	return await response.json();
}

export const createMemberFieldgroup = async ( data = false ) => {
	if ( !data ) { return; }
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/create/object:fieldgroups/type:members';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	return await response.json();
}

export const createInstitutionFieldgroup = async ( data = false ) => {
	if ( !data ) { return; }
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/create/object:fieldgroups/type:institutions';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	return await response.json();
}

export const updateMemberFields = async ( data = false ) => {
	if ( !data ) { return; }
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/modify/object:fields/type:members';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	return await response.json();
}

export const updateMemberFieldgroups = async ( data = false ) => {
	if ( !data ) { return; }
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/modify/object:fieldgroups/type:members';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	return await response.json();
}

export const updateInstitutionFields = async ( data = false ) => {
	if ( !data ) { return; }
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/modify/object:fields/type:institutions';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	return await response.json();
}

export const updateInstitutionFieldgroups = async ( data = false ) => {
	if ( !data ) { return; }
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/modify/object:fieldgroups/type:institutions';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	return await response.json();
}

export const updateMember = async ( data = false ) => {
	if ( !data ) { return; }
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/update';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	return await response.json();
}

export const updateInstitution = async ( data = false ) => {
	if ( !data ) { return; }
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/institutions/update';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : "" )
		},
		body: JSON.stringify({ data })
	});
	return await response.json();
}

export const createMember = async ( data = false ) => {
	if ( !data ) { return; }
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/create';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : "" )
		},
		body: JSON.stringify({ data })
	});
	return await response.json();
}

export const createInstitution = async ( data = false ) => {
	if ( !data ) { return; }
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/institutions/create';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	return await response.json();
}

export const toggleMember = async ( id = false ) => {
	if ( !id ) { return; }
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/toggle/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	return await response.json();
}

export const toggleInstitution = async ( id = false ) => {
	if ( !id ) { return; }
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/institutions/toggle/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "same-origin",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	return await response.json();
}
