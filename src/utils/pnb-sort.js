
export const sortConvertedInstitutions = ( items ) => {
	items.sort( ( a, b ) => {
        const nameA = a.name_full ? a.name_full.toLowerCase() : '';
        const nameB = b.name_full ? b.name_full.toLowerCase() : '';
		return nameA.localeCompare( nameB );
    });
}

export const sortConvertedMembers = ( items ) => {
	items.sort( ( a, b ) => {
        const nameA = a.name_last ? a.name_last.toLowerCase() : '';
        const nameB = b.name_last ? b.name_last.toLowerCase() : '';
		if ( nameA.localeCompare( nameB ) < 0 ) { return -1; }
		else if ( nameA.localeCompare( nameB ) > 0 ) { return 1; }
		const lnameA = a.name_first ? a.name_first.toLowerCase() : '';
		const lnameB = b.name_first ? b.name_first.toLowerCase() : '';
		return lnameA.localeCompare( lnameB );
    });
}

