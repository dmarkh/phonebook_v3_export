
export const get_binary_file = async ( url ) => {
	try {
	    const response = await fetch( url, {
    	    method: "GET",
        	cache: "no-cache",
	        redirect: "error",
			mode: 'cors',
			headers: {
				'Content-Type': 'application/octet-stream',
			},
			responseType: "arraybuffer"
	    });
		if ( response.ok ) {
    		return await response.arrayBuffer();
		}
		return false;
	} catch ( error ) {
    	return false;
	}
}
