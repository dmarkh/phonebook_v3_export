
export const geocode_locate_address = async ( address ) => {
	const url = 'https://photon.komoot.io/api/?q=' + encodeURIComponent( address );
    const response = await fetch( url, { method: "GET" });
    const res = await response.json();
	if ( res && res.features && res.features[0] && res.features[0].geometry && res.features[0].geometry.coordinates ) {
		return ( { lat: res.features[0].geometry.coordinates[1], lon: res.features[0].geometry.coordinates[0], desc: res.features[0].properties.name } );
	}
	return undefined;
};