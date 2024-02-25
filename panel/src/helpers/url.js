/**
 * Checks if the given argument is a URL
 *
 * @param {string|URL} url
 * @param {boolean} strict Whether to also check the URL against Kirby's URL validator
 * @returns {boolean}
 */
export function isUrl(url, strict) {
  if (url instanceof URL || url instanceof Location) {
    url = url.toString();
  }

  if (typeof url !== "string") {
    return false;
  }

  // check if the given URL can be
  // converted to a URL object to
  // validate it
  try {
    new URL(url, window.location);
  } catch (error) {
    return false;
  }

  // in strict mode, also validate against the
  // URL regex from the backend URL validator
  if (strict === true) {
    const regex =
      /^(?:(?:https?|ftp):\/\/)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:localhost)|(?:(?:[a-z\u00a1-\uffff0-9]+-?)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]+-?)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/[^\s]*)?$/i;
    return regex.test(url);
  }

  return true;
}

export default {
  isUrl
};
