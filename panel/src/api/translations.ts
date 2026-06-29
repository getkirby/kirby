import type Api from ".";

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default (api: Api) => ({
	async list() {
		return api.get("translations");
	},
	async get(locale: string) {
		return api.get("translations/" + locale);
	}
});
