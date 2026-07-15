
export const invenio_search_community = async ( group_name ) => {
	if ( !group_name ) { return false; }
	const collab = window.pnb.collaboration;
	const slug = collab.toLowerCase() + '-' + group_name.toLowerCase().replace(/[^a-zA-Z0-9]/g, '_');
    const results = await fetch( window.pnb.graphql, {
        method: 'POST',
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            query: `{
				invenioSearchCommunity(slug: "${slug}")
            }`
        })
    });
    const res = await results.json();
    return res.data;
}

export const invenio_create_community = async ( group_name ) => {
	if ( !group_name ) { return false; }
	const collab = window.pnb.collaboration;
	const name = collab + ': ' + group_name;
	const slug = collab.toLowerCase() + '-' + group_name.toLowerCase().replace(/[^a-zA-Z0-9]/g, '_');
    const results = await fetch( window.pnb.graphql, {
        method: 'POST',
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            query: `{
				invenioCreateCommunity(name: "${name}", slug: "${slug}")
            }`
        })
    });
    const res = await results.json();
    return res.data;
}