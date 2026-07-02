import type { Dayjs, PluginFunc, UnitType } from "dayjs";

declare module "dayjs" {
	interface Dayjs {
		merge(
			dt: Dayjs | null | undefined,
			units?: "date" | "time" | UnitType[]
		): Dayjs;
	}
}

const plugin: PluginFunc = (option, Dayjs) => {
	Dayjs.prototype.merge = function (
		this: Dayjs,
		dt: Dayjs | null | undefined,
		units: "date" | "time" | UnitType[] = "date"
	): Dayjs {
		let result = this.clone();

		// if provided object is not valid,
		// return unaltered
		if (!dt || dt.isValid() === false) {
			return this;
		}

		// if string alias has been provided,
		// transform to array of units
		let resolvedUnits: UnitType[];

		if (typeof units === "string") {
			const map: Record<"date" | "time", UnitType[]> = {
				date: ["year", "month", "date"],
				time: ["hour", "minute", "second"]
			};

			if (Object.hasOwn(map, units) === false) {
				throw new Error("Invalid merge unit alias");
			}

			resolvedUnits = map[units];
		} else {
			resolvedUnits = units;
		}

		for (const unit of resolvedUnits) {
			result = result.set(unit, dt.get(unit));
		}

		return result;
	};
};

export default plugin;
