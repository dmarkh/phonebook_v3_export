
export const authenticate = async ( login = false, pass = false ) => {
    if ( !login || !pass || !window.pnb.service ) { return false; }
	const formData = new FormData();
	formData.append( "login", login );
	formData.append( "pass",  pass  );
    const url = window.pnb.service;
    const response = await fetch( url, {
   	    method: "POST",
       	cache: "no-cache",
        credentials: "same-origin",
   	    mode: "cors",
       	body: formData
    });
   	return await response.json();
}