
const makeurl = ( url ) => {
	return ( window.location.protocol || 'https' )+'//'+window.location.hostname+window.pnb.basepath+'/#'+url;
}

export default makeurl;