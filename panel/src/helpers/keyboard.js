export function metaKey() {
  return window.navigator.userAgent.indexOf("Mac") > -1 ? "cmd" : "ctrl";
}

export default {
  metaKey
};
