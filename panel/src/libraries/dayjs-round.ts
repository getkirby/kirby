import type { Dayjs, ManipulateType, PluginFunc, UnitType } from "dayjs";

declare module "dayjs" {
	interface Dayjs {
		round(unit: UnitType, size: number): Dayjs;
	}
}

const plugin: PluginFunc = (_option, Dayjs) => {
	/**
	 * Rounds the current objec
	 * to the nearest provided unit step
	 */
	Dayjs.prototype.round = function (
		unit: UnitType = "date",
		size: number = 1
	): Dayjs {
		// Validate step unit
		const units: UnitType[] = [
			"second",
			"minute",
			"hour",
			"date",
			"month",
			"year"
		];

		if (unit === "day") {
			unit = "date";
		}

		if (units.includes(unit) === false) {
			throw new Error("Invalid rounding unit");
		}

		// Validate step size
		if (
			(["date", "month", "year"].includes(unit) && size !== 1) ||
			(unit === "hour" && 24 % size !== 0) ||
			(["second", "minute"].includes(unit) && 60 % size !== 0)
		) {
			throw "Invalid rounding size for " + unit;
		}

		// clone immutable datetime object
		let dt = this.clone();

		// set all subunits (except the direct precessor)
		// to its start
		const index = units.indexOf(unit);
		const subsubunits = units.slice(0, index);
		const subunit = subsubunits.pop();

		for (const unit of subsubunits) {
			dt = dt.startOf(unit);
		}

		// if a direct precessor subunit exists,
		// check if rounding leads to a carry over
		if (subunit) {
			// define ceiling for direct precessor subunit
			const map: Partial<Record<UnitType, number>> = {
				month: 12,
				date: dt.daysInMonth(),
				hour: 24,
				minute: 60,
				second: 60
			};
			const ceiling = map[subunit]!;

			// check if subunit was rounded up (ceiling),
			// if so manipulate datetime object to include carry over
			if (Math.round(dt.get(subunit) / ceiling) * ceiling === ceiling) {
				dt = dt.add(1, (unit === "date" ? "day" : unit) as ManipulateType);
			}

			// set subunit to its start
			dt = dt.startOf(unit);
		}

		// round the main step unit
		dt = dt.set(unit, Math.round(dt.get(unit) / size) * size);

		return dt;
	};
};

export default plugin;
