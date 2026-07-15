
const characterSet = '123456789ABCDEFGHJKMNPQRSTUVWXYZ';

export const short_ts_id = ( len = 8 ) => {
  const uid = 'x'
    .repeat(len)
    .replace(/x/g, () => characterSet[Math.trunc(Math.random() * 32)]);
	return 'ID-' + (new Date()).toISOString().substring(0,10) + '-' + uid;
}

export const short_id = ( len = 8 ) => {
  const uid = 'x'
    .repeat(len)
    .replace(/x/g, () => characterSet[Math.trunc(Math.random() * 32)]);
	return 'ID-' + uid;
}