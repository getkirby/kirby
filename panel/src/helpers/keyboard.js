/**
 * Returns name of meta key for current OS
 * @returns {string} `cmd` or `ctrl`
 */
export function metaKey() {
  return window.navigator.userAgent.indexOf("Mac") > -1 ? "cmd" : "ctrl";
}

export default {
  metaKey
};
