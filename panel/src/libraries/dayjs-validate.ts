/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */

import type { PluginFunc } from "dayjs";

declare module "dayjs" {
	interface Dayjs {
		/**
		 * Validates the datetime against an upper or lower
		 * (min/max) boundary
		 *
		 * @param boundary ISO string to check against
		 * @param type whether the boundary is the lower or upper end
		 */
		validate(boundary?: string, type?: "min" | "max"): boolean;
	}
}

const plugin: PluginFunc = (option, Dayjs, dayjs) => {
	Dayjs.prototype.validate = function (
		boundary?: string,
		type: "min" | "max" = "min"
	): boolean {
		if (this.isValid() === false) {
			return false;
		}

		// if no boundary is provided, return true
		// since we already know dayjs is valid
		if (!boundary) {
			return true;
		}

		// generate dayjs object for boundary
		const dt = dayjs.iso(boundary);

		if (dt === null) {
			return false;
		}

		return type === "min"
			? this.isBefore(dt) === false
			: this.isAfter(dt) === false;
	};
};

export default plugin;
