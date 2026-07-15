
import { auth, status, serviceURI, show_stop_and_warn } from '../store.js';
import { get } from 'svelte/store';

export const createEventPoll = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/eventpolls/create';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const eventPollAddMembers = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/eventpolls/addmembers';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const eventPollAddGroups = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/eventpolls/addgroups';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getEventPoll = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/eventpolls/get';
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
		body: JSON.stringify({ 'id': id })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const toggleEventPoll = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/eventpolls/toggle';
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
		body: JSON.stringify({ 'id': id })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const listEventPolls = async ( status = 'active', page = 0, rowsPerPage = 25 ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	// details: name, compact, full - TBD
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/eventpolls/list/status:' + status + '/page:' + page + '/rows-per-page:' + rowsPerPage;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
			"Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const voteEventPoll = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/eventpolls/vote';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}


export const searchMembers = async ( searchStr = '' ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/search/object:members/type:combined';
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
		body: JSON.stringify({ 'keyword': searchStr })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const searchInstitutions = async ( searchStr = '' ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/search/object:institutions/type:combined';
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
		body: JSON.stringify({ 'keyword': searchStr })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const checkMembersByOrcid = async ( searchStr = '' ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/check/orcid';
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
		body: JSON.stringify({ 'id': searchStr })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const checkMembersByEmail = async ( searchStr = '' ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/check/email';
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
		body: JSON.stringify({ 'id': searchStr })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const aiDocSearch = async ( searchStr = '', mode = 'title+abstract', limit = 5 ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/ai/docsearch/limit:5';
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
        body: JSON.stringify({ 'search': searchStr, mode, limit })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const fetchDisplayGeometries = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const url = 'display/geometries/geometries.json';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error"
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const fetchDisplayEvents = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const url = 'display/events/events.json';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error"
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const keepalive = async () => {
	if ( get(show_stop_and_warn) ) { return; }

try {
	const url = window.pnb.service + 'keepalive.php';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error"
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}

}

export const getEvent = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/events/get/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const advanceWorkflow = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/advance/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const resetWorkflow = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/reset/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getDocument = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/documents/get/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getWorkflowProgress = async ( document_id, workflow_id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/getprogress/document_id:' + document_id + '/workflow_id:' + workflow_id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getWorkflow = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/get/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getWorkflowMap = async ( documentid ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/getmap/documentid:' + documentid;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getTask = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/get/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getInstitution = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/institutions/get/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getMember = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/get/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getGroup = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/groups/get/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getGroupRoles = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/groups/roles/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const listGroupRoles = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/groups/listroles';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getTaskMembers = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/members/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getMemberTasks = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/tasks/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getMemberGroups = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/groups/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getMemberDocuments = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/documents/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getGroupTasks = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/groups/tasks/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getInstitutionTasks = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/institutions/tasks/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getTaskGroups = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/groups/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getTaskInstitutions = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/institutions/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getInstitutionHistory = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/institutions/history/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getDocumentHistory = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/documents/history/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getTaskHistory = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/history/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getEventHistory = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/events/history/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getInstitutionsHistory = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/institutions/history';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getMemberHistory = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/history/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getMembersHistory = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/history';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getInstitutions = async ({ details = 'full', all = false } = {}) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	// details: name, compact, full - TBD
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/institutions/list/status:' + ( all === true ? 'all' : get(status) ) + '/details:' + details;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getInstitutionsTS = async ({ unix_ts = ( Date.now() / 1000.), details = 'full', all = false } = {}) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	// details: name, compact, full - TBD
	const token = get(auth)['token'];
	if ( typeof unix_ts == 'string' ) { unix_ts = ( new Date( unix_ts ) ).getTime() / 1000.; }
	const url = serviceURI + '?q=/institutions/listts/ts:' + Math.floor( unix_ts ) + '/status:' + ( all === true ? 'all' : get(status) ) + '/details:' + details;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getAllInstitutions = async ({ details = 'full' } = {}) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	// details: name, compact, full - TBD
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/institutions/list/status:all/details:' + details;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getDocuments = async ( details = 'full', page = 0, rowsPerPage = 25, ids = [] ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	// details: name, compact, full - TBD
	const data = ids.length ? { ids } : {};
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/documents/list/status:' + get(status) + '/details:' + details + '/page:' + page + '/rows-per-page:' + rowsPerPage;
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
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getTasks = async ( details = 'full', page = 0, rowsPerPage = 1000000 ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	// details: name, compact, full - TBD
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/list/status:' + get(status) + '/details:' + details + '/page:' + page + '/rows-per-page:' + rowsPerPage;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getDocumentComments = async ( document_id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/documents/listcomments/id:' + document_id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getEvents = async ( details = 'full', page = 0, rowsPerPage = 25 ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	// details: name, compact, full - TBD
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/events/list/status:' + get(status) + '/details:' + details + '/page:' + page + '/rows-per-page:' + rowsPerPage;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getWorkflows = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/list/status:' + get(status);
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getMembers = async ( details = 'full' ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	// details: name, compact, full
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/list/status:' + get(status) + '/details:' + details;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getMembersTS = async ({ unix_ts = ( Date.now() / 1000. ), details = 'full' }) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	// details: name, compact, full
	const token = get(auth)['token'];
	if ( typeof unix_ts == 'string' ) { unix_ts = ( new Date( unix_ts ) ).getTime() / 1000.; }
	const url = serviceURI + '?q=/members/listts/ts:' + Math.floor( unix_ts ) + '/status:' + get(status) + '/details:' + details;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getAllMembers = async ( details = 'full' ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	// details: name, compact, full
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/list/status:all/details:' + details;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getGroups = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/groups/list/status:' + get(status);
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getWorkflowBlocks = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/listblocks/status:' + get(status);
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getConfiguredWorkflowBlocks = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/listconfig/id:'+id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getWorkflowBlock = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/getblock/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getInstitutionFields = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/list/object:fields/type:institutions';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getDocumentFields = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/list/object:fields/type:documents';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getTaskFields = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/list/object:fields/type:tasks';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getEventFields = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/list/object:fields/type:events';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getMemberFields = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/list/object:fields/type:members';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getInstitutionFieldgroups = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/list/object:fieldgroups/type:institutions';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: window.pnb && token ? { 'Authorization': 'Bearer ' + token } : {}
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getMemberFieldgroups = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/list/object:fieldgroups/type:members';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const getCountries = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/countries/list';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const createMemberField = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/create/object:fields/type:members';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const createInstitutionField = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/create/object:fields/type:institutions';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const createDocumentField = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/create/object:fields/type:documents';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const createTaskField = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/create/object:fields/type:tasks';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const createEventField = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/create/object:fields/type:events';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const createMemberFieldgroup = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/create/object:fieldgroups/type:members';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const createInstitutionFieldgroup = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/create/object:fieldgroups/type:institutions';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const updateMemberFields = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/modify/object:fields/type:members';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const updateMemberFieldgroups = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/modify/object:fieldgroups/type:members';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const updateInstitutionFields = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/modify/object:fields/type:institutions';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const updateDocumentFields = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/modify/object:fields/type:documents';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const updateTaskFields = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/modify/object:fields/type:tasks';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const updateEventFields = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/modify/object:fields/type:events';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const updateInstitutionFieldgroups = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/service/modify/object:fieldgroups/type:institutions';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const updateMember = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/update';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const updateWorkflowBlock = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/updateblock';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const addWorkflowConfig = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/addconfig';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const addWorkflowProgress = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/addprogress';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}


export const addDocumentComment = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/documents/addcomment';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}


export const updateWorkflowConfig = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/updateconfig';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const deleteWorkflowConfig = async ( id ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !id ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/deleteconfig/id:'+id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Authorization": ( token ? "Bearer " + token : '' )
		}
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}


export const updateInstitution = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/institutions/update';
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
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const updateDocument = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/documents/update';
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
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const updateTask = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/update';
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
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const updateWorkflow = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/update';
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
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const updateEvent = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/events/update';
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
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}


export const updateGroup = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/groups/update';
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
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const createEvent = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/events/create';
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
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const createWorkflow = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/create';
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
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const createWorkflowMap = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/createmap';
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
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const removeWorkflowMap = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/workflows/removemap';
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
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const createDocument = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/documents/create';
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
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const createTask = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/create';
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
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const createMember = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/create';
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
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const createInstitution = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/institutions/create';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const createGroup = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !data ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/groups/create';
	const response = await fetch( url, {
		method: "POST",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
		headers: {
			"Content-Type": "application/json; charset=utf-8",
			"Authorization": ( token ? "Bearer " + token : '' )
		},
		body: JSON.stringify({ data })
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const toggleDocument = async ( id = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !id ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/documents/toggle/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const toggleTask = async ( id = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !id ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/toggle/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const toggleEvent = async ( id = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !id ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/events/toggle/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const toggleMember = async ( id = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !id ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/members/toggle/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const toggleInstitution = async ( id = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !id ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/institutions/toggle/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const toggleGroup = async ( id = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !id ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/groups/toggle/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const groupAddMember = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
    if ( !data ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/groups/addmember';
    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : '' )
        },
        body: JSON.stringify({ data })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
    return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const taskAssign = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
    if ( !data ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/tasks/assign';
    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : '' )
        },
        body: JSON.stringify({ data })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
    return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const taskUnassign = async ( id = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !id ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/unassign/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const taskValidate = async ( id = false, val = 0 ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !id ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/validate/id:' + id + '/val:' + val;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const taskAssigned = async () => {
	if ( get(show_stop_and_warn) ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/assigned';
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const taskAssignedGet = async ( id = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !id ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/assignedget/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const taskAssignedTask = async ( id = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !id ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/assignedtask/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const taskAssignedGroup = async ( id = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !id ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/assignedgroup/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const taskAssignedMember = async ( id = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
	if ( !id ) { return; }
try {
	const token = get(auth)['token'];
	const url = serviceURI + '?q=/tasks/assignedmember/id:' + id;
	const response = await fetch( url, {
		method: "GET",
		cache: "no-cache",
		credentials: "include",
		redirect: "error",
		mode: "cors",
        headers: {
            "Authorization": ( token ? "Bearer " + token : "" )
        }
	});
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
	return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const taskAssignedUpdate = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
    if ( !data ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/tasks/assignedupdate';
    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : '' )
        },
        body: JSON.stringify({ data })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
    return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const taskAddMember = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
    if ( !data ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/tasks/addmember';
    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : '' )
        },
        body: JSON.stringify({ data })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
    return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const taskAddInstitution = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
    if ( !data ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/tasks/addinstitution';
    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : '' )
        },
        body: JSON.stringify({ data })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
    return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const taskAddGroup = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
    if ( !data ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/tasks/addgroup';
    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : '' )
        },
        body: JSON.stringify({ data })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
    return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const groupRemoveMember = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
    if ( !data ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/groups/removemember';
    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : '' )
        },
        body: JSON.stringify({ data })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
    return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const taskRemoveMember = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
    if ( !data ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/tasks/removemember';
    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : '' )
        },
        body: JSON.stringify({ data })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
    return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const taskRemoveInstitution = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
    if ( !data ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/tasks/removeinstitution';
    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : '' )
        },
        body: JSON.stringify({ data })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
    return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const taskRemoveGroup = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
    if ( !data ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/tasks/removegroup';
    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : '' )
        },
        body: JSON.stringify({ data })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
    return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const groupAddRole = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
    if ( !data ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/groups/addrole';
    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : '' )
        },
        body: JSON.stringify({ data })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
    return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const groupRemoveRole = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
    if ( !data ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/groups/removerole';
    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : '' )
        },
        body: JSON.stringify({ data })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
    return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const groupUpdateRole = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
    if ( !data ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/groups/updaterole';
    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : '' )
        },
        body: JSON.stringify({ data })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
    return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}

export const groupUpdateMemberRole = async ( data = false ) => {
	if ( get(show_stop_and_warn) ) { return; }
    if ( !data ) { return; }
try {
    const token = get(auth)['token'];
    const url = serviceURI + '?q=/groups/updatememberrole';
    const response = await fetch( url, {
        method: "POST",
        cache: "no-cache",
        credentials: "include",
        redirect: "error",
        mode: "cors",
        headers: {
            "Content-Type": "application/json; charset=utf-8",
            "Authorization": ( token ? "Bearer " + token : '' )
        },
        body: JSON.stringify({ data })
    });
	if ( response.status !== 200 ) {
		show_stop_and_warn.set(true);
		return;
	}
    return await response.json();
} catch ( error ) {
	show_stop_and_warn.set(true);
}
}
