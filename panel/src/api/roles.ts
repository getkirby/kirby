import type Api from ".";

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default (api: Api) => ({
	async list(params?: Record<string, unknown>) {
		return api.get("roles", params);
	},
	async get(name: string) {
		return api.get("roles/" + name);
	}
});
