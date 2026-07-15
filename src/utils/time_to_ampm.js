const time_to_ampm = ( timeStr ) => {
    // Split the string into hours and minutes
    let [hours, minutes] = timeStr.split(':');

    hours = parseInt(hours);

    // Determine AM or PM suffix
    const ampm = hours >= 12 ? 'PM' : 'AM';

    // Convert hours to 12-hour format
    hours = hours % 12;

    // The hour '0' should be '12'
    hours = hours ? hours : 12;

    return `${hours}:${minutes} ${ampm}`;
};

export default time_to_ampm;