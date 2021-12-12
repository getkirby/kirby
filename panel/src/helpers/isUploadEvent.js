/**
 * Checks if provided event is an upload-related event
 * @returns {bool}
 */
export default (event) => {
  if (!event.dataTransfer) {
    return false;
  }

  if (!event.dataTransfer.types) {
    return false;
  }

  if (event.dataTransfer.types.includes("Files") !== true) {
    return false;
  }

  if (event.dataTransfer.types.includes("text/plain") !== false) {
    return false;
  }

  return true;
};
