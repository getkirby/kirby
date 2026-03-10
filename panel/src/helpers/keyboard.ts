/**
 * Returns name of meta key for current OS (`cmd` or `ctrl`)
 */
export function metaKey(): string {
	return window.navigator.userAgent.indexOf("Mac") > -1 ? "cmd" : "ctrl";
}

export default {
	metaKey
};
