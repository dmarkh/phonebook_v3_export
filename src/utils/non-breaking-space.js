
export const non_breaking_space = ( str ) => {
	return str.replace( /\./g, '.~' ).replace(' ', '~');
}