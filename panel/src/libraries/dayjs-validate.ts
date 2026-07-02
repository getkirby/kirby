import type { PluginFunc, UnitType } from "dayjs";

declare module "dayjs" {
	interface Dayjs {
		validate(boundary?: string, type?: "min" | "max", unit?: UnitType): boolean;
	}
}

const plugin: PluginFunc = (option, Dayjs, dayjs) => {
	/**
	 * Validates datetime against an
	 * upper or lower (min/max) boundary
	 */
	Dayjs.prototype.validate = function (
		boundary?: string,
		type: "min" | "max" = "min",
		unit: UnitType = "day"
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

		if (!dt) {
			return false;
		}

		return (
			this.isSame(dt, unit) ||
			(type === "min" ? this.isAfter(dt, unit) : this.isBefore(dt, unit))
		);
	};
};

export default plugin;
