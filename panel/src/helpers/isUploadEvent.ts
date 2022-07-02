/**
 * Checks if provided event is an upload-related event
 * @returns {bool}
 */
export default (event: any): boolean => {
	if (
		event.dataTransfer?.types?.includes("Files") !== true ||
		event.dataTransfer?.types?.includes("text/plain") !== false
	) {
		return false;
	}

	return true;
};
